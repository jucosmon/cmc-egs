<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Block;
use App\Models\CurriculumSubject;
use App\Models\Instructor;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $showArchived = $request->boolean('show_archived');
        $query = Block::query()->with('program');
        $programsQuery = Program::active();

        /** PROGRAM HEAD FILTER */
        if ($user->role === 'program_head') {
            $programIds = Program::where('program_head_id', $user->id)->pluck('id');

            if ($programIds->isEmpty()) {
                return Inertia::render('Enrollments/ProgramHeadManage', [
                    'blocks' => Block::query()->whereNull('id')->paginate(15),
                    'programs' => [],
                    'activeTerm' => AcademicTerm::where('is_active', true)->first(),
                    'instructors' => Instructor::with('user')->active()->get(),
                    'filters' => $request->only(['program_id', 'status', 'admission_year']) + [
                        'show_archived' => $showArchived,
                    ],
                    'noProgramAssigned' => true,
                ]);
            }

            $query->whereIn('program_id', $programIds);
            $programsQuery->whereIn('id', $programIds);
        }

        /** OPTIONAL FILTERS */
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } elseif (!$showArchived) {
            $query->where('status', '!=', 'inactive');
        }

        if ($request->filled('admission_year')) {
            $query->where('admission_year', $request->admission_year);
        }

        $blocks = $query
            ->with([
                'students.user',
                'scheduledSubjects.academicTerm',
                'scheduledSubjects.instructor.user',
                'scheduledSubjects.curriculumSubject.subject',
            ])
            ->withCount('students')
            ->latest()
            ->paginate(15);

        return Inertia::render('Enrollments/ProgramHeadManage', [
            'blocks' => $blocks,
            'programs' => $programsQuery->get(['id', 'name', 'code']),
            'activeTerm' => AcademicTerm::where('is_active', true)->first(),
            'instructors' => Instructor::with('user')->active()->get(),
            'filters' => $request->only(['program_id', 'status', 'admission_year']) + [
                'show_archived' => $showArchived,
            ],
            'noProgramAssigned' => false,
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        $programs = $user->role === 'program_head'
            ? Program::active()->where('program_head_id', $user->id)->get(['id', 'name', 'code'])
            : Program::active()->get(['id', 'name', 'code']);

        return Inertia::render('Blocks/Create', compact('programs'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'code' => 'nullable|string|max:20|unique:blocks,code',
            'program_id' => 'required|exists:programs,id',
            'admission_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'max_students' => 'nullable|integer|min:1|max:50',
        ]);

        if ($user->role === 'program_head') {
            $programId = Program::where('program_head_id', $user->id)->value('id');
            if (!$programId) {
                abort(403, 'Unauthorized program.');
            }
            $validated['program_id'] = $programId;
        } else {
            if (!Program::where('id', $validated['program_id'])->exists()) {
                abort(403, 'Unauthorized program.');
            }
        }

        $program = Program::findOrFail($validated['program_id']);
        $validated['code'] = $this->generateBlockCode(
            $program,
            (int) $validated['admission_year']
        );
        $validated['status'] = 'active';
        $validated['max_students'] = (int) ($validated['max_students'] ?? 50);

        Block::create($validated);

        return redirect()->route('blocks.index')->with('success', 'Block created successfully.');
    }

    private function generateBlockCode(Program $program, int $admissionYear, ?int $excludeBlockId = null): string
    {
        $base = $program->code . '-' . $admissionYear . '-';

        $existingCodes = Block::where('program_id', $program->id)
            ->where('admission_year', $admissionYear)
            ->when($excludeBlockId, function ($query, $excludeBlockId) {
                $query->where('id', '!=', $excludeBlockId);
            })
            ->pluck('code');

        $usedLetters = $existingCodes->map(function ($code) use ($base) {
            if (str_starts_with($code, $base)) {
                $suffix = strtoupper(substr($code, strlen($base)));
                return $suffix !== '' ? $suffix[0] : null;
            }
            return null;
        })->filter()->values();

        $used = $usedLetters->flip();
        foreach (range('A', 'Z') as $letter) {
            if (!$used->has($letter)) {
                return $base . $letter;
            }
        }

        abort(422, 'Block code limit reached for this program and year.');
    }

    public function show(Block $block)
    {
        $block->load([
            'program.department',
            'students.user',
            'scheduledSubjects.academicTerm',
            'scheduledSubjects.instructor.user',
            'scheduledSubjects.curriculumSubject.subject',
        ]);

        return Inertia::render('Blocks/Show', [
            'block' => $block,
            'activeTerm' => AcademicTerm::where('is_active', true)->first(),
            'instructors' => Instructor::with('user')->active()->get(),
            'curriculumSubjects' => CurriculumSubject::with('subject')
                ->where('year_level', $block->admission_year)
                ->get(),
        ]);
    }

    public function edit(Block $block)
    {
        $user = Auth::user();

        if (
            $user->role === 'program_head' &&
            $block->program->program_head_id !== $user->id
        ) {
            abort(403);
        }

        return Inertia::render('Blocks/Edit', [
            'block' => $block,
            'programs' => Program::active()->get(['id', 'name', 'code']),
        ]);
    }

    public function update(Request $request, Block $block)
    {
        $user = Auth::user();

        if (
            $user->role === 'program_head' &&
            $block->program->program_head_id !== $user->id
        ) {
            abort(403);
        }

        $validated = $request->validate([
            'admission_year' => 'required|integer',
            'status' => 'required|in:active,inactive,graduated',
            'max_students' => 'required|integer|min:1|max:50',
        ]);

        if ((int) $validated['admission_year'] !== (int) $block->admission_year) {
            $validated['code'] = $this->generateBlockCode(
                $block->program,
                (int) $validated['admission_year'],
                $block->id
            );
        }

        $block->update($validated);

        return redirect()->route('blocks.index')->with('success', 'Block updated.');
    }

    public function destroy(Block $block)
    {
        $user = Auth::user();

        if (
            $user->role === 'program_head' &&
            $block->program->program_head_id !== $user->id
        ) {
            abort(403);
        }

        if ($block->status === 'inactive') {
            return back()->with('info', 'Block is already archived.');
        }

        if ($block->students()->exists()) {
            return back()->withErrors([
                'archive' => 'Cannot archive block with students assigned.',
            ]);
        }

        if ($block->scheduledSubjects()->whereHas('enrolledSubjects')->exists()) {
            return back()->withErrors([
                'archive' => 'Cannot archive block because enrollments already exist on its scheduled subjects.',
            ]);
        }

        if ($block->scheduledSubjects()->exists()) {
            return back()->withErrors([
                'archive' => 'Cannot archive block because subject schedules already exist for this block.',
            ]);
        }

        $block->archive();

        return back()->with('success', 'Block archived successfully.');
    }

    /**
     * Get available subjects for a block
     * Returns curriculum subjects from the program's active curriculum
     */
    public function getAvailableSubjects(Block $block)
    {
        // Load the program and its active curriculum
        $block->load('program.curriculums');

        // Get the active curriculum for this program
        $curriculum = $block->program->curriculums()
            ->where('is_active', true)
            ->first();

        if (!$curriculum) {
            return response()->json(['subjects' => []]);
        }

        // Get all curriculum subjects with their related subject info (only active subjects)
        $curriculumSubjects = CurriculumSubject::where('curriculum_id', $curriculum->id)
            ->with(['subject' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get()
            ->filter(function ($cs) {
                return $cs->subject !== null; // Remove any where subject is archived
            })
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
            })
            ->values();

        return response()->json(['subjects' => $curriculumSubjects]);
    }
}
