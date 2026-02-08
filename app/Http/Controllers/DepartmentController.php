<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments = Department::with(['dean', 'programs'])
            ->withCount(['instructors', 'programs'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function create()
    {
        // Get all users with dean role for the dropdown
        $deans = User::where('role', 'dean')
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'official_id')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'official_id' => $user->official_id,
                ];
            });

        return Inertia::render('Departments/Create', [
            'deans' => $deans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                Rule::unique('departments', 'name'),
            ],
            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('departments', 'code'),
            ],
            'dean_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('departments', 'dean_id'),
            ],
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Department name already exists.',
            'code.unique' => 'Department code already exists.',
            'dean_id.unique' => 'This dean is already assigned to another department.',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load([
            'dean',
            'programs.blocks',
            'instructors.user',
        ]);

        return Inertia::render('Departments/Show', [
            'department' => $department,
        ]);
    }

    public function edit(Department $department)
    {
        $deans = User::where('role', 'dean')
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'official_id')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'official_id' => $user->official_id,
                ];
            });

        return Inertia::render('Departments/Edit', [
            'department' => $department,
            'deans' => $deans,
        ]);
    }

    public function update(Request $request, Department $department)
    {

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
                Rule::unique('departments', 'name')->ignore($department->id),
            ],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('departments', 'code')->ignore($department->id),
            ],
            'dean_id' => [
                'sometimes',
                'nullable',
                'exists:users,id',
                Rule::unique('departments', 'dean_id')->ignore($department->id),
            ],
            'is_active' => 'sometimes|boolean',
        ], [
            'name.unique' => 'Department name already exists.',
            'code.unique' => 'Department code already exists.',
            'dean_id.unique' => 'This dean is already assigned to another department.',
        ]);

        $department->update($validated);

        return redirect()->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        // Check if department has programs
        if ($department->programs()->count() > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'delete' => 'Cannot delete department with existing programs.',
            ]);
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }

}
