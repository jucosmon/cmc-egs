<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class InstructorController extends Controller
{
    public function index(Request $request)
    {
        $query = Instructor::with(['user', 'department']);

        // Filter by department
        if ($request->has('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('official_id', 'like', "%{$search}%");
            });
        }

        $instructors = $query->withCount('scheduledSubjects')
            ->latest()
            ->paginate(15);

        $departments = Department::active()
            ->select('id', 'name', 'code')
            ->get();

        return Inertia::render('Instructors/Index', [
            'instructors' => $instructors,
            'departments' => $departments,
            'filters' => $request->only(['department_id', 'is_active', 'search']),
        ]);
    }

    public function create()
    {
        $departments = Department::active()
            ->select('id', 'name', 'code')
            ->get();

        return Inertia::render('Instructors/Create', [
            'departments' => $departments,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // User fields
            'email' => 'required|email|unique:users,email',
            'official_id' => 'required|string|max:20|unique:users,official_id',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:male,female,others',

            // Instructor fields
            'specialization' => 'nullable|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        // Create user first
        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make('password'), // Default password
            'role' => 'instructor',
            'official_id' => $validated['official_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Create instructor
        Instructor::create([
            'user_id' => $user->id,
            'department_id' => $validated['department_id'],
            'specialization' => $validated['specialization'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('instructors.index')
            ->with('success', 'Instructor created successfully. Default password is "password".');
    }

    public function show(Instructor $instructor)
    {
        $instructor->load([
            'user',
            'department',
            'scheduledSubjects' => function ($query) {
                $query->with([
                    'academicTerm',
                    'block',
                    'curriculumSubject.subject'
                ])->latest();
            },
            'programsAsHead'
        ]);

        return Inertia::render('Instructors/Show', [
            'instructor' => $instructor,
        ]);
    }

    public function edit(Instructor $instructor)
    {
        $instructor->load('user');

        $departments = Department::active()
            ->select('id', 'name', 'code')
            ->get();

        return Inertia::render('Instructors/Edit', [
            'instructor' => $instructor,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, Instructor $instructor)
    {
        $validated = $request->validate([
            // User fields
            'email' => 'required|email|unique:users,email,' . $instructor->user_id,
            'official_id' => 'required|string|max:20|unique:users,official_id,' . $instructor->user_id,
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:male,female,others',

            // Instructor fields
            'specialization' => 'nullable|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'is_active' => 'boolean',
        ]);

        // Update user
        $instructor->user->update([
            'email' => $validated['email'],
            'official_id' => $validated['official_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Update instructor
        $instructor->update([
            'department_id' => $validated['department_id'],
            'specialization' => $validated['specialization'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('instructors.index')
            ->with('success', 'Instructor updated successfully.');
    }

    public function destroy(Instructor $instructor)
    {
        // Check if instructor has scheduled subjects
        if ($instructor->scheduledSubjects()->count() > 0) {
            return back()->with('error', 'Cannot delete instructor with scheduled subjects.');
        }

        // Check if instructor is a program head
        if ($instructor->programsAsHead()->count() > 0) {
            return back()->with('error', 'Cannot delete instructor who is a program head.');
        }

        // Delete user and instructor (cascade will handle instructor)
        $instructor->user->delete();

        return redirect()->route('instructors.index')
            ->with('success', 'Instructor deleted successfully.');
    }
}
