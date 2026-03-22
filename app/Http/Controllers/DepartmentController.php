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
    public function index(Request $request)
    {
        $showArchived = $request->boolean('show_archived');

        $departments = Department::with(['dean', 'programs'])
            ->withCount(['instructors', 'programs'])
            ->when(!$showArchived, function ($query) {
                $query->active();
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
            'filters' => [
                'show_archived' => $showArchived,
            ],
        ]);
    }

    public function create()
    {
        // Get all deans NOT already assigned to other departments
        $availableDeans = User::where('role', 'dean')
            ->where('is_active', true)
            ->whereNotIn('id', Department::whereNotNull('dean_id')->pluck('dean_id'))
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
            'availableDeans' => $availableDeans,
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
        // Get all deans NOT already assigned to other departments
        // But allow the current department's dean to remain selectable
        $availableDeans = User::where('role', 'dean')
            ->where('is_active', true)
            ->whereNotIn('id',
                Department::whereNotNull('dean_id')
                    ->where('id', '!=', $department->id)
                    ->pluck('dean_id')
            )
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
            'availableDeans' => $availableDeans,
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
        if (!$department->is_active) {
            return back()->with('info', 'Department is already archived.');
        }

        if ($department->programs()->exists()) {
            return back()->withErrors([
                'archive' => 'Cannot archive department with existing programs.',
            ]);
        }

        if ($department->dean_id !== null || $department->instructors()->exists()) {
            return back()->withErrors([
                'archive' => 'Cannot archive department while users are still assigned (dean/instructors).',
            ]);
        }

        $department->archive();

        return redirect()->route('departments.index')
            ->with('success', 'Department archived successfully.');
    }

}
