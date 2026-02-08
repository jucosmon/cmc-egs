<?php

namespace App\Http\Controllers;

use App\Models\AcademicTerm;
use App\Models\Student;
use App\Models\Program;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
        // Enrollment Statistics Report
    public function enrollmentStatistics(Request $request)
    {
        // Get filters
        $academicTermId = $request->input('academic_term_id');
        $programId = $request->input('program_id');
        $yearLevel = $request->input('year_level');

        // Default to active term if not specified
        if (!$academicTermId) {
            $activeTerm = AcademicTerm::where('is_active', true)->first();
            $academicTermId = $activeTerm?->id;
        }

        // Build base query
        $enrollmentQuery = Enrollment::query();

        if ($academicTermId) {
            $enrollmentQuery->where('academic_term_id', $academicTermId);
        }

        if ($programId) {
            $enrollmentQuery->whereHas('student', function ($q) use ($programId) {
                $q->where('program_id', $programId);
            });
        }

        if ($yearLevel) {
            $enrollmentQuery->where('year_level', $yearLevel);
        }

        // Overall statistics
        $stats = [
            'total_enrollments' => $enrollmentQuery->count(),
            'enrolled' => (clone $enrollmentQuery)->where('status', 'enrolled')->count(),
            'completed' => (clone $enrollmentQuery)->where('status', 'completed')->count(),
        ];

        // Enrollment by program
        $byProgram = Enrollment::select('programs.name', DB::raw('count(*) as total'))
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->when($academicTermId, function ($q) use ($academicTermId) {
                $q->where('enrollments.academic_term_id', $academicTermId);
            })
            ->when($yearLevel, function ($q) use ($yearLevel) {
                $q->where('enrollments.year_level', $yearLevel);
            })
            ->groupBy('programs.id', 'programs.name')
            ->get();

        // Enrollment by year level
        $byYearLevel = Enrollment::select('year_level', DB::raw('count(*) as total'))
            ->when($academicTermId, function ($q) use ($academicTermId) {
                $q->where('academic_term_id', $academicTermId);
            })
            ->when($programId, function ($q) use ($programId) {
                $q->whereHas('student', function ($query) use ($programId) {
                    $query->where('program_id', $programId);
                });
            })
            ->when($yearLevel, function ($q) use ($yearLevel) {
                $q->where('year_level', $yearLevel);
            })
            ->groupBy('year_level')
            ->orderBy('year_level')
            ->get();

        // Enrollment by status
        $byStatus = Enrollment::select('status', DB::raw('count(*) as total'))
            ->when($academicTermId, function ($q) use ($academicTermId) {
                $q->where('academic_term_id', $academicTermId);
            })
            ->when($programId, function ($q) use ($programId) {
                $q->whereHas('student', function ($query) use ($programId) {
                    $query->where('program_id', $programId);
                });
            })
            ->when($yearLevel, function ($q) use ($yearLevel) {
                $q->where('year_level', $yearLevel);
            })
            ->groupBy('status')
            ->get();

        // Enrollment trend (last 5 terms)
        $enrollmentTrend = Enrollment::select(
                'academic_terms.academic_year',
                'academic_terms.semester',
                DB::raw('count(*) as total')
            )
            ->join('academic_terms', 'enrollments.academic_term_id', '=', 'academic_terms.id')
            ->when($programId, function ($q) use ($programId) {
                $q->whereHas('student', function ($query) use ($programId) {
                    $query->where('program_id', $programId);
                });
            })
            ->when($yearLevel, function ($q) use ($yearLevel) {
                $q->where('enrollments.year_level', $yearLevel);
            })
            ->groupBy('academic_terms.id', 'academic_terms.academic_year', 'academic_terms.semester')
            ->orderBy('academic_terms.academic_year', 'desc')
            ->orderBy('academic_terms.semester', 'desc')
            ->take(5)
            ->get();

        // Get filter options
        $academicTerms = AcademicTerm::latest('academic_year')->get();
        $programs = Program::active()->select('id', 'name', 'code')->get();

        return Inertia::render('Reports/EnrollmentStatistics', [
            'stats' => $stats,
            'byProgram' => $byProgram,
            'byYearLevel' => $byYearLevel,
            'byStatus' => $byStatus,
            'enrollmentTrend' => $enrollmentTrend,
            'academicTerms' => $academicTerms,
            'programs' => $programs,
            'filters' => [
                'academic_term_id' => $academicTermId,
                'program_id' => $programId,
                'year_level' => $yearLevel,
            ],
        ]);
    }

    // Generate TOR (Transcript of Records) Page
    public function generateTOR(Request $request)
    {
        // Search for student
        $search = $request->input('search');
        $studentId = $request->input('student_id');
        $students = null;
        $studentRecord = null;
        $torRecords = [];
        $torSummary = null;
        $torMessage = null;

        if ($search) {
            $students = Student::with(['user', 'program', 'block'])
                ->whereHas('user', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('official_id', 'like', "%{$search}%");
                })
                ->limit(20)
                ->get();
        }

        if ($studentId) {
            $studentRecord = Student::with(['user', 'program', 'block'])->find($studentId);

            if ($studentRecord) {
                $enrollments = Enrollment::where('student_id', $studentRecord->id)
                    ->with([
                        'academicTerm',
                        'enrolledSubjects' => function ($q) {
                            $q->with('scheduledSubject.curriculumSubject.subject')
                                ->where('status', 'completed')
                                ->whereNotNull('final_grade');
                        },
                    ])
                    ->orderBy('year_level')
                    ->get();

                $termGroups = $enrollments->groupBy(function ($enrollment) {
                    return $enrollment->academicTerm->academic_year . ' - ' . ucfirst($enrollment->academicTerm->semester);
                });

                $totalUnits = 0;
                $totalGradePoints = 0;
                $totalSubjects = 0;

                foreach ($termGroups as $term => $enrollmentsInTerm) {
                    $subjects = [];
                    foreach ($enrollmentsInTerm as $enrollment) {
                        foreach ($enrollment->enrolledSubjects as $enrolledSubject) {
                            $subject = $enrolledSubject->scheduledSubject->curriculumSubject->subject;
                            $units = $subject->units;
                            $grade = $enrolledSubject->final_grade;

                            $subjects[] = [
                                'code' => $subject->code,
                                'title' => $subject->title,
                                'units' => $units,
                                'final_grade' => $grade,
                                'remarks' => $grade >= 75 ? 'Passed' : 'Failed',
                                'year_level' => $enrollment->year_level,
                            ];

                            $totalUnits += $units;
                            $totalGradePoints += ($grade * $units);
                            $totalSubjects += 1;
                        }
                    }

                    $torRecords[] = [
                        'term' => $term,
                        'year_level' => $enrollmentsInTerm->first()->year_level ?? null,
                        'subjects' => $subjects,
                    ];
                }

                $overallGwa = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

                $torSummary = [
                    'total_units' => $totalUnits,
                    'total_subjects' => $totalSubjects,
                    'overall_gwa' => round($overallGwa, 2),
                ];
            } else {
                $torMessage = 'No student found.';
            }
        }

        return Inertia::render('Reports/GenerateTOR', [
            'students' => $students,
            'search' => $search,
            'studentRecord' => $studentRecord,
            'torRecords' => $torRecords,
            'torSummary' => $torSummary,
            'torMessage' => $torMessage,
        ]);
    }

    // Download TOR for a specific student
    public function downloadTOR(Student $student)
    {
        // Get all enrollments with grades
        $enrollments = Enrollment::where('student_id', $student->id)
            ->with([
                'academicTerm',
                'enrolledSubjects' => function ($q) {
                    $q->with('scheduledSubject.curriculumSubject.subject')
                        ->where('status', 'completed')
                        ->whereNotNull('final_grade');
                }
            ])
            ->orderBy('year_level')
            ->get();

        // Group by academic term
        $termGroups = $enrollments->groupBy(function ($enrollment) {
            return $enrollment->academicTerm->academic_year . ' - ' . ucfirst($enrollment->academicTerm->semester);
        });

        // Calculate GPA per term and overall
        $overallGPA = 0;
        $totalUnits = 0;
        $totalGradePoints = 0;

        $termSummaries = [];
        foreach ($termGroups as $term => $enrollmentsInTerm) {
            $termUnits = 0;
            $termGradePoints = 0;

            foreach ($enrollmentsInTerm as $enrollment) {
                foreach ($enrollment->enrolledSubjects as $enrolledSubject) {
                    $units = $enrolledSubject->scheduledSubject->curriculumSubject->subject->units;
                    $grade = $enrolledSubject->final_grade;

                    $termUnits += $units;
                    $termGradePoints += ($grade * $units);
                }
            }

            $termGPA = $termUnits > 0 ? $termGradePoints / $termUnits : 0;

            $termSummaries[$term] = [
                'units' => $termUnits,
                'gpa' => round($termGPA, 2),
            ];

            $totalUnits += $termUnits;
            $totalGradePoints += $termGradePoints;
        }

        $overallGPA = $totalUnits > 0 ? $totalGradePoints / $totalUnits : 0;

        // Load student data
        $student->load(['user', 'program.department']);

        // Generate PDF
        $pdf = PDF::loadView('reports.tor', [
            'student' => $student,
            'termGroups' => $termGroups,
            'termSummaries' => $termSummaries,
            'overallGPA' => round($overallGPA, 2),
            'totalUnits' => $totalUnits,
            'generatedDate' => now()->format('F d, Y'),
        ]);

        return $pdf->download('TOR_' . $student->user->official_id . '.pdf');
    }

    // Export Enrollment Statistics as PDF
    public function exportEnrollmentStatistics(Request $request)
    {
        $academicTermId = $request->input('academic_term_id');
        $programId = $request->input('program_id');
        $yearLevel = $request->input('year_level');

        $term = AcademicTerm::find($academicTermId);
        $program = Program::find($programId);

        // Get the same data as enrollmentStatistics method
        $enrollmentQuery = Enrollment::query();

        if ($academicTermId) {
            $enrollmentQuery->where('academic_term_id', $academicTermId);
        }

        if ($programId) {
            $enrollmentQuery->whereHas('student', function ($q) use ($programId) {
                $q->where('program_id', $programId);
            });
        }

        if ($yearLevel) {
            $enrollmentQuery->where('year_level', $yearLevel);
        }

        $stats = [
            'total_enrollments' => $enrollmentQuery->count(),
            'enrolled' => (clone $enrollmentQuery)->where('status', 'enrolled')->count(),
            'completed' => (clone $enrollmentQuery)->where('status', 'completed')->count(),
        ];

        $byProgram = Enrollment::select('programs.name', DB::raw('count(*) as total'))
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('programs', 'students.program_id', '=', 'programs.id')
            ->when($academicTermId, function ($q) use ($academicTermId) {
                $q->where('enrollments.academic_term_id', $academicTermId);
            })
            ->when($yearLevel, function ($q) use ($yearLevel) {
                $q->where('enrollments.year_level', $yearLevel);
            })
            ->groupBy('programs.id', 'programs.name')
            ->get();

        $byYearLevel = Enrollment::select('year_level', DB::raw('count(*) as total'))
            ->when($academicTermId, function ($q) use ($academicTermId) {
                $q->where('academic_term_id', $academicTermId);
            })
            ->when($programId, function ($q) use ($programId) {
                $q->whereHas('student', function ($query) use ($programId) {
                    $query->where('program_id', $programId);
                });
            })
            ->when($yearLevel, function ($q) use ($yearLevel) {
                $q->where('year_level', $yearLevel);
            })
            ->groupBy('year_level')
            ->orderBy('year_level')
            ->get();

        $pdf = PDF::loadView('reports.enrollment-statistics', [
            'term' => $term,
            'program' => $program,
            'yearLevel' => $yearLevel,
            'stats' => $stats,
            'byProgram' => $byProgram,
            'byYearLevel' => $byYearLevel,
            'generatedDate' => now()->format('F d, Y'),
        ]);

        return $pdf->download('Enrollment_Statistics_' . now()->format('Y-m-d') . '.pdf');
    }

    // Generate Class List for a scheduled subject
    public function classList(Request $request)
    {
        $scheduledSubjectId = $request->input('scheduled_subject_id');

        // This will be called from the Classes/Instructor view
        $scheduledSubject = \App\Models\ScheduledSubject::with([
            'academicTerm',
            'block',
            'curriculumSubject.subject',
            'instructor.user',
            'enrolledSubjects' => function ($q) {
                $q->with('enrollment.student.user')
                    ->where('status', '!=', 'dropped')
                    ->orderBy(function ($query) {
                        $query->select('last_name')
                            ->from('users')
                            ->join('students', 'users.id', '=', 'students.user_id')
                            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
                            ->whereColumn('enrollments.id', 'enrolled_subjects.enrollment_id')
                            ->limit(1);
                    });
            }
        ])->findOrFail($scheduledSubjectId);

        $pdf = Pdf::loadView('reports.class-list', [
            'scheduledSubject' => $scheduledSubject,
            'generatedDate' => now()->format('F d, Y'),
        ]);

        return $pdf->download('ClassList_' . $scheduledSubject->curriculumSubject->subject->code . '.pdf');
    }

}
