<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\Program;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::with(['department', 'programHead.user'])
            ->withCount(['students', 'blocks', 'curriculums'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Programs/Index', [
            'programs' => $programs,
        ]);
    }

    public function create()
    {
        $departments = Department::active()
            ->select('id', 'name', 'code')
            ->get();

        $instructors = Instructor::with('user')
            ->active()
            ->get()
            ->map(function ($instructor) {
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->user->first_name . ' ' . $instructor->user->last_name,
                    'specialization' => $instructor->specialization,
                ];
            });

        return Inertia::render('Programs/Create', [
            'departments' => $departments,
            'instructors' => $instructors,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:programs,code',
            'degree_type' => 'required|in:bachelors,masters,doctors,associate',
            'total_units' => 'required|integer|min:1',
            'duration_years' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'program_head_id' => 'nullable|exists:instructors,id',
            'is_active' => 'boolean',
        ]);

        Program::create($validated);

        return redirect()->route('programs.index')
            ->with('success', 'Program created successfully.');
    }

    public function show(Program $program)
    {
        $program->load([
            'department',
            'programHead.user',
            'curriculums' => function ($query) {
                $query->with('curriculumSubjects.subject');
            },
            'blocks' => function ($query) {
                $query->withCount('students');
            },
        ]);

        return Inertia::render('Programs/Show', [
            'program' => $program,
        ]);
    }

    public function edit(Program $program)
    {
        $departments = Department::active()
            ->select('id', 'name', 'code')
            ->get();

        $instructors = Instructor::with('user')
            ->active()
            ->get()
            ->map(function ($instructor) {
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->user->first_name . ' ' . $instructor->user->last_name,
                    'specialization' => $instructor->specialization,
                ];
            });

        return Inertia::render('Programs/Edit', [
            'program' => $program,
            'departments' => $departments,
            'instructors' => $instructors,
        ]);
    }

    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:programs,code,' . $program->id,
            'degree_type' => 'required|in:bachelors,masters,doctors,associate',
            'total_units' => 'required|integer|min:1',
            'duration_years' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'program_head_id' => 'nullable|exists:instructors,id',
            'is_active' => 'boolean',
        ]);

        $program->update($validated);

        return redirect()->route('programs.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        // Check if program has students or curriculums
        if ($program->students()->count() > 0) {
            return back()->with('error', 'Cannot delete program with enrolled students.');
        }

        if ($program->curriculums()->count() > 0) {
            return back()->with('error', 'Cannot delete program with existing curriculums.');
        }

        $program->delete();

        return redirect()->route('programs.index')
            ->with('success', 'Program deleted successfully.');
    }
}
