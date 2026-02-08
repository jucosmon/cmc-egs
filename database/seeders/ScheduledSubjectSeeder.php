<?php

namespace Database\Seeders;

use App\Models\AcademicTerm;
use App\Models\Block;
use App\Models\CurriculumSubject;
use App\Models\Instructor;
use App\Models\ScheduledSubject;
use Illuminate\Database\Seeder;

class ScheduledSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $terms = AcademicTerm::where('academic_year', '2025-2026')->get()->keyBy('semester');
        $blocks = Block::with('program')->get();
        $instructorsByDepartment = Instructor::all()->groupBy('department_id');

        $days = ['Monday', 'Wednesday', 'Friday'];
        $timeSlots = [
            ['08:00:00', '09:30:00'],
            ['10:00:00', '11:30:00'],
            ['13:00:00', '14:30:00'],
        ];

        foreach ($blocks as $block) {
            $programCode = $block->program->code;
            $departmentId = $block->program->department_id;
            $departmentInstructors = $instructorsByDepartment->get($departmentId, collect())->values();

            if ($departmentInstructors->isEmpty()) {
                continue;
            }

            foreach (['first', 'second'] as $semester) {
                $term = $terms->get($semester);

                if (!$term) {
                    continue;
                }

                $subjects = CurriculumSubject::whereHas('curriculum.program', function ($query) use ($programCode) {
                    $query->where('code', $programCode);
                })
                    ->where('semester', $semester)
                    ->orderBy('id')
                    ->take(3)
                    ->get();

                $slotIndex = 0;

                foreach ($subjects as $subject) {
                    $timeSlot = $timeSlots[$slotIndex % count($timeSlots)];
                    $day = $days[$slotIndex % count($days)];
                    $instructor = $departmentInstructors[$slotIndex % $departmentInstructors->count()];

                    $slotIndex++;

                    $roomNumber = 100 + $block->id;

                    ScheduledSubject::create([
                        'day' => $day,
                        'room' => 'RM-' . $roomNumber,
                        'time_start' => $timeSlot[0],
                        'time_end' => $timeSlot[1],
                        'academic_term_id' => $term->id,
                        'block_id' => $block->id,
                        'instructor_id' => $instructor->id,
                        'curriculum_subject_id' => $subject->id,
                    ]);
                }
            }
        }
    }
}
