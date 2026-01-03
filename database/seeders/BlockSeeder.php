<?php

namespace Database\Seeders;

use App\Models\Block;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [1 => 'BSIT', 2 => 'BSCS', 3 => 'BSBA', 4 => 'BSA'];
        $sections = ['A', 'B', 'C'];
        $years = [2024, 2023, 2022, 2021];

        foreach ($programs as $programId => $programCode) {
            foreach ($years as $year) {
                foreach ($sections as $section) {
                    Block::create([
                        'code' => "{$programCode}-{$year}-{$section}",
                        'admission_year' => $year,
                        'status' => $year >= 2023 ? 'active' : 'inactive',
                        'program_id' => $programId,
                    ]);
                }
            }
        }
    }
}
