<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\ScheduledSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClassController extends Controller
{
        /**
     * Display instructor's classes
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $instructor = $user->instructor;

        $query = ScheduledSubject::where('instructor_id', $instructor->id)
            ->with([
                'academicTerm',
                'block.program',
                'curriculumSubject.subject',
                'enrolledSubjects' => function ($q) {
                    $q->where('status', '!=', 'dropped');
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

        // Filter by day
        if ($request->has('day')) {
            $query->where('day', 'like', '%' . $request->day . '%');
        }

        $classes = $query->get()->map(function ($class) {
            return [
                'id' => $class->id,
                'subject_code' => $class->curriculumSubject->subject->code,
                'subject_title' => $class->curriculumSubject->subject->title,
                'units' => $class->curriculumSubject->subject->units,
                'block_code' => $class->block->code,
                'program_name' => $class->block->program->name,
                'day' => $class->day,
                'time' => $class->time_start . ' - ' . $class->time_end,
                'room' => $class->room,
                'student_count' => $class->enrolledSubjects->count(),
                'academic_term' => $class->academicTerm->academic_year . ' - ' . ucfirst($class->academicTerm->semester),
            ];
        });

        $academicTerms = AcademicTerm::latest('academic_year')->get();
        $activeTerm = AcademicTerm::where('is_active', true)->first();

        return Inertia::render('Classes/Index', [
            'classes' => $classes,
            'academicTerms' => $academicTerms,
            'activeTerm' => $activeTerm,
            'filters' => $request->only(['academic_term_id', 'day']),
        ]);
    }

    /**
     * Show class details with student list
     */
    public function show(ScheduledSubject $class)
    {
        $user = Auth::user();

        // Verify instructor owns this class
        if ($class->instructor_id !== $user->instructor->id) {
            abort(403, 'Unauthorized access.');
        }

        $class->load([
            'academicTerm',
            'block.program',
            'curriculumSubject.subject',
            'instructor.user',
            'enrolledSubjects' => function ($q) {
                $q->with('enrollment.student.user')
                    ->orderByRaw("CASE
                        WHEN status = 'enrolled' THEN 1
                        WHEN status = 'completed' THEN 2
                        WHEN status = 'dropped' THEN 3
                    END");
            }
        ]);

        // Get statistics
        $stats = [
            'total_enrolled' => $class->enrolledSubjects()->where('status', '!=', 'dropped')->count(),
            'total_dropped' => $class->enrolledSubjects()->where('status', 'dropped')->count(),
            'midterm_submitted' => $class->enrolledSubjects()->whereNotNull('midterm_grade')->count(),
            'final_submitted' => $class->enrolledSubjects()->whereNotNull('final_grade')->count(),
            'passing_rate' => 0,
        ];

        // Calculate passing rate
        $totalGraded = $class->enrolledSubjects()->whereNotNull('final_grade')->count();
        if ($totalGraded > 0) {
            $passing = $class->enrolledSubjects()
                ->whereNotNull('final_grade')
                ->where('final_grade', '<=', 3.0)
                ->count();
            $stats['passing_rate'] = round(($passing / $totalGraded) * 100, 2);
        }

        return Inertia::render('Classes/Show', [
            'class' => $class,
            'stats' => $stats,
        ]);
    }

    /**
     * Show attendance/participation tracking (Optional feature)
     */
    public function attendance(ScheduledSubject $class)
    {
        $user = Auth::user();

        if ($class->instructor_id !== $user->instructor->id) {
            abort(403, 'Unauthorized access.');
        }

        $class->load([
            'academicTerm',
            'block',
            'curriculumSubject.subject',
            'enrolledSubjects' => function ($q) {
                $q->with('enrollment.student.user')
                    ->where('status', '!=', 'dropped');
            }
        ]);

        return Inertia::render('Classes/Attendance', [
            'class' => $class,
        ]);
    }

}
