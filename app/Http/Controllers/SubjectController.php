<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();

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
            'filters' => $request->only('search'),
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
            'title' => 'required|string|max:150',
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
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'units' => 'required|integer|min:1|max:10',
        ]);

        $subject->update($validated);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        // Check if subject is being used in any curriculum
        if ($subject->curriculumSubjects()->count() > 0) {
            return back()->with('error', 'Cannot delete subject that is part of a curriculum.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }
}
