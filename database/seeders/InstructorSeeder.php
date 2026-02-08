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
            [
                'email' => 'inst.it@cmc.edu.ph',
                'department_id' => $coecsId,
                'specialization' => 'Software Engineering',
            ],
            [
                'email' => 'inst.cs@cmc.edu.ph',
                'department_id' => $coecsId,
                'specialization' => 'Data Structures and Algorithms',
            ],
            [
                'email' => 'inst.ba@cmc.edu.ph',
                'department_id' => $cbaId,
                'specialization' => 'Business Management',
            ],
            [
                'email' => 'inst.acc@cmc.edu.ph',
                'department_id' => $cbaId,
                'specialization' => 'Financial Accounting',
            ],
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
