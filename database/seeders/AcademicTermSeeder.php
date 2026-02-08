<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use Illuminate\Database\Seeder;

class AcademicTermSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terms = [
            [
                'academic_year' => '2025-2026',
                'semester' => 'first',
                'start_date' => '2025-08-11',
                'end_date' => '2025-12-12',
                'start_enrollment' => '2025-07-01',
                'end_enrollment' => '2025-08-05',
                'start_mid_grade' => '2025-10-06',
                'end_mid_grade' => '2025-10-20',
                'start_final_grade' => '2025-12-01',
                'end_final_grade' => '2025-12-12',
                'is_active' => false,
            ],
            [
                'academic_year' => '2025-2026',
                'semester' => 'second',
                'start_date' => '2026-01-12',
                'end_date' => '2026-05-16',
                'start_enrollment' => '2026-01-02',
                'end_enrollment' => '2026-01-15',
                'start_mid_grade' => '2026-03-09',
                'end_mid_grade' => '2026-03-20',
                'start_final_grade' => '2026-05-04',
                'end_final_grade' => '2026-05-16',
                'is_active' => true,
            ],
        ];

        foreach ($terms as $term) {
            AcademicTerm::create($term);
        }
    }
}
