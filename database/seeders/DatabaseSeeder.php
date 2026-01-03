<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DepartmentSeeder::class,
            InstructorSeeder::class,
            ProgramSeeder::class,
            BlockSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            CurriculumSeeder::class,
            CurriculumSubjectSeeder::class,
            PrerequisiteSeeder::class,
            AcademicTermSeeder::class,
            ScheduledSubjectSeeder::class,
            EnrollmentSeeder::class,
            EnrolledSubjectSeeder::class,
        ]);

    }
}
