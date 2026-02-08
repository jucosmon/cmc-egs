<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Block;
use App\Models\EnrolledSubject;
use App\Models\Enrollment;
use App\Models\ScheduledSubject;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Program heads should use the enrollment management interface
        if ($user->role === 'program_head') {
            return app(BlockController::class)->index($request);
        }

        // STUDENT VIEW
        if ($user->role === 'student') {
            return $this->studentView();
        }

        // REGISTRAR / PROGRAM HEAD MANAGE VIEW
        if (in_array($user->role, ['registrar', 'program_head'])) {
            return $this->registrarManage($request);
        }


        $query = Enrollment::with([
            'student.user',
            'academicTerm',
            'block',
            'enrolledSubjects.scheduledSubject.curriculumSubject.subject'
        ]);

        // Role-based filtering
        if ($user->role === 'student') {
            // Students see only their enrollments
            $student = $user->student;
            $query->where('student_id', $student->id);
        }
        // Registrars see all enrollments (no additional filter)

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

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->latest()->paginate(15);

        $academicTerms = AcademicTerm::latest('academic_year')->get();
        $activeTerm = AcademicTerm::where('is_active', true)->first();

        return Inertia::render('Enrollments/Index', [
            'enrollments' => $enrollments,
            'academicTerms' => $academicTerms,
            'activeTerm' => $activeTerm,
            'filters' => $request->only(['academic_term_id', 'status']),
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        // Check if user is a student
        if ($user->role !== 'student' && $user->role !== 'registrar') {
            return back()->with('error', 'Only students and registrars can create enrollments.');
        }

        $activeTerm = AcademicTerm::where('is_active', true)->first();

        if (!$activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        if (!$activeTerm->isEnrollmentOpen()) {
            return back()->with('error', 'Enrollment period is not currently open.');
        }

        // For registrar, get all students; for student, get their own data
        if ($user->role === 'registrar') {
            $students = Student::with(['user', 'program', 'block'])->get();
            $student = null;
        } else {
            $students = null;
            $student = $user->student;

            // Check if already enrolled in active term
            $existingEnrollment = Enrollment::where('student_id', $student->id)
                ->where('academic_term_id', $activeTerm->id)
                ->first();

            if ($existingEnrollment) {
                return redirect()->route('enrollments.show', $existingEnrollment)
                    ->with('info', 'You are already enrolled in this term.');
            }
        }

        return Inertia::render('Enrollments/Create', [
            'activeTerm' => $activeTerm,
            'students' => $students,
            'student' => $student,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'block_id' => 'required|exists:blocks,id',
            'year_level' => 'required|integer|min:1|max:5',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:scheduled_subjects,id',
        ]);

        // Check if student is already enrolled in this term
        $existing = Enrollment::where('student_id', $validated['student_id'])
            ->where('academic_term_id', $validated['academic_term_id'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Student is already enrolled in this term.');
        }

        // Verify enrollment period is open
        $term = AcademicTerm::findOrFail($validated['academic_term_id']);
        if (!$term->isEnrollmentOpen()) {
            return back()->with('error', 'Enrollment period is not currently open.');
        }

        DB::beginTransaction();
        try {
            // Create enrollment
            $enrollment = Enrollment::create([
                'student_id' => $validated['student_id'],
                'academic_term_id' => $validated['academic_term_id'],
                'block_id' => $validated['block_id'],
                'year_level' => $validated['year_level'],
                'status' => 'enrolled',
                'enrolled_at' => now(),
            ]);

            // Create enrolled subjects
            foreach ($validated['subject_ids'] as $scheduledSubjectId) {
                EnrolledSubject::create([
                    'enrollment_id' => $enrollment->id,
                    'scheduled_subject_id' => $scheduledSubjectId,
                    'status' => 'enrolled',
                ]);
            }

            // Update student's year level if different
            $student = Student::findOrFail($validated['student_id']);
            if ($student->year_level != $validated['year_level']) {
                $student->update(['year_level' => $validated['year_level']]);
            }

            DB::commit();

            return redirect()->route('enrollments.show', $enrollment)
                ->with('success', 'Enrollment created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create enrollment: ' . $e->getMessage());
        }
    }

    public function show(Enrollment $enrollment)
    {
        $user = Auth::user();

        // Check access
        if ($user->role === 'student' && $enrollment->student->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $enrollment->load([
            'student.user',
            'student.program',
            'academicTerm',
            'block',
            'enrolledSubjects' => function ($query) {
                $query->with([
                    'scheduledSubject.curriculumSubject.subject',
                    'scheduledSubject.instructor.user'
                ]);
            }
        ]);

        // Calculate total units
        $totalUnits = $enrollment->enrolledSubjects
            ->map(function ($enrolledSubject) {
                return $enrolledSubject->scheduledSubject->curriculumSubject->subject->units ?? 0;
            })
            ->sum();


        return Inertia::render('Enrollments/Show', [
            'enrollment' => $enrollment,
            'totalUnits' => $totalUnits,
        ]);
    }

    // Get available subjects for enrollment
    public function getAvailableSubjects(Request $request)
    {
        $blockId = $request->input('block_id');
        $termId = $request->input('academic_term_id');
        $studentId = $request->input('student_id');

        $student = Student::with('program')->findOrFail($studentId);
        $block = Block::with('program.curriculums')->findOrFail($blockId);

        // Get active curriculum
        $curriculum = $block->program->curriculums()->where('is_active', true)->first();

        if (!$curriculum) {
            return response()->json(['subjects' => []]);
        }

        // Get scheduled subjects for this block and term
        $scheduledSubjects = ScheduledSubject::where('block_id', $blockId)
            ->where('academic_term_id', $termId)
            ->with(['curriculumSubject.subject', 'instructor.user'])
            ->get()
            ->map(function ($ss) {
                return [
                    'id' => $ss->id,
                    'subject_code' => $ss->curriculumSubject->subject->code,
                    'subject_title' => $ss->curriculumSubject->subject->title,
                    'units' => $ss->curriculumSubject->subject->units,
                    'day' => $ss->day,
                    'time' => $ss->time_start . ' - ' . $ss->time_end,
                    'room' => $ss->room,
                    'instructor' => $ss->instructor->user->first_name . ' ' . $ss->instructor->user->last_name,
                ];
            });

        return response()->json(['subjects' => $scheduledSubjects]);
    }

    public function destroy(Enrollment $enrollment)
    {
        $user = Auth::user();

        // Only registrar can delete enrollments
        if ($user->role !== 'registrar') {
            return back()->with('error', 'Only registrars can delete enrollments.');
        }

        // Check if enrollment has grades
        $hasGrades = $enrollment->enrolledSubjects()
            ->where(function ($query) {
                $query->whereNotNull('midterm_grade')
                    ->orWhereNotNull('final_grade');
            })
            ->exists();

        if ($hasGrades) {
            return back()->with('error', 'Cannot delete enrollment with existing grades.');
        }

        DB::beginTransaction();
        try {
            // Delete enrolled subjects first
            $enrollment->enrolledSubjects()->delete();

            // Delete enrollment
            $enrollment->delete();

            DB::commit();

            return redirect()->route('enrollments.index')
                ->with('success', 'Enrollment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete enrollment: ' . $e->getMessage());
        }
    }


        /**
     * Registrar manage enrollment page
     */
    public function registrarManage(Request $request)
    {
        $studentId = $request->input('student_id');
        $student = null;
        $enrollment = null;
        $blockSchedules = [];
        $enrolledSubjects = [];
        $searchMessage = null;
        $activeTerm = AcademicTerm::where('is_active', true)->first();
        $enrollmentOpen = $activeTerm ? $activeTerm->isEnrollmentOpen() : false;

        if ($studentId) {
            // Find student by official_id
            $user = User::where('official_id', $studentId)->first();

            if ($user && $user->student) {
                $student = $user->student;
                $student->load(['user', 'program', 'block']);

                if ($activeTerm) {
                    // Check if enrollment exists
                    $enrollment = Enrollment::where('student_id', $student->id)
                        ->where('academic_term_id', $activeTerm->id)
                        ->first();

                    if ($enrollment) {
                        // Get block schedules
                        if ($student->block_id) {
                            $enrolledSubjectIds = $enrollment->enrolledSubjects()
                                ->pluck('scheduled_subject_id')
                                ->toArray();

                            $blockSchedules = ScheduledSubject::where('block_id', $student->block_id)
                                ->where('academic_term_id', $activeTerm->id)
                                ->with([
                                    'curriculumSubject.subject',
                                    'instructor.user'
                                ])
                                ->get()
                                ->map(function ($schedule) use ($enrolledSubjectIds) {
                                    $schedule->is_enrolled = in_array($schedule->id, $enrolledSubjectIds);
                                    return $schedule;
                                });
                        }

                        // Get enrolled subjects
                        $enrolledSubjects = $enrollment->enrolledSubjects()
                            ->with(['scheduledSubject.curriculumSubject.subject'])
                            ->where('status', '!=', 'dropped')
                            ->get();
                    }
                } else {
                    $searchMessage = 'No active academic term found.';
                }
            } else {
                $searchMessage = 'Student not found.';
            }
        }

        return Inertia::render('Enrollments/RegistrarManage', [
            'student' => $student,
            'enrollment' => $enrollment,
            'blockSchedules' => $blockSchedules,
            'enrolledSubjects' => $enrolledSubjects,
            'searchedId' => $studentId,
            'enrollmentOpen' => $enrollmentOpen,
            'searchMessage' => $searchMessage,
        ]);
    }

    /**
     * Create enrollment for student (Registrar)
     */
    public function registrarCreateEnrollment(Student $student)
    {
        $activeTerm = AcademicTerm::where('is_active', true)->first();

        if (!$activeTerm) {
            return back()->with('error', 'No active academic term found.');
        }

        if (!$activeTerm->isEnrollmentOpen()) {
            return back()->with('error', 'Enrollment period is already closed.');
        }

        // Check if already enrolled
        $existing = Enrollment::where('student_id', $student->id)
            ->where('academic_term_id', $activeTerm->id)
            ->first();

        if ($existing) {
            return back()->with('error', 'Student is already enrolled in this term.');
        }

        // Create enrollment
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'academic_term_id' => $activeTerm->id,
            'block_id' => $student->block_id,
            'year_level' => $student->year_level,
            'status' => 'enrolled',
            'enrolled_at' => now(),
        ]);

        return back()->with('success', 'You have successfully enrolled a student.');
    }

    /**
     * Enroll student in a subject (Registrar)
     */
    public function enrollSubject(Request $request, Enrollment $enrollment)
    {
        $validated = $request->validate([
            'scheduled_subject_id' => 'required|exists:scheduled_subjects,id',
        ]);

        $activeTerm = $enrollment->academicTerm;

        if (!$activeTerm || !$activeTerm->isEnrollmentOpen()) {
            return back()->with('error', 'Enrollment period is already closed.');
        }

        $scheduledSubject = ScheduledSubject::findOrFail($validated['scheduled_subject_id']);

        // Check if already enrolled
        $existing = EnrolledSubject::where('enrollment_id', $enrollment->id)
            ->where('scheduled_subject_id', $scheduledSubject->id)
            ->whereIn('status', ['enrolled', 'completed'])
            ->first();

        if ($existing) {
            return back()->with('error', 'Student is already enrolled in this subject.');
        }

        // Validate prerequisites
        $curriculumSubject = $scheduledSubject->curriculumSubject;
        $prerequisites = $curriculumSubject->prerequisites;

        foreach ($prerequisites as $prereq) {
            $hasCompleted = EnrolledSubject::whereHas('enrollment', function ($q) use ($enrollment) {
                    $q->where('student_id', $enrollment->student_id);
                })
                ->whereHas('scheduledSubject', function ($q) use ($prereq) {
                    $q->where('curriculum_subject_id', $prereq->id);
                })
                ->where('status', 'completed')
                ->where('final_grade', '>=', 75)
                ->exists();

            if (!$hasCompleted) {
                return back()->with('error', 'Student has not completed the prerequisite: ' . $prereq->subject->code);
            }
        }

        // Check schedule conflicts
        $conflicts = EnrolledSubject::where('enrollment_id', $enrollment->id)
            ->where('status', 'enrolled')
            ->whereHas('scheduledSubject', function ($q) use ($scheduledSubject) {
                $q->where('day', $scheduledSubject->day)
                    ->where(function ($query) use ($scheduledSubject) {
                        $query->whereBetween('time_start', [$scheduledSubject->time_start, $scheduledSubject->time_end])
                            ->orWhereBetween('time_end', [$scheduledSubject->time_start, $scheduledSubject->time_end])
                            ->orWhere(function ($q) use ($scheduledSubject) {
                                $q->where('time_start', '<=', $scheduledSubject->time_start)
                                    ->where('time_end', '>=', $scheduledSubject->time_end);
                            });
                    });
            })
            ->exists();

        if ($conflicts) {
            return back()->with('error', 'Schedule conflict detected.');
        }

        // Check unit limit (e.g., max 24 units)
        $currentUnits = $enrollment->enrolledSubjects()
            ->where('status', 'enrolled')
            ->get()
            ->sum(function ($es) {
                return $es->scheduledSubject->curriculumSubject->subject->units;
            });

        $newUnits = $scheduledSubject->curriculumSubject->subject->units;

        if ($currentUnits + $newUnits > 24) {
            return back()->with('error', 'Unit limit exceeded. Maximum is 24 units.');
        }

        // Enroll
        EnrolledSubject::create([
            'enrollment_id' => $enrollment->id,
            'scheduled_subject_id' => $scheduledSubject->id,
            'status' => 'enrolled',
        ]);

        $subjectName = $scheduledSubject->curriculumSubject->subject->title;

        return back()->with('success', "You have successfully enrolled the student in {$subjectName}.");
    }

    /**
     * Drop student from subject (Registrar)
     */
    public function dropSubject(EnrolledSubject $enrolledSubject)
    {
        // Check if grades exist
        if ($enrolledSubject->midterm_grade || $enrolledSubject->final_grade) {
            return back()->with('error', 'Cannot drop subject with existing grades.');
        }

        $subjectName = $enrolledSubject->scheduledSubject->curriculumSubject->subject->title;

        $term = $enrolledSubject->enrollment->academicTerm;

        if (!$term || !$term->isEnrollmentOpen()) {
            $enrolledSubject->update(['status' => 'dropped']);
            return back()->with('success', "You have successfully dropped the student from {$subjectName}.");
        }

        $enrolledSubject->delete();

        return back()->with('success', "You have successfully dropped the student from {$subjectName}.");
    }

    /**
     * Search subjects for adding (Registrar)
     */
    public function searchSubject(Request $request)
    {
        $validated = $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'subject_code' => 'nullable|string',
        ]);

        $enrollment = Enrollment::with(['student.program'])->findOrFail($validated['enrollment_id']);
        $activeTerm = $enrollment->academicTerm;
        $subjectCode = trim($validated['subject_code'] ?? '');

        $curriculum = $enrollment->student->program
            ->curriculums()
            ->where('is_active', true)
            ->first();

        if (!$curriculum) {
            return response()->json([
                'availableSchedules' => [],
                'status' => 'no_curriculum',
                'message' => 'No active curriculum found for this program.',
            ]);
        }

        $curriculumSubjectsQuery = $curriculum->curriculumSubjects()->with('subject');

        if ($subjectCode !== '') {
            $curriculumSubjectsQuery->whereHas('subject', function ($q) use ($subjectCode) {
                $q->where('code', 'like', "%{$subjectCode}%");
            });
        }

        $curriculumSubjects = $curriculumSubjectsQuery->get();

        if ($curriculumSubjects->isEmpty()) {
            $subjectExists = $subjectCode !== ''
                ? Subject::where('code', 'like', "%{$subjectCode}%")->exists()
                : false;

            return response()->json([
                'availableSchedules' => [],
                'status' => $subjectExists ? 'not_in_curriculum' : 'not_found',
                'message' => $subjectExists
                    ? 'Subject exists but is not part of the student\'s curriculum.'
                    : 'Subject code does not exist.',
            ]);
        }

        $scheduledSubjects = ScheduledSubject::where('academic_term_id', $activeTerm->id)
            ->whereIn('curriculum_subject_id', $curriculumSubjects->pluck('id'))
            ->with(['curriculumSubject.subject', 'block', 'instructor.user'])
            ->get()
            ->groupBy('curriculum_subject_id');

        $availableSchedules = $curriculumSubjects
            ->flatMap(function ($curriculumSubject) use ($scheduledSubjects) {
                $subject = $curriculumSubject->subject;
                $schedules = $scheduledSubjects->get($curriculumSubject->id, collect());

                if ($schedules->isEmpty()) {
                    return [[
                        'id' => null,
                        'curriculum_subject_id' => $curriculumSubject->id,
                        'subject_code' => $subject->code,
                        'subject_title' => $subject->title,
                        'day' => null,
                        'time_start' => null,
                        'time_end' => null,
                        'room' => null,
                        'block_code' => null,
                        'has_schedule' => false,
                    ]];
                }

                return $schedules->map(function ($schedule) {
                    return [
                        'id' => $schedule->id,
                        'curriculum_subject_id' => $schedule->curriculum_subject_id,
                        'subject_code' => $schedule->curriculumSubject->subject->code,
                        'subject_title' => $schedule->curriculumSubject->subject->title,
                        'day' => $schedule->day,
                        'time_start' => $schedule->time_start,
                        'time_end' => $schedule->time_end,
                        'room' => $schedule->room,
                        'block_code' => optional($schedule->block)->code,
                        'has_schedule' => true,
                    ];
                });
            })
            ->values();

        return response()->json([
            'availableSchedules' => $availableSchedules,
            'status' => 'found',
            'message' => $availableSchedules->isEmpty() ? 'No matching subjects found.' : null,
        ]);
    }

    /**
     * Student enrollment view
     */
    public function studentView()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $activeTerm = AcademicTerm::where('is_active', true)->first();

        if (!$activeTerm) {
            return Inertia::render('Enrollments/StudentView', [
                'enrollment' => null,
                'message' => 'No active academic term.',
            ]);
        }



        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('academic_term_id', $activeTerm->id)
            ->with([
                'academicTerm',
                'block',
                'student.program',
                'enrolledSubjects' => function ($q) {
                    $q->with([
                        'scheduledSubject.curriculumSubject.subject',
                        'scheduledSubject.instructor.user'
                    ])
                    ->where('status', '!=', 'dropped');
                }
            ])
            ->first();

        if (!$enrollment && !$activeTerm->isEnrollmentOpen()) {
            return Inertia::render('Enrollments/StudentView', [
                'enrollment' => null,
                'message' => 'Enrollment period is closed.',
            ]);
        }
        if (!$enrollment) {
            return Inertia::render('Enrollments/StudentView', [
                'enrollment' => null,
                'message' => 'Not yet enrolled. Please contact the Registrar.',
            ]);
        }

        return Inertia::render('Enrollments/StudentView', [
            'enrollment' => $enrollment,
            'message' => null,
        ]);
    }
}
