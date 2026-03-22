<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\EnrolledSubject;
use App\Models\Enrollment;
use App\Models\GradeChangeLog;
use App\Models\ScheduledSubject;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GradeController extends Controller
{
    // CMC Grade Codes: DR=Dropped, NA=Never Attended, INC=Incomplete, W=Withdrawn, NG=No Grade
    private const FINAL_GRADE_CODES = ['DR', 'NA', 'INC', 'W', 'NG'];

    private const GPA_EQUIVALENT_CODES = [
        'NA' => 5.0,
        'DR' => 5.0,
    ];

    private const COMPLETION_DEADLINE_CODES = ['INC'];

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'student') {
            return $this->studentGrades($request);
        } elseif ($user->role === 'instructor') {
            return $this->instructorGrades($request);
        } elseif ($user->role === 'registrar') {
            return $this->registrarGrades($request);
        }

        abort(403, 'Unauthorized access.');
    }

    // Student view - see their own grades
    private function studentGrades(Request $request)
    {
        $student = Auth::user()->student;

        $query = Enrollment::where('student_id', $student->id)
            ->with([
                'academicTerm',
                'enrolledSubjects' => function ($q) {
                    $q->with([
                        'scheduledSubject.curriculumSubject.subject',
                        'scheduledSubject.instructor.user'
                    ]);
                }
            ]);

        // Filter by term
        if ($request->has('academic_term_id')) {
            $query->where('academic_term_id', $request->academic_term_id);
        }

        $enrollments = collect($query->latest()->get()->toArray())->map(function (array $enrollment) {
            $gradedSubjects = collect($enrollment['enrolled_subjects'] ?? [])
                ->map(function (array $subject) {
                    $subject['_gpa_grade'] = $this->resolveFinalGradeForGpa($subject);
                    return $subject;
                })
                ->filter(fn (array $subject) => $subject['_gpa_grade'] !== null)
                ->values();

            $totalUnits = 0;
            $totalGradePoints = 0;

            foreach ($gradedSubjects as $subject) {
                $units = (float) ($subject['scheduled_subject']['curriculum_subject']['subject']['units'] ?? 0);
                $grade = $subject['_gpa_grade'];

                if ($units <= 0 || $grade === null) {
                    continue;
                }

                $totalUnits += $units;
                $totalGradePoints += ($grade * $units);
            }

            return array_merge($enrollment, [
                'gpa_units' => $totalUnits,
                'gpa_subject_count' => $gradedSubjects->count(),
                'term_gpa' => $totalUnits > 0 ? round($totalGradePoints / $totalUnits, 2) : null,
            ]);
        });

        $academicTerms = AcademicTerm::latest('academic_year')->get();
        $activeTerm = AcademicTerm::where('is_active', true)->first();

        return Inertia::render('Grades/StudentView', [
            'enrollments' => $enrollments,
            'academicTerms' => $academicTerms,
            'activeTermId' => $activeTerm?->id,
            'filters' => $request->only('academic_term_id'),
        ]);
    }

    // Instructor view - manage grades for their classes
    private function instructorGrades(Request $request)
    {
        $instructor = Auth::user()->instructor;

        $query = ScheduledSubject::where('instructor_id', $instructor->id)
            ->with([
                'academicTerm',
                'block',
                'curriculumSubject.subject',
                'enrolledSubjects' => function ($q) {
                    $q->with('enrollment.student.user');
                }
            ]);

        // Filter by academic term
        if ($request->has('academic_term_id')) {
            $query->where('academic_term_id', $request->academic_term_id);
        } else {
            // Default to active term
            $activeTerm = AcademicTerm::where('is_active', true)->first();
            if ($activeTerm) {
                $query->where('academic_term_id', $activeTerm->id);
            }
        }

        $classes = $query->get()->map(function ($scheduledSubject) {
            $term = $scheduledSubject->academicTerm;
            $scheduledSubject->midterm_open = $term ? $term->isMidGradeOpen() : false;
            $scheduledSubject->final_open = $term ? $term->isFinalGradeOpen() : false;
            return $scheduledSubject;
        });

        $academicTerms = AcademicTerm::latest('academic_year')->get();
        $activeTerm = AcademicTerm::where('is_active', true)->first();

        return Inertia::render('Grades/InstructorView', [
            'classes' => $classes,
            'academicTerms' => $academicTerms,
            'activeTerm' => $activeTerm,
            'filters' => $request->only('academic_term_id'),
        ]);
    }

    // Registrar view - overview of all grades
    private function registrarGrades(Request $request)
    {
        $searchedId = $request->input('student_id');
        $student = null;
        $enrollments = collect();
        $searchMessage = null;

        if ($searchedId) {
            $student = Student::with(['user', 'program', 'block'])
                ->whereHas('user', function ($q) use ($searchedId) {
                    $q->where('official_id', $searchedId);
                })
                ->first();

            if ($student) {
                $enrollments = Enrollment::where('student_id', $student->id)
                    ->with([
                        'academicTerm',
                        'enrolledSubjects' => function ($q) {
                            $q->with([
                                'scheduledSubject.curriculumSubject.subject',
                                'scheduledSubject.instructor.user',
                                'gradeChangeLogs.modifiedBy'
                            ])
                                ->orderBy('status');
                        }
                    ])
                    ->latest('academic_term_id')
                    ->get()
                    ->toArray();

                $enrollments = collect($enrollments)->map(function (array $enrollment) {
                    $gradedSubjects = collect($enrollment['enrolled_subjects'] ?? [])
                        ->map(function (array $subject) {
                            $subject['_gpa_grade'] = $this->resolveFinalGradeForGpa($subject);
                            return $subject;
                        })
                        ->filter(fn (array $subject) => $subject['_gpa_grade'] !== null)
                        ->values();

                    $totalUnits = 0;
                    $totalGradePoints = 0;

                    foreach ($gradedSubjects as $subject) {
                        $units = (float) ($subject['scheduled_subject']['curriculum_subject']['subject']['units'] ?? 0);
                        $grade = $subject['_gpa_grade'];

                        if ($units <= 0 || $grade === null) {
                            continue;
                        }

                        $totalUnits += $units;
                        $totalGradePoints += ($grade * $units);
                    }

                    return array_merge($enrollment, [
                        'gpa_units' => $totalUnits,
                        'gpa_subject_count' => $gradedSubjects->count(),
                        'term_gpa' => $totalUnits > 0 ? round($totalGradePoints / $totalUnits, 2) : null,
                    ]);
                });
            } else {
                $searchMessage = 'No student found.';
            }
        }

        return Inertia::render('Grades/RegistrarView', [
            'student' => $student,
            'enrollments' => $enrollments,
            'searchedId' => $searchedId,
            'searchMessage' => $searchMessage,
        ]);
    }

    // Show grade entry form for a specific class (Instructor)
    public function edit(ScheduledSubject $scheduledSubject)
    {
        $user = Auth::user();

        // Check if instructor owns this class
        if ($user->role === 'instructor' && $scheduledSubject->instructor_id !== $user->instructor->id) {
            abort(403, 'Unauthorized access.');
        }

        $scheduledSubject->load([
            'academicTerm',
            'block',
            'curriculumSubject.subject',
            'enrolledSubjects' => function ($q) {
                $q->with('enrollment.student.user')
                    ->orderBy('status');
            }
        ]);

        $term = $scheduledSubject->academicTerm;

        return Inertia::render('Grades/Edit', [
            'scheduledSubject' => $scheduledSubject,
            'canEditMidterm' => $term->isMidGradeOpen() || $user->role === 'registrar',
            'canEditFinal' => $term->isFinalGradeOpen() || $user->role === 'registrar',
            'midtermOpen' => $term->isMidGradeOpen(),
            'finalOpen' => $term->isFinalGradeOpen(),
            'midtermSubmittedAt' => $scheduledSubject->midterm_submitted_at,
            'finalSubmittedAt' => $scheduledSubject->final_submitted_at,
        ]);
    }

    // Update grades (Instructor or Registrar)
    public function update(Request $request, ScheduledSubject $scheduledSubject)
    {
        $user = Auth::user();
        $term = $scheduledSubject->academicTerm;

        // Authorization check
        if ($user->role === 'instructor' && $scheduledSubject->instructor_id !== $user->instructor->id) {
            abort(403, 'Unauthorized access.');
        }

        $gradeType = $request->input('grade_type');

        // Check if grade submission is open and not submitted (unless registrar)
        if ($user->role !== 'registrar') {
            if ($gradeType === 'midterm' && !$term->isMidGradeOpen()) {
                return back()->with('error', 'Midterm grade submission period is not open.');
            }

            if ($gradeType === 'final' && !$term->isFinalGradeOpen()) {
                return back()->with('error', 'Final grade submission period is not open.');
            }

            if ($gradeType === 'midterm' && $scheduledSubject->isMidtermSubmitted()) {
                return back()->with('error', 'Midterm grades have already been submitted.');
            }

            if ($gradeType === 'final' && $scheduledSubject->isFinalSubmitted()) {
                return back()->with('error', 'Final grades have already been submitted.');
            }
        }

        $rules = [
            'grade_type' => 'required|in:midterm,final',
            'grades' => 'required|array',
            'grades.*.enrolled_subject_id' => 'required|exists:enrolled_subjects,id',
            'grades.*.grade_code' => 'nullable|in:' . implode(',', self::FINAL_GRADE_CODES),
        ];

        $rules['grades.*.grade'] = 'nullable|numeric|min:1|max:5';

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            foreach ($validated['grades'] as $gradeData) {
                $enrolledSubject = EnrolledSubject::findOrFail($gradeData['enrolled_subject_id']);

                // Verify this enrolled subject belongs to this scheduled subject
                if ($enrolledSubject->scheduled_subject_id !== $scheduledSubject->id) {
                    continue;
                }

                if ($validated['grade_type'] === 'midterm') {
                    if (!isset($gradeData['grade']) || $gradeData['grade'] === null || $gradeData['grade'] === '') {
                        return back()->withErrors([
                            'grades' => 'Midterm grade is required and must be between 1.00 and 5.00.',
                        ])->withInput();
                    }

                    $enrolledSubject->update(['midterm_grade' => $gradeData['grade']]);
                } else {
                    $gradeValue = $gradeData['grade'] ?? null;
                    $gradeCode = strtoupper((string) ($gradeData['grade_code'] ?? ''));
                    $gradeCode = $gradeCode !== '' ? $gradeCode : null;

                    if ($gradeValue === null && $gradeCode === null) {
                        return back()->withErrors([
                            'grades' => 'Final grade must have either a numeric value (1.00-5.00) or a grade code.',
                        ])->withInput();
                    }

                    $enrolledSubject->update([
                        'final_grade' => $gradeValue,
                        'final_grade_code' => $gradeCode,
                        'completion_due_at' => ((float) $gradeValue === 4.0 || in_array($gradeCode, self::COMPLETION_DEADLINE_CODES, true))
                            ? now()->addMonths(6)->toDateString()
                            : null,
                    ]);

                    // Mark as completed if final grade is submitted
                    if ($gradeValue !== null || $gradeCode !== null) {
                        $enrolledSubject->update(['status' => 'completed']);
                    }
                }
            }

            if ($user->role === 'instructor') {
                if ($validated['grade_type'] === 'midterm') {
                    $scheduledSubject->update([
                        'midterm_submitted_at' => now(),
                        'midterm_submitted_by' => $user->id,
                    ]);
                } else {
                    $scheduledSubject->update([
                        'final_submitted_at' => now(),
                        'final_submitted_by' => $user->id,
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', ucfirst($validated['grade_type']) . ' grades saved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save grades: ' . $e->getMessage());
        }
    }

    // Registrar: Override/modify a specific grade
    public function updateSingleGrade(Request $request, EnrolledSubject $enrolledSubject)
    {
        $user = Auth::user();

        if ($user->role !== 'registrar') {
            abort(403, 'Only registrars can modify individual grades.');
        }

        $validated = $request->validate([
            'grade_period' => 'required|in:midterm,final',
            'new_grade_numeric' => 'nullable|numeric|min:1|max:5',
            'new_grade_code' => 'nullable|in:' . implode(',', self::FINAL_GRADE_CODES),
            'completion_due_at' => 'nullable|date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $oldGrade = $validated['grade_period'] === 'midterm'
            ? $enrolledSubject->midterm_grade
            : ($enrolledSubject->final_grade_code ?? $enrolledSubject->final_grade);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('grade-change-attachments');
        }

        if ($validated['grade_period'] === 'midterm') {
            if (($validated['new_grade_numeric'] ?? null) === null) {
                return back()->withErrors(['new_grade_numeric' => 'Midterm grade must be numeric and between 1.00 and 5.00.'])->withInput();
            }

            $enrolledSubject->update(['midterm_grade' => (float) $validated['new_grade_numeric']]);
        } else {
            $finalGrade = null;
            $finalCode = null;
            $completionDueAt = null;

            if (($validated['new_grade_numeric'] ?? null) === null && ($validated['new_grade_code'] ?? null) === null) {
                return back()->withErrors([
                    'new_grade_numeric' => 'Provide either final numeric grade (1.00-5.00) or a grade code.',
                ])->withInput();
            }

            if (($validated['new_grade_numeric'] ?? null) !== null) {
                $finalGrade = (float) $validated['new_grade_numeric'];
            }

            if (($validated['new_grade_code'] ?? null) !== null) {
                $finalCode = strtoupper((string) $validated['new_grade_code']);
            }

            if ($finalGrade === 4.0 || in_array($finalCode, self::COMPLETION_DEADLINE_CODES, true)) {
                $completionDueAt = $validated['completion_due_at'] ?? now()->addMonths(6)->toDateString();
            }

            $enrolledSubject->update([
                'final_grade' => $finalGrade,
                'final_grade_code' => $finalCode,
                'completion_due_at' => $completionDueAt,
                'status' => ($finalGrade !== null || $finalCode !== null) ? 'completed' : $enrolledSubject->status,
            ]);
        }

        GradeChangeLog::create([
            'enrolled_subject_id' => $enrolledSubject->id,
            'grade_period' => $validated['grade_period'],
            'old_grade' => is_numeric($oldGrade) ? (float) $oldGrade : null,
            'new_grade' => ($validated['new_grade_numeric'] ?? null) !== null
                ? (float) $validated['new_grade_numeric']
                : null,
            'reason' => ($validated['new_grade_code'] ?? null)
                ? $validated['reason'] . ' [Code: ' . strtoupper((string) $validated['new_grade_code']) . ']'
                : $validated['reason'],
            'attachment_path' => $attachmentPath,
            'modified_by' => $user->id,
        ]);

        return back()->with('success', 'Grade updated successfully.');
    }

    // Drop a subject (Student or Registrar)
    public function dropSubject(Request $request, EnrolledSubject $enrolledSubject)
    {
        $user = Auth::user();
        $term = $enrolledSubject->enrollment->academicTerm;

        // Check authorization
        if ($user->role === 'student') {
            if ($enrolledSubject->enrollment->student->user_id !== $user->id) {
                abort(403, 'Unauthorized access.');
            }

            // Students can only drop during enrollment period
            if (!$term->isEnrollmentOpen()) {
                return back()->with('error', 'Cannot drop subjects outside enrollment period.');
            }
        }

        // Cannot drop if grades are already submitted
        if ($enrolledSubject->midterm_grade !== null || $enrolledSubject->final_grade !== null) {
            return back()->with('error', 'Cannot drop subject with submitted grades.');
        }

        $enrolledSubject->update(['status' => 'dropped']);

        return back()->with('success', 'Subject dropped successfully.');
    }

    // Generate class grade sheet (Instructor/Registrar)
    public function classGradeSheet(ScheduledSubject $scheduledSubject)
    {
        $scheduledSubject->load([
            'academicTerm',
            'block',
            'curriculumSubject.subject',
            'instructor.user',
            'enrolledSubjects' => function ($q) {
                $q->with('enrollment.student.user')
                    ->where('status', '!=', 'dropped')
                    ->orderBy('status');
            }
        ]);

        return Inertia::render('Grades/ClassGradeSheet', [
            'scheduledSubject' => $scheduledSubject,
        ]);
    }

    private function resolveFinalGradeForGpa(array $subject): ?float
    {
        if (is_numeric($subject['final_grade'] ?? null)) {
            return (float) $subject['final_grade'];
        }

        $code = strtoupper((string) ($subject['final_grade_code'] ?? ''));

        if (isset(self::GPA_EQUIVALENT_CODES[$code])) {
            return self::GPA_EQUIVALENT_CODES[$code];
        }

        return null;
    }

}
