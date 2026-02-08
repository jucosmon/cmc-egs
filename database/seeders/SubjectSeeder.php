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
            ['code' => 'IT 201', 'title' => 'Database Management Systems', 'units' => 3],
            ['code' => 'IT 202', 'title' => 'Web Development Fundamentals', 'units' => 3],
            ['code' => 'IT 203', 'title' => 'Software Engineering', 'units' => 3],
            ['code' => 'IT 301', 'title' => 'Network Administration', 'units' => 3],
            ['code' => 'IT 305', 'title' => 'Capstone Project 1', 'units' => 3],
            ['code' => 'IT 401', 'title' => 'Capstone Project 2', 'units' => 3],
            ['code' => 'CS 101', 'title' => 'Discrete Structures', 'units' => 3],
            ['code' => 'CS 102', 'title' => 'Object-Oriented Programming', 'units' => 3],
            ['code' => 'CS 103', 'title' => 'Data Structures', 'units' => 3],
            ['code' => 'CS 201', 'title' => 'Algorithms and Complexity', 'units' => 3],
            ['code' => 'BUS 101', 'title' => 'Principles of Management', 'units' => 3],
            ['code' => 'BUS 102', 'title' => 'Business Communication', 'units' => 3],
            ['code' => 'MKT 101', 'title' => 'Principles of Marketing', 'units' => 3],
            ['code' => 'MGT 101', 'title' => 'Organization and Management', 'units' => 3],
            ['code' => 'ACC 101', 'title' => 'Financial Accounting', 'units' => 3],
            ['code' => 'ACC 102', 'title' => 'Managerial Accounting', 'units' => 3],
            ['code' => 'TAX 101', 'title' => 'Fundamentals of Taxation', 'units' => 3],
            ['code' => 'ECON 101', 'title' => 'Microeconomics', 'units' => 3],
            ['code' => 'ENG 101', 'title' => 'Communication Skills', 'units' => 3],
            ['code' => 'ENG 102', 'title' => 'Technical Writing', 'units' => 3],
            ['code' => 'MATH 101', 'title' => 'College Algebra', 'units' => 3],
            ['code' => 'MATH 102', 'title' => 'Trigonometry', 'units' => 3],
            ['code' => 'PE 101', 'title' => 'Physical Education 1', 'units' => 2],
            ['code' => 'NSTP 101', 'title' => 'National Service Training Program 1', 'units' => 3],
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
