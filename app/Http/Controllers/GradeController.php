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

        $enrollments = $query->latest()->get();

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
                    ->get();
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
        ];

        $rules['grades.*.grade'] = $user->role === 'registrar'
            ? 'nullable|numeric|min:0|max:100'
            : 'required|numeric|min:0|max:100';

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
                    $enrolledSubject->update(['midterm_grade' => $gradeData['grade']]);
                } else {
                    $enrolledSubject->update(['final_grade' => $gradeData['grade']]);

                    // Mark as completed if final grade is submitted
                    if ($gradeData['grade'] !== null) {
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
            'new_grade' => 'required|numeric|min:0|max:100',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|max:5120',
        ]);

        $oldGrade = $validated['grade_period'] === 'midterm'
            ? $enrolledSubject->midterm_grade
            : $enrolledSubject->final_grade;

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('grade-change-attachments');
        }

        if ($validated['grade_period'] === 'midterm') {
            $enrolledSubject->update(['midterm_grade' => $validated['new_grade']]);
        } else {
            $enrolledSubject->update([
                'final_grade' => $validated['new_grade'],
                'status' => $validated['new_grade'] !== null ? 'completed' : $enrolledSubject->status,
            ]);
        }

        GradeChangeLog::create([
            'enrolled_subject_id' => $enrolledSubject->id,
            'grade_period' => $validated['grade_period'],
            'old_grade' => $oldGrade,
            'new_grade' => $validated['new_grade'],
            'reason' => $validated['reason'],
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

}
