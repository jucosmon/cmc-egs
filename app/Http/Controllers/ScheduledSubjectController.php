<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Block;
use App\Models\CurriculumSubject;
use App\Models\Instructor;
use App\Models\ScheduledSubject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'day' => ['required', 'string', 'max:20', Rule::in([
                'MWF', 'TTH', 'MW', 'TH',
                'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY',
                'M', 'T', 'W', 'F', 'S'
            ])],
            'room' => 'required|string|max:50',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'academic_term_id' => 'required|exists:academic_terms,id',
            'block_id' => 'required|exists:blocks,id',
            'instructor_id' => 'required|exists:instructors,id',
            'curriculum_subject_id' => 'required|exists:curriculum_subjects,id',
        ]);

        $daySet = $this->normalizeDaySet($validated['day']);
        if (empty($daySet)) {
            return back()->withErrors([
                'schedule' => 'Invalid day selection.',
            ])->withInput();
        }

        $duplicateSubject = ScheduledSubject::where('academic_term_id', $validated['academic_term_id'])
            ->where('block_id', $validated['block_id'])
            ->where('curriculum_subject_id', $validated['curriculum_subject_id'])
            ->exists();

        if ($duplicateSubject) {
            return back()->withErrors([
                'schedule' => 'This subject is already scheduled for the block in this term.',
            ])->withInput();
        }

        $blockSchedules = ScheduledSubject::where('academic_term_id', $validated['academic_term_id'])
            ->where('block_id', $validated['block_id'])
            ->get(['day', 'time_start', 'time_end']);

        if ($this->hasScheduleConflict($blockSchedules, $daySet, $validated['time_start'], $validated['time_end'])) {
            return back()->withErrors([
                'schedule' => 'Scheduling conflict detected in this block.',
            ])->withInput();
        }

        if ($validated['instructor_id']) {
            $instructorSchedules = ScheduledSubject::where('academic_term_id', $validated['academic_term_id'])
                ->where('instructor_id', $validated['instructor_id'])
                ->get(['day', 'time_start', 'time_end']);

            if ($this->hasScheduleConflict($instructorSchedules, $daySet, $validated['time_start'], $validated['time_end'])) {
                return back()->withErrors([
                    'schedule' => 'Instructor schedule conflict detected.',
                ])->withInput();
            }
        }

        $roomSchedules = ScheduledSubject::where('academic_term_id', $validated['academic_term_id'])
            ->where('room', $validated['room'])
            ->get(['day', 'time_start', 'time_end']);

        if ($this->hasScheduleConflict($roomSchedules, $daySet, $validated['time_start'], $validated['time_end'])) {
            return back()->withErrors([
                'schedule' => 'Room schedule conflict detected.',
            ])->withInput();
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
            'day' => ['required', 'string', 'max:20', Rule::in([
                'MWF', 'TTH', 'MW', 'TH',
                'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY',
                'M', 'T', 'W', 'F', 'S'
            ])],
            'room' => 'required|string|max:50',
            'time_start' => 'required|date_format:H:i',
            'time_end' => 'required|date_format:H:i|after:time_start',
            'instructor_id' => 'required|exists:instructors,id',
        ]);

        $daySet = $this->normalizeDaySet($validated['day']);
        if (empty($daySet)) {
            return back()->withErrors([
                'schedule' => 'Invalid day selection.',
            ])->withInput();
        }

        $blockSchedules = ScheduledSubject::where('id', '!=', $scheduledSubject->id)
            ->where('academic_term_id', $scheduledSubject->academic_term_id)
            ->where('block_id', $scheduledSubject->block_id)
            ->get(['day', 'time_start', 'time_end']);

        if ($this->hasScheduleConflict($blockSchedules, $daySet, $validated['time_start'], $validated['time_end'])) {
            return back()->withErrors([
                'schedule' => 'Scheduling conflict detected in this block.',
            ])->withInput();
        }

        if ($validated['instructor_id']) {
            $instructorSchedules = ScheduledSubject::where('id', '!=', $scheduledSubject->id)
                ->where('academic_term_id', $scheduledSubject->academic_term_id)
                ->where('instructor_id', $validated['instructor_id'])
                ->get(['day', 'time_start', 'time_end']);

            if ($this->hasScheduleConflict($instructorSchedules, $daySet, $validated['time_start'], $validated['time_end'])) {
                return back()->withErrors([
                    'schedule' => 'Instructor schedule conflict detected.',
                ])->withInput();
            }
        }

        $roomSchedules = ScheduledSubject::where('id', '!=', $scheduledSubject->id)
            ->where('academic_term_id', $scheduledSubject->academic_term_id)
            ->where('room', $validated['room'])
            ->get(['day', 'time_start', 'time_end']);

        if ($this->hasScheduleConflict($roomSchedules, $daySet, $validated['time_start'], $validated['time_end'])) {
            return back()->withErrors([
                'schedule' => 'Room schedule conflict detected.',
            ])->withInput();
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

    private function normalizeDaySet(string $day): array
    {
        $key = strtoupper($day);
        $key = preg_replace('/[^A-Z]/', '', $key);

        $map = [
            'MWF' => ['MONDAY', 'WEDNESDAY', 'FRIDAY'],
            'TTH' => ['TUESDAY', 'THURSDAY'],
            'MW' => ['MONDAY', 'WEDNESDAY'],
            'TH' => ['THURSDAY'],
            'M' => ['MONDAY'],
            'T' => ['TUESDAY'],
            'W' => ['WEDNESDAY'],
            'F' => ['FRIDAY'],
            'S' => ['SATURDAY'],
            'MTWTF' => ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY'],
            'MTWTFSS' => ['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'],
            'MONDAY' => ['MONDAY'],
            'TUESDAY' => ['TUESDAY'],
            'WEDNESDAY' => ['WEDNESDAY'],
            'THURSDAY' => ['THURSDAY'],
            'FRIDAY' => ['FRIDAY'],
            'SATURDAY' => ['SATURDAY'],
        ];

        return $map[$key] ?? [];
    }

    private function hasScheduleConflict($schedules, array $daySet, string $timeStart, string $timeEnd): bool
    {
        foreach ($schedules as $schedule) {
            $existingDays = $this->normalizeDaySet($schedule->day);
            if (empty(array_intersect($daySet, $existingDays))) {
                continue;
            }

            if ($this->timesOverlap($timeStart, $timeEnd, $schedule->time_start, $schedule->time_end)) {
                return true;
            }
        }

        return false;
    }

    private function timesOverlap(string $startA, string $endA, string $startB, string $endB): bool
    {
        $startMinutesA = $this->timeToMinutes($startA);
        $endMinutesA = $this->timeToMinutes($endA);
        $startMinutesB = $this->timeToMinutes($startB);
        $endMinutesB = $this->timeToMinutes($endB);

        return $startMinutesA < $endMinutesB && $endMinutesA > $startMinutesB;
    }

    private function timeToMinutes(string $time): int
    {
        if ($time instanceof \DateTimeInterface) {
            $hours = (int) $time->format('H');
            $minutes = (int) $time->format('i');
            return ($hours * 60) + $minutes;
        }

        $value = (string) $time;
        if (preg_match('/(\d{1,2}):(\d{2})/', $value, $matches)) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];
            return ($hours * 60) + $minutes;
        }

        return 0;
    }
}
