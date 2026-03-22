<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        $coecsId = Department::where('code', 'COECS')->value('id');
        $cbaId = Department::where('code', 'CBA')->value('id');

        $instructors = [
            // COECS Instructors
            ['email' => 'inst.it1@cmc.edu.ph', 'department_id' => $coecsId, 'specialization' => 'Software Engineering & Web Development'],
            ['email' => 'inst.it2@cmc.edu.ph', 'department_id' => $coecsId, 'specialization' => 'Mobile & Cloud Computing'],
            ['email' => 'inst.cs1@cmc.edu.ph', 'department_id' => $coecsId, 'specialization' => 'Data Structures & Algorithms'],
            ['email' => 'inst.cs2@cmc.edu.ph', 'department_id' => $coecsId, 'specialization' => 'Artificial Intelligence & Machine Learning'],

            // CBA Instructors
            ['email' => 'inst.ba1@cmc.edu.ph', 'department_id' => $cbaId, 'specialization' => 'Business Management & Entrepreneurship'],
            ['email' => 'inst.ba2@cmc.edu.ph', 'department_id' => $cbaId, 'specialization' => 'Human Resource Management'],
            ['email' => 'inst.acc1@cmc.edu.ph', 'department_id' => $cbaId, 'specialization' => 'Financial Accounting & Auditing'],
            ['email' => 'inst.acc2@cmc.edu.ph', 'department_id' => $cbaId, 'specialization' => 'Taxation & Corporate Accounting'],
        ];

        foreach ($instructors as $instructor) {
            $userId = User::where('email', $instructor['email'])->value('id');

            if (!$userId) {
                continue;
            }

            Instructor::create([
                'user_id' => $userId,
                'department_id' => $instructor['department_id'],
                'specialization' => $instructor['specialization'],
                'is_active' => true,
            ]);
        }
    }
}

