<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['code' => 'IT 101', 'title' => 'Introduction to Computing', 'units' => 3],
            ['code' => 'IT 102', 'title' => 'Computer Programming 1', 'units' => 3],
            ['code' => 'IT 103', 'title' => 'Computer Programming 2', 'units' => 3],
            ['code' => 'IT 104', 'title' => 'Data Structures and Algorithms', 'units' => 3],
            ['code' => 'IT 105', 'title' => 'Object-Oriented Programming', 'units' => 3],
            ['code' => 'IT 201', 'title' => 'Database Management Systems', 'units' => 3],
            ['code' => 'IT 202', 'title' => 'Web Development', 'units' => 3],
            ['code' => 'IT 203', 'title' => 'Software Engineering', 'units' => 3],
            ['code' => 'IT 204', 'title' => 'Information Management', 'units' => 3],
            ['code' => 'IT 205', 'title' => 'System Analysis and Design', 'units' => 3],
            ['code' => 'IT 301', 'title' => 'Network Administration', 'units' => 3],
            ['code' => 'IT 302', 'title' => 'Mobile Application Development', 'units' => 3],
            ['code' => 'IT 303', 'title' => 'Human Computer Interaction', 'units' => 3],
            ['code' => 'IT 304', 'title' => 'IT Project Management', 'units' => 3],
            ['code' => 'IT 305', 'title' => 'Capstone Project 1', 'units' => 3],
            ['code' => 'IT 401', 'title' => 'Capstone Project 2', 'units' => 3],
            ['code' => 'IT 402', 'title' => 'IT Ethics and Professionalism', 'units' => 3],
            ['code' => 'IT 403', 'title' => 'Emerging Technologies', 'units' => 3],
            ['code' => 'MATH 101', 'title' => 'College Algebra', 'units' => 3],
            ['code' => 'MATH 102', 'title' => 'Trigonometry', 'units' => 3],
            ['code' => 'ENG 101', 'title' => 'Communication Skills', 'units' => 3],
            ['code' => 'ENG 102', 'title' => 'Technical Writing', 'units' => 3],
            ['code' => 'FIL 101', 'title' => 'Komunikasyon sa Filipino', 'units' => 3],
            ['code' => 'PE 101', 'title' => 'Physical Education 1', 'units' => 2],
            ['code' => 'PE 102', 'title' => 'Physical Education 2', 'units' => 2],
            ['code' => 'NSTP 101', 'title' => 'National Service Training Program 1', 'units' => 3],
            ['code' => 'NSTP 102', 'title' => 'National Service Training Program 2', 'units' => 3],
        ];

        foreach ($subjects as $subject) {
            Subject::create([
                'code' => $subject['code'],
                'title' => $subject['title'],
                'description' => 'Description for ' . $subject['title'],
                'units' => $subject['units'],
            ]);
        }
    }
}
