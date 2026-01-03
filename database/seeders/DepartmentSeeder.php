<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::create([
            'name' => 'College of Engineering and Computer Studies',
            'code' => 'COECS',
            'is_active' => true,
            'dean_id' => 2, // Dean user
        ]);

        Department::create([
            'name' => 'College of Business and Accountancy',
            'code' => 'CBA',
            'is_active' => true,
            'dean_id' => null,
        ]);

        Department::create([
            'name' => 'College of Arts and Sciences',
            'code' => 'CAS',
            'is_active' => true,
            'dean_id' => null,
        ]);

        Department::create([
            'name' => 'College of Education',
            'code' => 'COED',
            'is_active' => true,
            'dean_id' => null,
        ]);
    }
}
