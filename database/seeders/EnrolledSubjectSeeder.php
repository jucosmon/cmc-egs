<?php

namespace Database\Seeders;

use App\Models\EnrolledSubject;
use App\Models\Enrollment;
use App\Models\ScheduledSubject;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;

class EnrolledSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enrollments = Enrollment::all();
        $gradeSteps = [1.0, 1.25, 1.5, 1.75, 2.0, 2.25, 2.5, 2.75, 3.0];
        // CMC special codes for some subjects (Incomplete, Withdrawn, Never Attended, Dropped)
        $specialCodes = ['INC', 'W', 'NA', 'DR', 'NG'];
        $specialCodeIndex = 0;

        foreach ($enrollments as $enrollment) {
            $scheduledSubjects = ScheduledSubject::where('academic_term_id', $enrollment->academic_term_id)
                ->where('block_id', $enrollment->block_id)
                ->get();

            $gradeIndex = 0;

            foreach ($scheduledSubjects as $scheduledSubject) {
                $status = $enrollment->status === 'completed' ? 'completed' : 'enrolled';
                $finalGrade = null;
                $midtermGrade = null;
                $finalGradeCode = null;

                if ($status === 'completed') {
                    // Use numeric grades for most subjects
                    if ($gradeIndex % 7 !== 0) { // 6 out of 7 get numeric grades
                        $finalGrade = $gradeSteps[$gradeIndex % count($gradeSteps)];
                        $midtermGrade = min(3.0, $finalGrade + 0.25);
                    } else {
                        // 1 out of 7 gets a special code instead
                        $finalGradeCode = $specialCodes[$specialCodeIndex % count($specialCodes)];
                        $midtermGrade = 2.5; // Some midterm grade even with code
                        $specialCodeIndex++;
                    }
                    $gradeIndex++;
                }

                EnrolledSubject::create([
                    'status' => $status,
                    'midterm_grade' => $midtermGrade,
                    'final_grade' => $finalGrade,
                    'final_grade_code' => $finalGradeCode,
                    'enrollment_id' => $enrollment->id,
                    'scheduled_subject_id' => $scheduledSubject->id,
                ]);
            }
        }

        $userId = User::where('email', 'student.bsba1@cmc.edu.ph')->value('id');
        $studentId = $userId ? Student::where('user_id', $userId)->value('id') : null;

        if ($studentId) {
            $completedEnrollment = Enrollment::where('student_id', $studentId)
                ->where('status', 'completed')
                ->orderBy('academic_term_id')
                ->first();

            if ($completedEnrollment) {
                $failedSubject = EnrolledSubject::where('enrollment_id', $completedEnrollment->id)->first();

                if ($failedSubject) {
                    // Mark as never attended (NA) instead of numeric fail
                    $failedSubject->update([
                        'midterm_grade' => null,
                        'final_grade' => null,
                        'final_grade_code' => 'NA',
                        'status' => 'completed',
                    ]);
                }
            }
        }
    }
}
