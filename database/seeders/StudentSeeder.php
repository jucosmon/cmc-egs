<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Student user IDs start from 14 (after admin, dean, registrar, and 10 instructors)
        for ($i = 14; $i <= 63; $i++) {
            $blockId = rand(1, 48); // Random block assignment
            $programId = rand(1, 4); // Random program

            Student::create([
                'user_id' => $i,
                'year_level' => rand(1, 4),
                'status' => 'regular',
                'block_id' => $blockId,
                'program_id' => $programId,
            ]);
        }
    }
}
