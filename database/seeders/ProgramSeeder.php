<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $coecsId = Department::where('code', 'COECS')->value('id');
        $cbaId = Department::where('code', 'CBA')->value('id');

        $programHeads = [
            'BSIT' => User::where('email', 'ph.bsit@cmc.edu.ph')->value('id'),
            'BSCS' => User::where('email', 'ph.bscs@cmc.edu.ph')->value('id'),
            'BSBA' => User::where('email', 'ph.bsba@cmc.edu.ph')->value('id'),
            'BSA' => User::where('email', 'ph.bsa@cmc.edu.ph')->value('id'),
        ];

        Program::create([
            'name' => 'Bachelor of Science in Information Technology',
            'code' => 'BSIT',
            'degree_type' => 'bachelors',
            'total_units' => 180,
            'duration_years' => 4,
            'description' => 'A program focused on information technology and software development.',
            'is_active' => true,
            'program_head_id' => $programHeads['BSIT'],
            'department_id' => $coecsId,
        ]);

        Program::create([
            'name' => 'Bachelor of Science in Computer Science',
            'code' => 'BSCS',
            'degree_type' => 'bachelors',
            'total_units' => 186,
            'duration_years' => 4,
            'description' => 'A program emphasizing computer science theory and programming.',
            'is_active' => true,
            'program_head_id' => $programHeads['BSCS'],
            'department_id' => $coecsId,
        ]);

        Program::create([
            'name' => 'Bachelor of Science in Business Administration',
            'code' => 'BSBA',
            'degree_type' => 'bachelors',
            'total_units' => 150,
            'duration_years' => 4,
            'description' => 'A program focused on business management and entrepreneurship.',
            'is_active' => true,
            'program_head_id' => $programHeads['BSBA'],
            'department_id' => $cbaId,
        ]);

        Program::create([
            'name' => 'Bachelor of Science in Accountancy',
            'code' => 'BSA',
            'degree_type' => 'bachelors',
            'total_units' => 165,
            'duration_years' => 4,
            'description' => 'A program preparing students for accounting and auditing careers.',
            'is_active' => true,
            'program_head_id' => $programHeads['BSA'],
            'department_id' => $cbaId,
        ]);
    }
}
