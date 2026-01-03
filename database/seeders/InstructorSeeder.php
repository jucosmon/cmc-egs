<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            'Software Engineering',
            'Database Management',
            'Network Security',
            'Web Development',
            'Mobile Application Development',
            'Artificial Intelligence',
            'Data Science',
            'Computer Architecture',
            'Algorithm Design',
        ];

        // Program head (user_id 4)
        Instructor::create([
            'user_id' => 4,
            'department_id' => 1, // COECS
            'specialization' => 'Software Engineering',
            'is_active' => true,
        ]);

        // Other instructors (user_ids 5-13)
        for ($i = 5; $i <= 13; $i++) {
            Instructor::create([
                'user_id' => $i,
                'department_id' => rand(1, 4),
                'specialization' => $specializations[array_rand($specializations)],
                'is_active' => true,
            ]);
        }
    }
}
