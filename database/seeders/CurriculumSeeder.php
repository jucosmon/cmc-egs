<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\Program;
use Illuminate\Database\Seeder;

class CurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::whereIn('code', ['BSIT', 'BSCS', 'BSBA', 'BSA'])->get()->keyBy('code');

        $curricula = [
            ['code' => 'BSIT', 'name' => 'BSIT Curriculum 2024', 'year_effective' => 2024, 'is_active' => true],
            ['code' => 'BSCS', 'name' => 'BSCS Curriculum 2024', 'year_effective' => 2024, 'is_active' => true],
            ['code' => 'BSBA', 'name' => 'BSBA Curriculum 2024', 'year_effective' => 2024, 'is_active' => true],
            ['code' => 'BSA', 'name' => 'BSA Curriculum 2024', 'year_effective' => 2024, 'is_active' => true],
        ];

        foreach ($curricula as $curriculum) {
            $program = $programs->get($curriculum['code']);

            if (!$program) {
                continue;
            }

            Curriculum::create([
                'name' => $curriculum['name'],
                'is_active' => $curriculum['is_active'],
                'year_effective' => $curriculum['year_effective'],
                'program_id' => $program->id,
            ]);
        }
    }
}
