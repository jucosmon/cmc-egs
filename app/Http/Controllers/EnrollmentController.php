<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Block;
use App\Models\EnrolledSubject;
use App\Models\Enrollment;
use App\Models\ScheduledSubject;
use App\Models\Student;
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
        if (!$term->isEnrollmentOpen() && $user->role !== 'registrar') {
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
}
