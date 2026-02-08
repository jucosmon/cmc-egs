<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Program;
use App\Models\ScheduledSubject;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProgramHeadController extends Controller
{
    public function instructorLoads()
    {
        $user = Auth::user();

        $program = Program::with('department')
            ->where('program_head_id', $user->id)
            ->first();

        if (!$program) {
            abort(403, 'No program assigned to this Program Head.');
        }

        $activeTerm = AcademicTerm::where('is_active', true)->first();

        $scheduledSubjects = ScheduledSubject::query()
            ->whereHas('block', function ($query) use ($program) {
                $query->where('program_id', $program->id);
            })
            ->when($activeTerm, function ($query) use ($activeTerm) {
                $query->where('academic_term_id', $activeTerm->id);
            })
            ->with([
                'block',
                'curriculumSubject.subject',
                'instructor.user',
                'enrolledSubjects',
            ])
            ->get();

        $instructorLoads = $scheduledSubjects
            ->groupBy(function ($subject) {
                return $subject->instructor_id ?: 0;
            })
            ->map(function ($subjects, $instructorId) {
                $first = $subjects->first();
                $instructor = $first->instructor;

                $name = $instructor
                    ? $instructor->user->full_name
                    : 'Unassigned';

                $details = $subjects->map(function ($subject) {
                    $units = $subject->curriculumSubject?->subject?->units ?? 0;
                    $statusCounts = $subject->enrolledSubjects
                        ->groupBy('status')
                        ->map(function ($items) {
                            return $items->count();
                        });

                    return [
                        'id' => $subject->id,
                        'subject_code' => $subject->curriculumSubject?->subject?->code ?? 'N/A',
                        'subject_title' => $subject->curriculumSubject?->subject?->title ?? 'N/A',
                        'units' => $units,
                        'block_code' => $subject->block?->code ?? 'N/A',
                        'day' => $subject->day,
                        'time' => $subject->time_start . ' - ' . $subject->time_end,
                        'room' => $subject->room,
                        'enrolled_count' => $statusCounts->get('enrolled', 0),
                        'completed_count' => $statusCounts->get('completed', 0),
                        'dropped_count' => $statusCounts->get('dropped', 0),
                    ];
                });

                return [
                    'id' => $instructorId,
                    'name' => $name,
                    'official_id' => $instructor?->user?->official_id,
                    'total_subjects' => $details->count(),
                    'total_units' => $details->sum('units'),
                    'total_enrolled' => $details->sum('enrolled_count'),
                    'details' => $details,
                ];
            })
            ->values();

        return Inertia::render('ProgramHead/InstructorLoads', [
            'program' => $program,
            'activeTerm' => $activeTerm,
            'instructorLoads' => $instructorLoads,
        ]);
    }
}
