<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlockController extends Controller
{
    public function index(Request $request)
    {
        $query = Block::with(['program']);

        // Filter by program
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by admission year
        if ($request->has('admission_year')) {
            $query->where('admission_year', $request->admission_year);
        }

        $blocks = $query->withCount('students')
            ->latest()
            ->paginate(15);

        $programs = Program::active()
            ->select('id', 'name', 'code')
            ->get();

        return Inertia::render('Blocks/Index', [
            'blocks' => $blocks,
            'programs' => $programs,
            'filters' => $request->only(['program_id', 'status', 'admission_year']),
        ]);
    }

    public function create()
    {
        $programs = Program::active()
            ->select('id', 'name', 'code')
            ->get();

        return Inertia::render('Blocks/Create', [
            'programs' => $programs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:blocks,code',
            'admission_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'status' => 'required|in:active,inactive,graduated',
            'program_id' => 'required|exists:programs,id',
        ]);

        Block::create($validated);

        return redirect()->route('blocks.index')
            ->with('success', 'Block created successfully.');
    }

    public function show(Block $block)
    {
        $block->load([
            'program.department',
            'students.user',
            'scheduledSubjects' => function ($query) {
                $query->with([
                    'academicTerm',
                    'instructor.user',
                    'curriculumSubject.subject'
                ]);
            }
        ]);

        return Inertia::render('Blocks/Show', [
            'block' => $block,
        ]);
    }

    public function edit(Block $block)
    {
        $programs = Program::active()
            ->select('id', 'name', 'code')
            ->get();

        return Inertia::render('Blocks/Edit', [
            'block' => $block,
            'programs' => $programs,
        ]);
    }

    public function update(Request $request, Block $block)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:blocks,code,' . $block->id,
            'admission_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'status' => 'required|in:active,inactive,graduated',
            'program_id' => 'required|exists:programs,id',
        ]);

        $block->update($validated);

        return redirect()->route('blocks.index')
            ->with('success', 'Block updated successfully.');
    }

    public function destroy(Block $block)
    {
        // Check if block has students
        if ($block->students()->count() > 0) {
            return back()->with('error', 'Cannot delete block with enrolled students.');
        }

        // Check if block has scheduled subjects
        if ($block->scheduledSubjects()->count() > 0) {
            return back()->with('error', 'Cannot delete block with scheduled subjects.');
        }

        $block->delete();

        return redirect()->route('blocks.index')
            ->with('success', 'Block deleted successfully.');
    }
}
