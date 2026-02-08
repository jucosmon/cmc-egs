<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $coecsDeanId = User::where('email', 'dean.coecs@cmc.edu.ph')->value('id');
        $cbaDeanId = User::where('email', 'dean.cba@cmc.edu.ph')->value('id');

        Department::create([
            'name' => 'College of Engineering and Computer Studies',
            'code' => 'COECS',
            'is_active' => true,
            'dean_id' => $coecsDeanId,
        ]);

        Department::create([
            'name' => 'College of Business and Accountancy',
            'code' => 'CBA',
            'is_active' => true,
            'dean_id' => $cbaDeanId,
        ]);
    }
}
