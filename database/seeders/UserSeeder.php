<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // IT Admin
        User::create([
            'email' => 'admin@cmc.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'it_admin',
            'official_id' => 'ADMIN001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'middle_name' => 'Smith',
            'phone' => '09123456789',
            'address' => '123 Main St, Quezon City',
            'date_of_birth' => '1985-05-15',
            'sex' => 'male',
            'is_active' => true,
        ]);

        // Dean
        User::create([
            'email' => 'dean@cmc.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'dean',
            'official_id' => 'DEAN001',
            'first_name' => 'Maria',
            'last_name' => 'Garcia',
            'middle_name' => 'Santos',
            'phone' => '09187654321',
            'address' => '456 University Ave, Quezon City',
            'date_of_birth' => '1975-08-20',
            'sex' => 'female',
            'is_active' => true,
        ]);

        // Registrar
        User::create([
            'email' => 'registrar@cmc.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'registrar',
            'official_id' => 'REG001',
            'first_name' => 'Pedro',
            'last_name' => 'Cruz',
            'middle_name' => 'Reyes',
            'phone' => '09161234567',
            'address' => '789 Admin Bldg, Quezon City',
            'date_of_birth' => '1980-03-10',
            'sex' => 'male',
            'is_active' => true,
        ]);

        // Program Head
        User::create([
            'email' => 'programhead@cmc.edu.ph',
            'password' => Hash::make('password'),
            'role' => 'program_head',
            'official_id' => 'PH001',
            'first_name' => 'Ana',
            'last_name' => 'Mendoza',
            'middle_name' => 'Lopez',
            'phone' => '09171234567',
            'address' => '321 Faculty St, Quezon City',
            'date_of_birth' => '1982-11-25',
            'sex' => 'female',
            'is_active' => true,
        ]);

        // Instructors
        for ($i = 2; $i <= 10; $i++) {
            User::create([
                'email' => "instructor{$i}@cmc.edu.ph",
                'password' => Hash::make('password'),
                'role' => 'instructor',
                'official_id' => 'INST' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'first_name' => 'Instructor',
                'last_name' => "Number{$i}",
                'middle_name' => 'Middle',
                'phone' => '0917' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'address' => "{$i} Faculty St, Quezon City",
                'date_of_birth' => '1985-01-01',
                'sex' => $i % 2 == 0 ? 'male' : 'female',
                'is_active' => true,
            ]);
        }

        // Students
        for ($i = 1; $i <= 50; $i++) {
            User::create([
                'email' => "student{$i}@cmc.edu.ph",
                'password' => Hash::make('password'),
                'role' => 'student',
                'official_id' => '2024-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => 'Student',
                'last_name' => "Number{$i}",
                'middle_name' => 'Middle',
                'phone' => '0917' . str_pad($i + 100, 7, '0', STR_PAD_LEFT),
                'address' => "{$i} Student St, Quezon City",
                'date_of_birth' => '2005-06-15',
                'sex' => $i % 2 == 0 ? 'male' : 'female',
                'is_active' => true,
            ]);
        }
    }
}
