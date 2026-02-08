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

        foreach ($enrollments as $enrollment) {
            $scheduledSubjects = ScheduledSubject::where('academic_term_id', $enrollment->academic_term_id)
                ->where('block_id', $enrollment->block_id)
                ->get();

            $gradeIndex = 0;

            foreach ($scheduledSubjects as $scheduledSubject) {
                $status = $enrollment->status === 'completed' ? 'completed' : 'enrolled';
                $finalGrade = null;
                $midtermGrade = null;

                if ($status === 'completed') {
                    $finalGrade = $gradeSteps[$gradeIndex % count($gradeSteps)];
                    $midtermGrade = min(3.0, $finalGrade + 0.25);
                    $gradeIndex++;
                }

                EnrolledSubject::create([
                    'status' => $status,
                    'midterm_grade' => $midtermGrade,
                    'final_grade' => $finalGrade,
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
                    $failedSubject->update([
                        'midterm_grade' => 4.0,
                        'final_grade' => 5.0,
                        'status' => 'completed',
                    ]);
                }
            }
        }
    }
}
