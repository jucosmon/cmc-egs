<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use App\Models\EnrolledSubject;
use App\Models\Program;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CurriculumController extends Controller
{
    public function index(Request $request)
    {
        $query = Curriculum::with(['program']);
        $user = Auth::user();

        // If user is a program head, only show curriculums for their program
        if ($user->role === 'program_head') {
            $programHeadProgram = Program::where('program_head_id', $user->id)->first();
            if ($programHeadProgram) {
                $query->where('program_id', $programHeadProgram->id);
            } else {
                // If program head has no program assigned, return empty
                $curriculums = Curriculum::where('id', -1)->paginate(15);
                return Inertia::render('Curriculums/Index', [
                    'curriculums' => $curriculums,
                    'programs' => [],
                    'filters' => [],
                ]);
            }
        } else {
            // For other roles (dean, it_admin), allow filtering by program
            if ($request->has('program_id')) {
                $query->where('program_id', $request->program_id);
            }
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $curriculums = $query->withCount('curriculumSubjects')
            ->latest()
            ->paginate(15);

        // Only show relevant programs for filtering
        if ($user->role === 'program_head') {
            $programs = Program::where('program_head_id', $user->id)->select('id', 'name', 'code')->get();
        } else {
            $programs = Program::active()->select('id', 'name', 'code')->get();
        }

        return Inertia::render('Curriculums/Index', [
            'curriculums' => $curriculums,
            'programs' => $programs,
            'filters' => $request->only(['program_id', 'is_active']),
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->role === 'program_head') {
            $programs = Program::where('program_head_id', $user->id)
                ->select('id', 'name', 'code')
                ->get();
        } else {
            $programs = Program::active()->select('id', 'name', 'code')->get();
        }
        $subjects = Subject::select('id', 'code', 'title', 'units')->orderBy('code')->get();

        return Inertia::render('Curriculums/Create', [
            'programs' => $programs,
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'year_effective' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'program_id' => 'required|exists:programs,id',
            'is_active' => 'boolean',

            // Curriculum subjects array
            'subjects' => 'nullable|array',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.year_level' => 'required|integer|min:1|max:5',
            'subjects.*.semester' => 'required|in:first,second,summer',
            'subjects.*.course_type' => 'required|in:major,elective,minor',
            'subjects.*.has_laboratory' => 'boolean',
            'subjects.*.prerequisites' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // If setting as active, deactivate other curriculums for this program
            if ($validated['is_active'] ?? false) {
                Curriculum::where('program_id', $validated['program_id'])
                    ->update(['is_active' => false]);
            }

            // Create curriculum
            $curriculum = Curriculum::create([
                'name' => $validated['name'],
                'year_effective' => $validated['year_effective'],
                'program_id' => $validated['program_id'],
                'is_active' => $validated['is_active'] ?? false,
            ]);

            // Add curriculum subjects if provided
            if (!empty($validated['subjects'])) {
                foreach ($validated['subjects'] as $subjectData) {
                    $curriculumSubject = CurriculumSubject::create([
                        'curriculum_id' => $curriculum->id,
                        'subject_id' => $subjectData['subject_id'],
                        'year_level' => $subjectData['year_level'],
                        'semester' => $subjectData['semester'],
                        'course_type' => $subjectData['course_type'],
                        'has_laboratory' => $subjectData['has_laboratory'] ?? false,
                    ]);

                    // Attach prerequisites if provided
                    if (!empty($subjectData['prerequisites'])) {
                        $curriculumSubject->prerequisites()->attach($subjectData['prerequisites']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('curriculums.index')
                ->with('success', 'Curriculum created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create curriculum: ' . $e->getMessage());
        }
    }

    public function show(Curriculum $curriculum)
    {
        $curriculum->load([
            'program.department',
            'curriculumSubjects' => function ($query) {
                $query->with(['subject', 'prerequisites.subject'])
                    ->orderBy('year_level')
                    ->orderBy('semester');
            }
        ]);

        // Group subjects by year and semester
        $groupedSubjects = $curriculum->curriculumSubjects->groupBy(function ($item) {
            return $item->year_level . '-' . $item->semester;
        });

        return Inertia::render('Curriculums/Show', [
            'curriculum' => $curriculum,
            'groupedSubjects' => $groupedSubjects,
        ]);
    }

    public function edit(Curriculum $curriculum)
    {
        $curriculum->load([
            'curriculumSubjects' => function ($query) {
                $query->with(['subject', 'prerequisites']);
            }
        ]);
        $user = Auth::user();
        if ($user->role === 'program_head') {
            $programs = Program::where('program_head_id', $user->id)
                ->select('id', 'name', 'code')
                ->get();
        } else {
            $programs = Program::active()->select('id', 'name', 'code')->get();
        }
        $subjects = Subject::select('id', 'code', 'title', 'units')->orderBy('code')->get();

        return Inertia::render('Curriculums/Edit', [
            'curriculum' => $curriculum,
            'programs' => $programs,
            'subjects' => $subjects,
        ]);
    }

    public function update(Request $request, Curriculum $curriculum)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'year_effective' => 'required|integer|min:2000|max:' . (date('Y') + 5),
            'program_id' => 'required|exists:programs,id',
            'is_active' => 'boolean',

            // Curriculum subjects array
            'subjects' => 'nullable|array',
            'subjects.*.id' => 'nullable|exists:curriculum_subjects,id',
            'subjects.*.subject_id' => 'required|exists:subjects,id',
            'subjects.*.year_level' => 'required|integer|min:1|max:5',
            'subjects.*.semester' => 'required|in:first,second,summer',
            'subjects.*.course_type' => 'required|in:major,elective,minor',
            'subjects.*.has_laboratory' => 'boolean',
            'subjects.*.prerequisites' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            // If setting as active, deactivate other curriculums for this program
            if ($validated['is_active'] ?? false) {
                Curriculum::where('program_id', $validated['program_id'])
                    ->where('id', '!=', $curriculum->id)
                    ->update(['is_active' => false]);
            }

            // Update curriculum
            $curriculum->update([
                'name' => $validated['name'],
                'year_effective' => $validated['year_effective'],
                'program_id' => $validated['program_id'],
                'is_active' => $validated['is_active'] ?? false,
            ]);

            // Handle curriculum subjects updates
            if (isset($validated['subjects'])) {
                $existingIds = [];

                foreach ($validated['subjects'] as $subjectData) {
                    if (!empty($subjectData['id'])) {
                        // Update existing
                        $curriculumSubject = CurriculumSubject::find($subjectData['id']);
                        $curriculumSubject->update([
                            'subject_id' => $subjectData['subject_id'],
                            'year_level' => $subjectData['year_level'],
                            'semester' => $subjectData['semester'],
                            'course_type' => $subjectData['course_type'],
                            'has_laboratory' => $subjectData['has_laboratory'] ?? false,
                        ]);
                        $existingIds[] = $subjectData['id'];
                    } else {
                        // Create new
                        $curriculumSubject = CurriculumSubject::create([
                            'curriculum_id' => $curriculum->id,
                            'subject_id' => $subjectData['subject_id'],
                            'year_level' => $subjectData['year_level'],
                            'semester' => $subjectData['semester'],
                            'course_type' => $subjectData['course_type'],
                            'has_laboratory' => $subjectData['has_laboratory'] ?? false,
                        ]);
                        $existingIds[] = $curriculumSubject->id;
                    }

                    // Sync prerequisites
                    $curriculumSubject->prerequisites()->sync($subjectData['prerequisites'] ?? []);
                }

                // Delete removed subjects
                $curriculum->curriculumSubjects()
                    ->whereNotIn('id', $existingIds)
                    ->delete();
            }

            DB::commit();

            return redirect()->route('curriculums.index')
                ->with('success', 'Curriculum updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update curriculum: ' . $e->getMessage());
        }
    }

    public function destroy(Curriculum $curriculum)
    {
        // Check if curriculum is being used
        if ($curriculum->curriculumSubjects()->whereHas('scheduledSubjects')->count() > 0) {
            return back()->with('error', 'Cannot delete curriculum with scheduled subjects.');
        }

        $curriculum->delete();

        return redirect()->route('curriculums.index')
            ->with('success', 'Curriculum deleted successfully.');
    }


    public function activate(Curriculum $curriculum)
    {
        DB::beginTransaction();
        try {
            // Deactivate all other curriculums for this program
            Curriculum::where('program_id', $curriculum->program_id)
                ->where('id', '!=', $curriculum->id)
                ->update(['is_active' => false]);

            // Activate the selected curriculum
            $curriculum->update(['is_active' => true]);

            DB::commit();

            return redirect()->route('curriculums.index')
                ->with('success', 'You have activated a new curriculum');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to activate curriculum: ' . $e->getMessage());
        }
    }

    public function studentChecklist()
    {
        $user = Auth::user();

        if ($user->role !== 'student') {
            abort(403, 'Unauthorized access.');
        }

        $student = $user->student;

        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        $student->load(['user', 'program']);

        $curriculum = Curriculum::where('program_id', $student->program_id)
            ->where('is_active', true)
            ->with([
                'program',
                'curriculumSubjects' => function ($query) {
                    $query->with('subject')
                        ->orderBy('year_level')
                        ->orderBy('semester');
                },
            ])
            ->first();

        $activeTerm = AcademicTerm::active()->first();
        $subjectStatuses = [];

        if ($curriculum) {
            $enrolledSubjects = EnrolledSubject::whereHas('enrollment', function ($query) use ($student) {
                $query->where('student_id', $student->id);
            })
                ->with([
                    'scheduledSubject.curriculumSubject.subject',
                    'enrollment',
                ])
                ->get();

            foreach ($enrolledSubjects as $enrolledSubject) {
                $curriculumSubject = $enrolledSubject->scheduledSubject?->curriculumSubject;

                if (!$curriculumSubject || $curriculumSubject->curriculum_id !== $curriculum->id) {
                    continue;
                }

                $curriculumSubjectId = $curriculumSubject->id;

                if (!isset($subjectStatuses[$curriculumSubjectId])) {
                    $subjectStatuses[$curriculumSubjectId] = [
                        'status' => 'not_taken',
                        'final_grade' => null,
                    ];
                }

                $attemptPassed = $enrolledSubject->final_grade !== null && $enrolledSubject->final_grade >= 75;
                $attemptFailed = $enrolledSubject->final_grade !== null && $enrolledSubject->final_grade < 75;

                if ($attemptPassed || ($enrolledSubject->status === 'completed' && $enrolledSubject->final_grade === null)) {
                    $subjectStatuses[$curriculumSubjectId]['status'] = 'completed';
                    $subjectStatuses[$curriculumSubjectId]['final_grade'] = $enrolledSubject->final_grade;
                    continue;
                }

                $isActiveEnrolled = $activeTerm
                    && $enrolledSubject->status === 'enrolled'
                    && $enrolledSubject->enrollment?->academic_term_id === $activeTerm->id;

                if ($isActiveEnrolled && $subjectStatuses[$curriculumSubjectId]['status'] !== 'completed') {
                    $subjectStatuses[$curriculumSubjectId]['status'] = 'in_progress';
                }

                if ($attemptFailed && $subjectStatuses[$curriculumSubjectId]['status'] === 'not_taken') {
                    $subjectStatuses[$curriculumSubjectId]['status'] = 'failed';
                    $subjectStatuses[$curriculumSubjectId]['final_grade'] = $enrolledSubject->final_grade;
                }
            }
        }

        $groupedSubjects = $curriculum
            ? $curriculum->curriculumSubjects->groupBy(function ($item) {
                return $item->year_level . '-' . $item->semester;
            })
            : collect();

        return Inertia::render('Curriculums/StudentChecklist', [
            'student' => $student,
            'curriculum' => $curriculum,
            'groupedSubjects' => $groupedSubjects,
            'activeTerm' => $activeTerm,
            'subjectStatuses' => $subjectStatuses,
        ]);
    }
}
