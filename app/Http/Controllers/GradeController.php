<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\EnrolledSubject;
use App\Models\Enrollment;
use App\Models\ScheduledSubject;
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

        return Inertia::render('Grades/StudentView', [
            'enrollments' => $enrollments,
            'academicTerms' => $academicTerms,
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

        $classes = $query->get();

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
        $query = EnrolledSubject::with([
            'enrollment.student.user',
            'enrollment.academicTerm',
            'scheduledSubject.curriculumSubject.subject',
            'scheduledSubject.instructor.user'
        ]);

        // Filter by term
        if ($request->has('academic_term_id')) {
            $query->whereHas('enrollment', function ($q) use ($request) {
                $q->where('academic_term_id', $request->academic_term_id);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by student name
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('enrollment.student.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('official_id', 'like', "%{$search}%");
            });
        }

        $enrolledSubjects = $query->latest()->paginate(20);

        $academicTerms = AcademicTerm::latest('academic_year')->get();

        return Inertia::render('Grades/RegistrarView', [
            'enrolledSubjects' => $enrolledSubjects,
            'academicTerms' => $academicTerms,
            'filters' => $request->only(['academic_term_id', 'status', 'search']),
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

        // Check if grade submission is open (unless registrar)
        if ($user->role !== 'registrar') {
            $gradeType = $request->input('grade_type');

            if ($gradeType === 'midterm' && !$term->isMidGradeOpen()) {
                return back()->with('error', 'Midterm grade submission period is not open.');
            }

            if ($gradeType === 'final' && !$term->isFinalGradeOpen()) {
                return back()->with('error', 'Final grade submission period is not open.');
            }
        }

        $validated = $request->validate([
            'grade_type' => 'required|in:midterm,final',
            'grades' => 'required|array',
            'grades.*.enrolled_subject_id' => 'required|exists:enrolled_subjects,id',
            'grades.*.grade' => 'nullable|numeric|min:0|max:100',
        ]);

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
            'midterm_grade' => 'nullable|numeric|min:0|max:100',
            'final_grade' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|in:enrolled,dropped,completed',
        ]);

        $enrolledSubject->update($validated);

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
