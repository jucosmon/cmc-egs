<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Block;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // Philippine Birth places and schools for realistic data
        $birthPlaces = ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Marikina', 'Las Piñas', 'Caloocan', 'Parañaque', 'Cebu', 'Davao'];
        $religions = ['Catholic', 'Protestant', 'Assembly of God', 'Mormon', 'Pentecostal', 'Iglesia ni Cristo'];
        $citizenship = ['Filipino'];

        $elementarySchools = ['Philippine Science High School', 'Ateneo de Manila University', 'De La Salle University', 'Magandang Pag-asa School', 'Quezon City Science High School', 'Marikina Science High School'];
        $secondarySchools = ['Philippine Science High School', 'Ateneo de Manila University', 'De La Salle University', 'Sto. Domingo High School', 'Quezon City Science High School', 'Marikina Science High School'];

        $students = [
            // BSIT - Block A
            ['email' => 'student.bsit.a0@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-A', 'birth_place' => 'Manila', 'religion' => 'Catholic', 'father' => 'Roberto Reyes', 'mother' => 'Aurora Gonzales', 'elementary' => 'Philippine Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsit.a1@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-A', 'birth_place' => 'Quezon City', 'religion' => 'Catholic', 'father' => 'Carlos Santos', 'mother' => 'Maria Reyes', 'elementary' => 'Ateneo de Manila University', 'elementary_year' => '2013'],
            ['email' => 'student.bsit.a2@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-A', 'birth_place' => 'Makati', 'religion' => 'Protestant', 'father' => 'Juan Garcia', 'mother' => 'Rosa Torres', 'elementary' => 'De La Salle University', 'elementary_year' => '2013'],
            ['email' => 'student.bsit.a3@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-A', 'birth_place' => 'Pasig', 'religion' => 'Catholic', 'father' => 'Antonio Castro', 'mother' => 'Angela Silva', 'elementary' => 'Magandang Pag-asa School', 'elementary_year' => '2013'],

            // BSIT - Block B
            ['email' => 'student.bsit.b0@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-B', 'birth_place' => 'Marikina', 'religion' => 'Assembly of God', 'father' => 'Miguel Pena', 'mother' => 'Victoria Reyes', 'elementary' => 'Quezon City Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsit.b1@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-B', 'birth_place' => 'Las Piñas', 'religion' => 'Catholic', 'father' => 'Leonardo Santiago', 'mother' => 'Josephine Dizon', 'elementary' => 'Marikina Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsit.b2@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-B', 'birth_place' => 'Caloocan', 'religion' => 'Catholic', 'father' => 'Francisco Bernardo', 'mother' => 'Rebecca Mercado', 'elementary' => 'Philippine Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsit.b3@cmc.edu.ph', 'program' => 'BSIT', 'block' => 'BSIT-2025-B', 'birth_place' => 'Parañaque', 'religion' => 'Pentecostal', 'father' => 'Vicente Dalluran', 'mother' => 'Luz Flores', 'elementary' => 'Ateneo de Manila University', 'elementary_year' => '2013'],

            // BSCS - Block A
            ['email' => 'student.bscs.a0@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-A', 'birth_place' => 'Cebu', 'religion' => 'Catholic', 'father' => 'Pedro Dela Cruz', 'mother' => 'Elena Villanueva', 'elementary' => 'De La Salle University', 'elementary_year' => '2013'],
            ['email' => 'student.bscs.a1@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-A', 'birth_place' => 'Davao', 'religion' => 'Catholic', 'father' => 'Raul Gutierrez', 'mother' => 'Sandra Ramos', 'elementary' => 'Magandang Pag-asa School', 'elementary_year' => '2013'],
            ['email' => 'student.bscs.a2@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-A', 'birth_place' => 'Manila', 'religion' => 'Assembly of God', 'father' => 'Daniel Flores', 'mother' => 'Marissa Mendoza', 'elementary' => 'Quezon City Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bscs.a3@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-A', 'birth_place' => 'Quezon City', 'religion' => 'Catholic', 'father' => 'Winston Lopez', 'mother' => 'Theresa Martinez', 'elementary' => 'Marikina Science High School', 'elementary_year' => '2013'],

            // BSCS - Block B
            ['email' => 'student.bscs.b0@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-B', 'birth_place' => 'Makati', 'religion' => 'Catholic', 'father' => 'Sergio Torres', 'mother' => 'Priscilla Rosales', 'elementary' => 'Philippine Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bscs.b1@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-B', 'birth_place' => 'Pasig', 'religion' => 'Protestant', 'father' => 'Nelson Fernandez', 'mother' => 'Corazon Cruz', 'elementary' => 'Ateneo de Manila University', 'elementary_year' => '2013'],
            ['email' => 'student.bscs.b2@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-B', 'birth_place' => 'Marikina', 'religion' => 'Iglesia ni Cristo', 'father' => 'Emilio Hernandez', 'mother' => 'Aida Hernandez', 'elementary' => 'De La Salle University', 'elementary_year' => '2013'],
            ['email' => 'student.bscs.b3@cmc.edu.ph', 'program' => 'BSCS', 'block' => 'BSCS-2025-B', 'birth_place' => 'Las Piñas', 'religion' => 'Catholic', 'father' => 'Ricardo Rodriguez', 'mother' => 'Marcella Ortiz', 'elementary' => 'Magandang Pag-asa School', 'elementary_year' => '2013'],

            // BSBA - Block A
            ['email' => 'student.bsba.a0@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-A', 'birth_place' => 'Caloocan', 'religion' => 'Assembly of God', 'father' => 'Benito Aquino', 'mother' => 'Stella Gonzales', 'elementary' => 'Quezon City Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsba.a1@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-A', 'birth_place' => 'Parañaque', 'religion' => 'Catholic', 'father' => 'Ruben Valdez', 'mother' => 'Patricia Robles', 'elementary' => 'Marikina Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsba.a2@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-A', 'birth_place' => 'Cebu', 'religion' => 'Catholic', 'father' => 'Arnaldo tejada', 'mother' => 'Imelda Lim', 'elementary' => 'Philippine Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsba.a3@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-A', 'birth_place' => 'Manila', 'religion' => 'Pentecostal', 'father' => 'Oscar Sy', 'mother' => 'Victoria Tolentino', 'elementary' => 'Ateneo de Manila University', 'elementary_year' => '2013'],

            // BSBA - Block B
            ['email' => 'student.bsba.b0@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-B', 'birth_place' => 'Davao', 'religion' => 'Catholic', 'father' => 'Marcelino Dalluran', 'mother' => 'Evangelina Santos', 'elementary' => 'De La Salle University', 'elementary_year' => '2013'],
            ['email' => 'student.bsba.b1@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-B', 'birth_place' => 'Quezon City', 'religion' => 'Assembly of God', 'father' => 'Silvino Mercado', 'mother' => 'Anita Reyes', 'elementary' => 'Magandang Pag-asa School', 'elementary_year' => '2013'],
            ['email' => 'student.bsba.b2@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-B', 'birth_place' => 'Makati', 'religion' => 'Catholic', 'father' => 'Eduardo Flores', 'mother' => 'Remedios Ong', 'elementary' => 'Quezon City Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsba.b3@cmc.edu.ph', 'program' => 'BSBA', 'block' => 'BSBA-2025-B', 'birth_place' => 'Pasig', 'religion' => 'Iglesia ni Cristo', 'father' => 'Gilberto Sumalinog', 'mother' => 'Myrna Tolentino', 'elementary' => 'Marikina Science High School', 'elementary_year' => '2013'],

            // BSA - Block A
            ['email' => 'student.bsa.a0@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-A', 'birth_place' => 'Marikina', 'religion' => 'Catholic', 'father' => 'Ramon Villanueva', 'mother' => 'Lourdes Medina', 'elementary' => 'Philippine Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsa.a1@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-A', 'birth_place' => 'Las Piñas', 'religion' => 'Catholic', 'father' => 'Aurelio Tala', 'mother' => 'Grace Pascual', 'elementary' => 'Ateneo de Manila University', 'elementary_year' => '2013'],
            ['email' => 'student.bsa.a2@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-A', 'birth_place' => 'Caloocan', 'religion' => 'Assembly of God', 'father' => 'Rogelio Zamora', 'mother' => 'Josefina De Guzman', 'elementary' => 'De La Salle University', 'elementary_year' => '2013'],
            ['email' => 'student.bsa.a3@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-A', 'birth_place' => 'Parañaque', 'religion' => 'Catholic', 'father' => 'Herminio Castro', 'mother' => 'Angela Santiago', 'elementary' => 'Magandang Pag-asa School', 'elementary_year' => '2013'],

            // BSA - Block B
            ['email' => 'student.bsa.b0@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-B', 'birth_place' => 'Cebu', 'religion' => 'Catholic', 'father' => 'Roque Fernandez', 'mother' => 'Celia Fernandez', 'elementary' => 'Quezon City Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsa.b1@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-B', 'birth_place' => 'Manila', 'religion' => 'Pentecostal', 'father' => 'Faustino Gonzales', 'mother' => 'Josephine Mendoza', 'elementary' => 'Marikina Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsa.b2@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-B', 'birth_place' => 'Davao', 'religion' => 'Catholic', 'father' => 'Gregorio Aguilar', 'mother' => 'Rosario Flores', 'elementary' => 'Philippine Science High School', 'elementary_year' => '2013'],
            ['email' => 'student.bsa.b3@cmc.edu.ph', 'program' => 'BSA', 'block' => 'BSA-2025-B', 'birth_place' => 'Makati', 'religion' => 'Iglesia ni Cristo', 'father' => 'Marcelo Santos', 'mother' => 'Elisa Castillo', 'elementary' => 'Ateneo de Manila University', 'elementary_year' => '2013'],
        ];

        foreach ($students as $student) {
            $userId = User::where('email', $student['email'])->value('id');
            $programId = Program::where('code', $student['program'])->value('id');
            $blockId = Block::where('code', $student['block'])->value('id');

            if (!$userId || !$programId || !$blockId) {
                continue;
            }

            Student::create([
                'user_id' => $userId,
                'year_level' => 1,
                'status' => 'regular',
                'block_id' => $blockId,
                'program_id' => $programId,
                'birth_place' => $student['birth_place'],
                'religion' => $student['religion'],
                'citizenship' => 'Filipino',
                'father_name' => $student['father'],
                'mother_name' => $student['mother'],
                'elementary_school' => $student['elementary'],
                'elementary_year' => $student['elementary_year'],
                'secondary_school' => $student['secondary'] ?? array_rand(['Philippine Science High School' => 1, 'Ateneo de Manila University' => 1, 'De La Salle University' => 1]),
                'secondary_year' => '2019',
            ]);
        }
    }
}

