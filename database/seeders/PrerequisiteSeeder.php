<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use Illuminate\Database\Seeder;

class PrerequisiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $curricula = Curriculum::with('program')->get()
            ->keyBy(fn ($curriculum) => $curriculum->program->code);

        $pairs = [
            'BSIT' => [
                ['subject' => 'IT 102', 'prereq' => 'IT 101'],
                ['subject' => 'IT 103', 'prereq' => 'IT 102'],
                ['subject' => 'IT 201', 'prereq' => 'IT 103'],
            ],
            'BSCS' => [
                ['subject' => 'CS 103', 'prereq' => 'CS 102'],
                ['subject' => 'CS 201', 'prereq' => 'CS 103'],
            ],
            'BSBA' => [
                ['subject' => 'BUS 102', 'prereq' => 'BUS 101'],
                ['subject' => 'ACC 102', 'prereq' => 'ACC 101'],
            ],
            'BSA' => [
                ['subject' => 'ACC 102', 'prereq' => 'ACC 101'],
                ['subject' => 'TAX 101', 'prereq' => 'ACC 102'],
            ],
        ];

        foreach ($pairs as $programCode => $items) {
            $curriculum = $curricula->get($programCode);

            if (!$curriculum) {
                continue;
            }

            $curriculumSubjects = CurriculumSubject::where('curriculum_id', $curriculum->id)
                ->with('subject')
                ->get()
                ->keyBy(fn ($item) => $item->subject->code);

            foreach ($items as $item) {
                $subject = $curriculumSubjects->get($item['subject']);
                $prereq = $curriculumSubjects->get($item['prereq']);

                if (!$subject || !$prereq) {
                    continue;
                }

                $subject->prerequisites()->syncWithoutDetaching([$prereq->id]);
            }
        }
    }
}
