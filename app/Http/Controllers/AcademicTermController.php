<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AcademicTermController extends Controller
{
    public function index(Request $request)
    {
        $query = AcademicTerm::query();

        // Filter by semester
        if ($request->has('semester')) {
            $query->where('semester', $request->semester);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $terms = $query->withCount(['enrollments', 'scheduledSubjects'])
            ->latest('academic_year')
            ->latest('semester')
            ->paginate(10);

        return Inertia::render('AcademicTerms/Index', [
            'terms' => $terms,
            'filters' => $request->only(['semester', 'is_active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('AcademicTerms/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year' => 'required|string|max:9|regex:/^\d{4}-\d{4}$/',
            'semester' => 'required|in:first,second,summer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'start_enrollment' => 'required|date',
            'end_enrollment' => 'required|date|after:start_enrollment',
            'start_mid_grade' => 'required|date',
            'end_mid_grade' => 'required|date|after:start_mid_grade',
            'start_final_grade' => 'required|date',
            'end_final_grade' => 'required|date|after:start_final_grade',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // If setting as active, deactivate all other terms
            if ($validated['is_active'] ?? false) {
                AcademicTerm::where('is_active', true)->update(['is_active' => false]);
            }

            AcademicTerm::create($validated);

            DB::commit();

            return redirect()->route('terms.index')
                ->with('success', 'Academic term created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create academic term: ' . $e->getMessage());
        }
    }

    public function show(AcademicTerm $term)
    {
        $term->load([
            'enrollments' => function ($query) {
                $query->with(['student.user', 'block'])
                    ->latest()
                    ->take(50);
            },
            'scheduledSubjects' => function ($query) {
                $query->with([
                    'block',
                    'instructor.user',
                    'curriculumSubject.subject'
                ]);
            }
        ]);

        // Statistics
        $stats = [
            'total_enrollments' => $term->enrollments()->count(),
            'total_scheduled_subjects' => $term->scheduledSubjects()->count(),
            'enrollment_status' => $term->isEnrollmentOpen() ? 'Open' : 'Closed',
            'midterm_grade_status' => $term->isMidGradeOpen() ? 'Open' : 'Closed',
            'final_grade_status' => $term->isFinalGradeOpen() ? 'Open' : 'Closed',
        ];

        return Inertia::render('AcademicTerms/Show', [
            'term' => $term,
            'stats' => $stats,
        ]);
    }

    public function edit(AcademicTerm $term)
    {
        return Inertia::render('AcademicTerms/Edit', [
            'term' => $term,
        ]);
    }

    public function update(Request $request, AcademicTerm $term)
    {
        $validated = $request->validate([
            'academic_year' => 'required|string|max:9|regex:/^\d{4}-\d{4}$/',
            'semester' => 'required|in:first,second,summer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'start_enrollment' => 'required|date',
            'end_enrollment' => 'required|date|after:start_enrollment',
            'start_mid_grade' => 'required|date',
            'end_mid_grade' => 'required|date|after:start_mid_grade',
            'start_final_grade' => 'required|date',
            'end_final_grade' => 'required|date|after:start_final_grade',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // If setting as active, deactivate all other terms
            if ($validated['is_active'] ?? false) {
                AcademicTerm::where('is_active', true)
                    ->where('id', '!=', $term->id)
                    ->update(['is_active' => false]);
            }

            $term->update($validated);

            DB::commit();

            return redirect()->route('terms.index')
                ->with('success', 'Academic term updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update academic term: ' . $e->getMessage());
        }
    }

    public function destroy(AcademicTerm $term)
    {
        // Check if term has enrollments
        if ($term->enrollments()->count() > 0) {
            return back()->with('error', 'Cannot delete academic term with existing enrollments.');
        }

        // Check if term has scheduled subjects
        if ($term->scheduledSubjects()->count() > 0) {
            return back()->with('error', 'Cannot delete academic term with scheduled subjects.');
        }

        $term->delete();

        return redirect()->route('terms.index')
            ->with('success', 'Academic term deleted successfully.');
    }

    // Additional method to activate a term
    public function activate(AcademicTerm $term)
    {
        DB::beginTransaction();
        try {
            // Deactivate all other terms
            AcademicTerm::where('is_active', true)->update(['is_active' => false]);

            // Activate this term
            $term->update(['is_active' => true]);

            DB::commit();

            return back()->with('success', 'Academic term activated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to activate term: ' . $e->getMessage());
        }
    }
}
