<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        Program::create([
            'name' => 'Bachelor of Science in Information Technology',
            'code' => 'BSIT',
            'degree_type' => 'bachelors',
            'total_units' => 180,
            'duration_years' => 4,
            'description' => 'A program focused on information technology and software development.',
            'is_active' => true,
            'program_head_id' => 5, // First instructor
            'department_id' => 1, // COECS
        ]);

        Program::create([
            'name' => 'Bachelor of Science in Computer Science',
            'code' => 'BSCS',
            'degree_type' => 'bachelors',
            'total_units' => 186,
            'duration_years' => 4,
            'description' => 'A program emphasizing computer science theory and programming.',
            'is_active' => true,
            'program_head_id' => null,
            'department_id' => 1, // COECS
        ]);

        Program::create([
            'name' => 'Bachelor of Science in Business Administration',
            'code' => 'BSBA',
            'degree_type' => 'bachelors',
            'total_units' => 150,
            'duration_years' => 4,
            'description' => 'A program focused on business management and entrepreneurship.',
            'is_active' => true,
            'program_head_id' => null,
            'department_id' => 2, // CBA
        ]);

        Program::create([
            'name' => 'Bachelor of Science in Accountancy',
            'code' => 'BSA',
            'degree_type' => 'bachelors',
            'total_units' => 165,
            'duration_years' => 4,
            'description' => 'A program preparing students for accounting and auditing careers.',
            'is_active' => true,
            'program_head_id' => null,
            'department_id' => 2, // CBA
        ]);
    }
}
