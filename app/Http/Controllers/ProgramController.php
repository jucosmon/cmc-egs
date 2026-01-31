<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProgramController extends Controller
{
    /**
     * Get dean's department or throw 403 if not a dean.
     */
    private function getDeanDepartmentOrFail()
    {
        $user = Auth::user();
        if ($user->role !== 'dean') {
            abort(403, 'Unauthorized action.');
        }
        $department = \App\Models\Department::where('dean_id', $user->id)->first();
        if (!$department) {
            abort(403, 'No department assigned.');
        }
        return $department;
    }

    public function index()
    {
        $user = Auth::user();
        $query = Program::with(['department', 'programHead'])
            ->withCount(['students', 'blocks', 'curriculums'])
            ->latest();

        // Deans can only see programs in their assigned department
        if ($user->role === 'dean') {
            $department = $this->getDeanDepartmentOrFail();
            $query->where('department_id', $department->id);
        }

        $programs = $query
            ->paginate(10);

        return Inertia::render('Programs/Index', [
            'programs' => $programs,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $departments = Department::active()
            ->select('id', 'name', 'code')
            ->get();

        // Deans can only create programs for their own department
        if ($user->role === 'dean') {
            $department = $this->getDeanDepartmentOrFail();
            $departments = $departments->where('id', $department->id);
        }

        $programHeads = \App\Models\User::where('role', 'program_head')
            ->active()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'official_id' => $user->official_id ?? null,
                ];
            });

        return Inertia::render('Programs/Create', [
            'departments' => $departments,
            'programHeads' => $programHeads,
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
            'program_head_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        Program::create($validated);

        return redirect()->route('programs.index')
            ->with('success', 'Program created successfully.');
    }

    public function show(Program $program)
    {
        $user = Auth::user();
        // Deans can only view programs in their assigned department
        if ($user->role === 'dean') {
            $department = $this->getDeanDepartmentOrFail();
            if ($program->department_id !== $department->id) {
                abort(403, 'Unauthorized to view this program.');
            }
        }

        $program->load([
            'department',
            'programHead',
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
        $user = Auth::user();
        // Deans can only edit programs in their assigned department
        if ($user->role === 'dean') {
            $department = $this->getDeanDepartmentOrFail();
            if ($program->department_id !== $department->id) {
                abort(403, 'Unauthorized to edit this program.');
            }
        }

        $departments = Department::active()
            ->select('id', 'name', 'code')
            ->get();

        // Deans can only edit within their own department
        if ($user->role === 'dean') {
            $department = $this->getDeanDepartmentOrFail();
            $departments = $departments->where('id', $department->id);
        }

        $programHeads = \App\Models\User::where('role', 'program_head')
            ->active()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'official_id' => $user->official_id ?? null,
                ];
            });

        return Inertia::render('Programs/Edit', [
            'program' => $program,
            'departments' => $departments,
            'programHeads' => $programHeads,
        ]);
    }

    public function update(Request $request, Program $program)
    {
        $user = Auth::user();
        // Deans can only update programs in their assigned department
        if ($user->role === 'dean') {
            $department = $this->getDeanDepartmentOrFail();
            if ($program->department_id !== $department->id) {
                abort(403, 'Unauthorized to update this program.');
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:programs,code,' . $program->id,
            'degree_type' => 'required|in:bachelors,masters,doctors,associate',
            'total_units' => 'required|integer|min:1',
            'duration_years' => 'required|integer|min:1|max:10',
            'description' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'program_head_id' => 'nullable|exists:users,id',
            'is_active' => 'boolean',
        ]);

        $program->update($validated);

        return redirect()->route('programs.index')
            ->with('success', 'Program updated successfully.');
    }

    public function destroy(Program $program)
    {
        $user = Auth::user();
        // Deans can only delete programs in their assigned department
        if ($user->role === 'dean') {
            $department = $this->getDeanDepartmentOrFail();
            if ($program->department_id !== $department->id) {
                abort(403, 'Unauthorized to delete this program.');
            }
        }

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
