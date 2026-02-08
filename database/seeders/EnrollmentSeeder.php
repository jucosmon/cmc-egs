<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Seeder;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terms = AcademicTerm::where('academic_year', '2025-2026')->get()->keyBy('semester');
        $students = Student::with('block')->get();

        $firstTerm = $terms->get('first');
        $secondTerm = $terms->get('second');

        foreach ($students as $student) {
            if ($firstTerm) {
                Enrollment::create([
                    'status' => 'completed',
                    'year_level' => $student->year_level,
                    'enrolled_at' => $firstTerm->start_enrollment,
                    'academic_term_id' => $firstTerm->id,
                    'block_id' => $student->block_id,
                    'student_id' => $student->id,
                ]);
            }

            if ($secondTerm) {
                Enrollment::create([
                    'status' => 'enrolled',
                    'year_level' => $student->year_level,
                    'enrolled_at' => $secondTerm->start_enrollment,
                    'academic_term_id' => $secondTerm->id,
                    'block_id' => $student->block_id,
                    'student_id' => $student->id,
                ]);
            }
        }
    }
}
