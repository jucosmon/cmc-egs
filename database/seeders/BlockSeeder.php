<?php

namespace Database\Seeders;

use App\Models\Block;
use App\Models\Program;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    public function run(): void
    {
        $programs = Program::whereIn('code', ['BSIT', 'BSCS', 'BSBA', 'BSA'])->get();

        foreach ($programs as $program) {
            foreach (['A', 'B'] as $section) {
                Block::create([
                    'code' => "{$program->code}-2025-{$section}",
                    'admission_year' => 2025,
                    'status' => 'active',
                    'program_id' => $program->id,
                ]);
            }
        }
    }
}
