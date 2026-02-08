<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Block;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['email' => 'student.bsit1@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-A'],
            ['email' => 'student.bsit2@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-A'],
            ['email' => 'student.bscs1@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-A'],
            ['email' => 'student.bscs2@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-A'],
            ['email' => 'student.bsba1@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-A'],
            ['email' => 'student.bsba2@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-A'],
            ['email' => 'student.bsa1@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-A'],
            ['email' => 'student.bsa2@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-A'],
        ];

        foreach ($students as $student) {
            $userId = User::where('email', $student['email'])->value('id');
            $programId = Program::where('code', $student['program'])->value('id');
            $blockId = Block::where('code', $student['block'])->value('id');

            if (!$userId || !$programId || !$blockId) {
                continue;
            }

            Student::create([
                'user_id' => $userId,
                'year_level' => 1,
                'status' => 'regular',
                'block_id' => $blockId,
                'program_id' => $programId,
            ]);
        }
    }
}
