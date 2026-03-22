# Role Testing Guide (Seeded Accounts, Flow)

Use this guide with the seeded accounts in `database/seeders/UserSeeder.php`.

## Seeded Test Accounts (Password: `password`)

| Role         | Name             | Email                    |
| ------------ | ---------------- | ------------------------ |
| it_admin     | John Doe         | admin@cmc.edu.ph         |
| registrar    | Pedro Cruz       | registrar@cmc.edu.ph     |
| dean         | Maria Garcia     | dean.coecs@cmc.edu.ph    |
| dean         | Loren Villanueva | dean.cba@cmc.edu.ph      |
| program_head | Ana Mendoza      | ph.bsit@cmc.edu.ph       |
| program_head | Renato Sison     | ph.bscs@cmc.edu.ph       |
| program_head | Dianne Rosales   | ph.bsba@cmc.edu.ph       |
| program_head | Paolo Lazaro     | ph.bsa@cmc.edu.ph        |
| instructor   | Rhea Santos      | inst.it@cmc.edu.ph       |
| instructor   | Armand Delgado   | inst.cs@cmc.edu.ph       |
| instructor   | Janelle Pascual  | inst.ba@cmc.edu.ph       |
| instructor   | Victor Alvarez   | inst.acc@cmc.edu.ph      |
| student      | Kyle Reyes       | student.bsit1@cmc.edu.ph |
| student      | Mia Santos       | student.bsit2@cmc.edu.ph |
| student      | Noel Garcia      | student.bscs1@cmc.edu.ph |
| student      | Ivy Castro       | student.bscs2@cmc.edu.ph |
| student      | Liam Pena        | student.bsba1@cmc.edu.ph |
| student      | Alyssa Santiago  | student.bsba2@cmc.edu.ph |
| student      | Jared Dizon      | student.bsa1@cmc.edu.ph  |
| student      | Celine Bernardo  | student.bsa2@cmc.edu.ph  |

## Test Flow (Role by Role)

Follow this exact flow. Log out after each role.

### Step 1: IT Admin

Login: `admin@cmc.edu.ph` / `password`

- Open Accounts. Confirm all seeded users exist.
- Open Departments and Programs. Confirm programs and heads exist.

### Step 2: Dean (COECS)

Login: `dean.coecs@cmc.edu.ph` / `password`

- Open Programs. Verify only COECS programs are visible.
- Open a program and check details.

### Step 3: Registrar

Login: `registrar@cmc.edu.ph` / `password`

- Open Enrollments list.
- Open Grades/Reports pages and confirm access.

### Step 4: Program Head (BSIT)

Login: `ph.bsit@cmc.edu.ph` / `password`

- Open Manage Enrollment.
- Confirm blocks and schedules for BSIT are visible.
- Open Instructor Loads and confirm only BSIT-related loads.

### Step 5: Instructor (IT)

Login: `inst.it@cmc.edu.ph` / `password`

- Open your classes.
- Open grade encoding for your class and confirm you can edit only your sections.

### Step 6: Student (BSIT)

Login: `student.bsit1@cmc.edu.ph` / `password`

- Open Enrollment and Grades.
- Confirm only your data is visible.

## Notes

- These accounts come from `database/seeders/UserSeeder.php`.
- If a page is empty, check related seeders (Program, Block, Schedule, Enrollment).
