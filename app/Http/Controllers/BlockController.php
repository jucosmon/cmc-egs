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
        $query = Block::query()->with('program');

        /** PROGRAM HEAD FILTER */
        if ($user->role === 'program_head') {
            $programIds = Program::where('program_head_id', $user->id)->pluck('id');

            if ($programIds->isEmpty()) {
                abort(403, 'No program assigned to this Program Head.');
            }

            $query->whereIn('program_id', $programIds);
        }

        /** OPTIONAL FILTERS */
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        return Inertia::render('Blocks/Index', [
            'blocks' => $blocks,
            'programs' => Program::active()->get(['id', 'name', 'code']),
            'activeTerm' => AcademicTerm::where('is_active', true)->first(),
            'instructors' => Instructor::with('user')->active()->get(),
            'filters' => $request->only(['program_id', 'status', 'admission_year']),
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
            'code' => 'required|string|max:20|unique:blocks,code',
            'program_id' => 'required|exists:programs,id',
            'admission_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'status' => 'required|in:active,inactive,graduated',
        ]);

        if (
            $user->role === 'program_head' &&
            !Program::where('program_head_id', $user->id)->where('id', $validated['program_id'])->exists()
        ) {
            abort(403, 'Unauthorized program.');
        }

        Block::create($validated);

        return redirect()->route('blocks.index')->with('success', 'Block created successfully.');
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
            'code' => 'required|string|max:20|unique:blocks,code,' . $block->id,
            'program_id' => 'required|exists:programs,id',
            'admission_year' => 'required|integer',
            'status' => 'required|in:active,inactive,graduated',
        ]);

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

        if ($block->students()->exists()) {
            return back()->with('error', 'Cannot delete block with students.');
        }

        if ($block->scheduledSubjects()->exists()) {
            return back()->with('error', 'Cannot delete block with schedules.');
        }

        $block->delete();

        return back()->with('success', 'Block deleted.');
    }
}
