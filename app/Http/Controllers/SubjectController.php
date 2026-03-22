<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

        // Filter by active status - only show inactive if show_archived=1
        $showArchived = $request->input('show_archived') === '1' || $request->input('show_archived') === 1;
        if (!$showArchived) {
            $query->where('is_active', true);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $subjects = $query->withCount('curriculumSubjects')
            ->latest()
            ->paginate(15);

        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects,
            'filters' => $request->only('search', 'show_archived'),
        ]);
    }

    public function create()
    {
        return Inertia::render('Subjects/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:subjects,code',
            'title' => [
                'required',
                'string',
                'max:150',
                Rule::unique('subjects', 'title'),
            ],
            'description' => 'nullable|string',
            'units' => 'required|integer|min:1|max:10',
        ]);

        Subject::create($validated);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function show(Subject $subject)
    {
        $subject->load([
            'curriculumSubjects' => function ($query) {
                $query->with(['curriculum.program', 'prerequisites.subject']);
            }
        ]);

        return Inertia::render('Subjects/Show', [
            'subject' => $subject,
        ]);
    }

    public function edit(Subject $subject)
    {
        return Inertia::render('Subjects/Edit', [
            'subject' => $subject,
        ]);
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:subjects,code,' . $subject->id,
            'title' => [
                'required',
                'string',
                'max:150',
                Rule::unique('subjects', 'title')->ignore($subject->id),
            ],
            'description' => 'nullable|string',
            'units' => 'required|integer|min:1|max:10',
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        // If subject is already archived (is_active = false), unarchive it
        if (!$subject->is_active) {
            $subject->update(['is_active' => true, 'archived_at' => null]);
            return redirect()->route('subjects.index')
                ->with('success', 'Subject restored successfully.');
        }

        // Check if subject is being used in any curriculum
        if ($subject->curriculumSubjects()->count() > 0) {
            return back()->withErrors([
                'archive' => 'Cannot archive subject that is part of a curriculum.',
            ]);
        }

        $subject->archive();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject archived successfully.');
    }
}
