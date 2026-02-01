<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Block;
use App\Models\CurriculumSubject;
use App\Models\Instructor;
use App\Models\ScheduledSubject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScheduledSubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = ScheduledSubject::with([
            'academicTerm',
            'block.program',
            'instructor.user',
            'curriculumSubject.subject'
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

        // Filter by block
        if ($request->has('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        // Filter by instructor
        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        $scheduledSubjects = $query->latest()->paginate(15);

        $academicTerms = AcademicTerm::latest('academic_year')->get();
        $blocks = Block::active()->with('program')->get();
        $instructors = Instructor::with('user')->active()->get();

        return Inertia::render('ScheduledSubjects/Index', [
            'scheduledSubjects' => $scheduledSubjects,
            'academicTerms' => $academicTerms,
            'blocks' => $blocks,
            'instructors' => $instructors,
            'filters' => $request->only(['academic_term_id', 'block_id', 'instructor_id']),
        ]);
    }

    public function create()
    {
        $academicTerms = AcademicTerm::latest('academic_year')->get();
        $activeTerm = AcademicTerm::where('is_active', true)->first();
        $blocks = Block::active()->with('program')->get();
        $instructors = Instructor::with('user')->active()->get()
            ->map(function ($instructor) {
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->user->first_name . ' ' . $instructor->user->last_name,
                    'specialization' => $instructor->specialization,
                ];
            });

        return Inertia::render('ScheduledSubjects/Create', [
            'academicTerms' => $academicTerms,
            'activeTerm' => $activeTerm,
            'blocks' => $blocks,
            'instructors' => $instructors,
        ]);
    }

    // API endpoint to get curriculum subjects for a block
    public function getCurriculumSubjects(Request $request)
    {
        $blockId = $request->input('block_id');
        $block = Block::with('program.curriculums')->findOrFail($blockId);

        // Get active curriculum for the program
        $curriculum = $block->program->curriculums()->where('is_active', true)->first();

        if (!$curriculum) {
            return response()->json(['subjects' => []]);
        }

        $curriculumSubjects = CurriculumSubject::where('curriculum_id', $curriculum->id)
            ->with('subject')
            ->get()
            ->map(function ($cs) {
                return [
                    'id' => $cs->id,
                    'subject_code' => $cs->subject->code,
                    'subject_title' => $cs->subject->title,
                    'units' => $cs->subject->units,
                    'year_level' => $cs->year_level,
                    'semester' => $cs->semester,
                    'has_laboratory' => $cs->has_laboratory,
                ];
            });

        return response()->json(['subjects' => $curriculumSubjects]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'day' => 'required|string|max:20',
            'room' => 'required|string|max:50',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'block_id' => 'required|exists:blocks,id',
            'instructor_id' => 'nullable|exists:instructors,id',
            'curriculum_subject_id' => 'required|exists:curriculum_subjects,id',
        ]);

        // Check for scheduling conflicts
        $conflict = ScheduledSubject::where('academic_term_id', $validated['academic_term_id'])
            ->where(function ($query) use ($validated) {
                // Same block at same time
                $query->where('block_id', $validated['block_id'])
                    ->where('day', $validated['day'])
                    ->where(function ($q) use ($validated) {
                        $q->whereBetween('time_start', [$validated['time_start'], $validated['time_end']])
                            ->orWhereBetween('time_end', [$validated['time_start'], $validated['time_end']])
                            ->orWhere(function ($q2) use ($validated) {
                                $q2->where('time_start', '<=', $validated['time_start'])
                                    ->where('time_end', '>=', $validated['time_end']);
                            });
                    });
            });

        // Only check instructor conflict if one is assigned
        if ($validated['instructor_id']) {
            $conflict->orWhere(function ($query) use ($validated) {
                $query->where('instructor_id', $validated['instructor_id'])
                    ->where('day', $validated['day'])
                    ->where(function ($q) use ($validated) {
                        $q->whereBetween('time_start', [$validated['time_start'], $validated['time_end']])
                            ->orWhereBetween('time_end', [$validated['time_start'], $validated['time_end']])
                            ->orWhere(function ($q2) use ($validated) {
                                $q2->where('time_start', '<=', $validated['time_start'])
                                    ->where('time_end', '>=', $validated['time_end']);
                            });
                    });
            });
        }

        $conflict->orWhere(function ($query) use ($validated) {
            // Same room at same time
            $query->where('room', $validated['room'])
                ->where('day', $validated['day'])
                ->where(function ($q) use ($validated) {
                    $q->whereBetween('time_start', [$validated['time_start'], $validated['time_end']])
                        ->orWhereBetween('time_end', [$validated['time_start'], $validated['time_end']])
                        ->orWhere(function ($q2) use ($validated) {
                            $q2->where('time_start', '<=', $validated['time_start'])
                                ->where('time_end', '>=', $validated['time_end']);
                        });
                });
        });

        if ($conflict->exists()) {
            return back()->with('error', 'Scheduling conflict detected. Please check block, instructor, or room availability.');
        }

        ScheduledSubject::create($validated);

        return back()->with('success', 'You have successfully created a new subject schedule.');
    }

    public function show(ScheduledSubject $scheduledSubject)
    {
        $scheduledSubject->load([
            'academicTerm',
            'block.program',
            'instructor.user',
            'curriculumSubject.subject',
            'enrolledSubjects' => function ($query) {
                $query->with('enrollment.student.user');
            }
        ]);

        return Inertia::render('ScheduledSubjects/Show', [
            'scheduledSubject' => $scheduledSubject,
        ]);
    }

    public function edit(ScheduledSubject $scheduledSubject)
    {
        $scheduledSubject->load('curriculumSubject.subject');

        $academicTerms = AcademicTerm::latest('academic_year')->get();
        $blocks = Block::active()->with('program')->get();
        $instructors = Instructor::with('user')->active()->get()
            ->map(function ($instructor) {
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->user->first_name . ' ' . $instructor->user->last_name,
                    'specialization' => $instructor->specialization,
                ];
            });

        return Inertia::render('ScheduledSubjects/Edit', [
            'scheduledSubject' => $scheduledSubject,
            'academicTerms' => $academicTerms,
            'blocks' => $blocks,
            'instructors' => $instructors,
        ]);
    }

    public function update(Request $request, ScheduledSubject $scheduledSubject)
    {
        $validated = $request->validate([
            'day' => 'required|string|max:20',
            'room' => 'required|string|max:50',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'instructor_id' => 'nullable|exists:instructors,id',
        ]);

        // Check for scheduling conflicts (excluding current schedule)
        $conflict = ScheduledSubject::where('id', '!=', $scheduledSubject->id)
            ->where('academic_term_id', $scheduledSubject->academic_term_id)
            ->where('block_id', $scheduledSubject->block_id)
            ->where('day', $validated['day'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('time_start', [$validated['time_start'], $validated['time_end']])
                    ->orWhereBetween('time_end', [$validated['time_start'], $validated['time_end']])
                    ->orWhere(function ($q2) use ($validated) {
                        $q2->where('time_start', '<=', $validated['time_start'])
                            ->where('time_end', '>=', $validated['time_end']);
                    });
            })
            ->exists();

        if ($conflict) {
            return back()->with('error', 'Scheduling conflict detected.');
        }

        $scheduledSubject->update($validated);

        return back()->with('success', 'You have successfully updated the subject schedule.');
    }

    public function destroy(ScheduledSubject $scheduledSubject)
    {
        // Check if there are enrolled subjects
        if ($scheduledSubject->enrolledSubjects()->count() > 0) {
            return back()->with('error', 'Cannot delete schedule with enrolled students.');
        }

        $scheduledSubject->delete();

        return back()->with('success', 'Successfully dropped a subject schedule.');
    }
}
