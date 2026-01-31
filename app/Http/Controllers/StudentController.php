<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'program', 'block']);

        // Filter by program
        if ($request->has('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by block
        if ($request->has('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        // Filter by year level
        if ($request->has('year_level')) {
            $query->where('year_level', $request->year_level);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name or student number
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('official_id', 'like', "%{$search}%");
            });
        }

        $students = $query->withCount('enrollments')
            ->latest()
            ->paginate(15);

        $programs = Program::active()->select('id', 'name', 'code')->get();
        $blocks = Block::active()->select('id', 'code', 'program_id')->get();

        return Inertia::render('Students/Index', [
            'students' => $students,
            'programs' => $programs,
            'blocks' => $blocks,
            'filters' => $request->only(['program_id', 'block_id', 'year_level', 'status', 'search']),
        ]);
    }

    public function create()
    {
        $programs = Program::active()->select('id', 'name', 'code')->get();
        $blocks = Block::active()->with('program')->get();

        return Inertia::render('Students/Create', [
            'programs' => $programs,
            'blocks' => $blocks,
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

            // Student fields
            'year_level' => 'required|integer|min:1|max:5',
            'status' => 'required|in:regular,irregular,graduated',
            'program_id' => 'required|exists:programs,id',
            'block_id' => 'nullable|exists:blocks,id',
        ]);

        // Create user first
        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make('password'), // Default password
            'role' => 'student',
            'official_id' => $validated['official_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'sex' => $validated['sex'] ?? null,
            'is_active' => true,
        ]);

        // Create student
        Student::create([
            'user_id' => $user->id,
            'program_id' => $validated['program_id'],
            'block_id' => $validated['block_id'] ?? null,
            'year_level' => $validated['year_level'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('students.index')
            ->with('success', 'Student created successfully. Default password is "password".');
    }

    public function show(Student $student)
    {
        $student->load([
            'user',
            'program.department',
            'block',
            'enrollments' => function ($query) {
                $query->with([
                    'academicTerm',
                    'block',
                    'enrolledSubjects.scheduledSubject.curriculumSubject.subject'
                ])->latest();
            }
        ]);

        return Inertia::render('Students/Show', [
            'student' => $student,
        ]);
    }

    public function edit(Student $student)
    {
        $student->load('user');

        $programs = Program::active()->select('id', 'name', 'code')->get();
        $blocks = Block::active()->with('program')->get();

        return Inertia::render('Students/Edit', [
            'student' => $student,
            'programs' => $programs,
            'blocks' => $blocks,
        ]);
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            // User fields
            'email' => 'required|email|unique:users,email,' . $student->user_id,
            'official_id' => 'required|string|max:20|unique:users,official_id,' . $student->user_id,
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'sex' => 'nullable|in:male,female,others',

            // Student fields
            'year_level' => 'required|integer|min:1|max:5',
            'status' => 'required|in:regular,irregular,graduated',
            'program_id' => 'required|exists:programs,id',
            'block_id' => 'nullable|exists:blocks,id',
        ]);

        // Update user
        $student->user->update([
            'email' => $validated['email'],
            'official_id' => $validated['official_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'sex' => $validated['sex'] ?? null,
        ]);

        // Update student
        $student->update([
            'program_id' => $validated['program_id'],
            'block_id' => $validated['block_id'] ?? null,
            'year_level' => $validated['year_level'],
            'status' => $validated['status'],
        ]);

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student)
    {
        // Check if student has enrollments
        if ($student->enrollments()->count() > 0) {
            return back()->with('error', 'Cannot delete student with enrollment records.');
        }

        // Delete user and student (cascade will handle student)
        $student->user->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }
}
