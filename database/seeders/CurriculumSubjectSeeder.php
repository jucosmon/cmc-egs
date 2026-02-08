<?php

namespace Database\Seeders;

use App\Models\Curriculum;
use App\Models\CurriculumSubject;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class CurriculumSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $curricula = Curriculum::with('program')->get()
            ->keyBy(fn ($curriculum) => $curriculum->program->code);

        $subjectCodes = [
            'IT 101', 'IT 102', 'IT 103', 'IT 201', 'IT 202',
            'CS 101', 'CS 102', 'CS 103', 'CS 201',
            'BUS 101', 'BUS 102', 'MKT 101', 'MGT 101',
            'ACC 101', 'ACC 102', 'TAX 101', 'ECON 101',
            'ENG 101', 'ENG 102', 'MATH 101', 'MATH 102',
            'PE 101', 'NSTP 101',
        ];

        $subjects = Subject::whereIn('code', $subjectCodes)->get()->keyBy('code');

        $curriculumSubjects = [
            'BSIT' => [
                ['code' => 'IT 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'IT 102', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'ENG 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'MATH 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'NSTP 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'PE 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'IT 103', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'IT 201', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'IT 202', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'ENG 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'minor'],
                ['code' => 'MATH 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'minor'],
            ],
            'BSCS' => [
                ['code' => 'CS 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'CS 102', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'ENG 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'MATH 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'CS 103', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'CS 201', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'ENG 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'minor'],
                ['code' => 'MATH 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'minor'],
            ],
            'BSBA' => [
                ['code' => 'BUS 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'ACC 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'ECON 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'ENG 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'MATH 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'BUS 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'ACC 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'MKT 101', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'ENG 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'minor'],
                ['code' => 'PE 101', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'minor'],
            ],
            'BSA' => [
                ['code' => 'ACC 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'BUS 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'ECON 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'major'],
                ['code' => 'ENG 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'MATH 101', 'year_level' => 1, 'semester' => 'first', 'course_type' => 'minor'],
                ['code' => 'ACC 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'TAX 101', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'MGT 101', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'major'],
                ['code' => 'ENG 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'minor'],
                ['code' => 'MATH 102', 'year_level' => 1, 'semester' => 'second', 'course_type' => 'minor'],
            ],
        ];

        foreach ($curriculumSubjects as $programCode => $entries) {
            $curriculum = $curricula->get($programCode);

            if (!$curriculum) {
                continue;
            }

            foreach ($entries as $entry) {
                $subject = $subjects->get($entry['code']);

                if (!$subject) {
                    continue;
                }

                CurriculumSubject::create([
                    'year_level' => $entry['year_level'],
                    'semester' => $entry['semester'],
                    'course_type' => $entry['course_type'],
                    'has_laboratory' => false,
                    'subject_id' => $subject->id,
                    'curriculum_id' => $curriculum->id,
                ]);
            }
        }
    }
}
