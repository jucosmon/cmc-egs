# Architecture - Program Head Enrollment Management

## Component Relationships

```
Program Head Page (ProgramHeadManage.vue)
│
├── Displays: List of Blocks (from BlockController)
│   └── Each Block has:
│       - code, admission_year, program_id, status
│       - students_count
│       - scheduled_subjects (for active term)
│
├── When "View Details" clicked:
│   └── Fetch Available Subjects (NEW API)
│       └── GET /api/blocks/{id}/available-subjects
│           └── BlockController.getAvailableSubjects()
│               ├── Gets Block's Program
│               ├── Gets Program's Active Curriculum
│               └── Returns: CurriculumSubjects with year_level & semester
│
├── Filter Logic (Frontend):
│   └── getRequiredSubjects(block)
│       ├── Calculate: currentYearLevel from (currentYear - block.admission_year)
│       ├── Get: currentSemester from props.activeTerm.semester
│       └── Filter: subjects where year_level == currentYearLevel && semester == currentSemester
│
└── Create Schedule Form:
    ├── Select Subject (from filtered list)
    ├── Set: day, time_start, time_end, room, instructor_id
    └── POST /scheduled-subjects (ScheduledSubjectController.store)
        └── Creates record with curriculum_subject_id
```

---

## Data Flow Detailed

### 1. Load Page

```
Request: GET /enrollments (with program_head role)
↓
EnrollmentController.index()
  → Detects role is 'program_head'
  → Calls BlockController.index()
↓
BlockController.index()
  ├── Filter blocks by program_head.id
  ├── Load with relations:
  │   - students.user
  │   - scheduledSubjects.academicTerm
  │   - scheduledSubjects.instructor.user
  │   - scheduledSubjects.curriculumSubject.subject
  └── Return: Inertia::render('Enrollments/ProgramHeadManage', [
      'blocks' => $blocks (paginated),
      'programs' => [id, name, code],
      'activeTerm' => AcademicTerm (active one),
      'instructors' => Instructor list
  ])
↓
Response: Vue component renders block list
```

### 2. View Block Details

```
User Action: Click "View Details" button on a block
↓
Vue: viewBlock(block)
  ├── Set selectedBlock.value = block
  └── Call fetchAvailableSubjects(block.id)
↓
Request: GET /api/blocks/{id}/available-subjects
↓
BlockController.getAvailableSubjects(Block $block)
  ├── Load block.program.curriculums
  ├── Query: curriculums where is_active = true
  ├── If curriculum exists:
  │   └── Query: CurriculumSubject where curriculum_id = $curriculum->id
  │       └── Include relation: subject
  │       └── Map: [id, subject_code, subject_title, units, year_level, semester, has_laboratory]
  └── Return: response()->json(['subjects' => $curriculumSubjects])
↓
Vue: Store response in availableSubjects.value
↓
Display: Required Subjects section populated (if filtering succeeds)
```

### 3. Filter Subjects

```
Vue: getRequiredSubjects(block)
  ├── Calculate:
  │   ├── currentYear = 2026
  │   ├── admission_year = 2024 (from block)
  │   ├── yearsSinceAdmission = 2026 - 2024 = 2
  │   └── currentYearLevel = min(2 + 1, 4) = 3
  │
  ├── Get:
  │   └── currentSemester = props.activeTerm.semester (e.g., "first")
  │
  ├── Filter: availableSubjects
  │   └── Only keep where: year_level == 3 AND semester == "first"
  │
  └── Return: filtered array
↓
Display: Only matching subjects in subject selector dropdown
```

### 4. Create Schedule

```
User Action: Click "+ Add Schedule" → Select subject → Fill form → Submit
↓
Vue: createSchedule()
  └── Call: scheduleForm.post(route('scheduled-subjects.store'), ...)
↓
Request: POST /scheduled-subjects
Data: {
  curriculum_subject_id: number,
  day: string,
  room: string,
  time_start: time,
  time_end: time,
  instructor_id: number (optional),
  academic_term_id: number,
  block_id: number
}
↓
ScheduledSubjectController.store()
  ├── Validate all fields
  ├── Check: conflicts (block, instructor, room at same time)
  ├── If no conflicts:
  │   └── Create: ScheduledSubject record
  └── Return: redirect with success/error
↓
Vue: Reload blocks data
↓
Display: Schedule appears in "Created Schedules" section
```

---

## Model Relationships

```
Program
├── has (1:N) Block
├── has (1:N) Curriculum

Block
├── belongs_to Program
├── has (1:N) Student
├── has (1:N) ScheduledSubject
└── has (1:N) Enrollment

Curriculum
├── belongs_to Program
└── has (1:N) CurriculumSubject

CurriculumSubject
├── belongs_to Curriculum
├── belongs_to Subject
└── has (0:N) ScheduledSubject
      └── has (1:N) EnrolledSubject

ScheduledSubject
├── belongs_to Block
├── belongs_to AcademicTerm
├── belongs_to CurriculumSubject
├── belongs_to Instructor
└── has (1:N) EnrolledSubject

AcademicTerm
├── has (1:N) ScheduledSubject
├── has (1:N) Enrollment
└── (Active term is filtered by is_active = true)
```

---

## Key Calculations

### Year Level from Admission Year

```
Input: Block.admission_year
Process:
  current_year = 2026
  years_since_admission = 2026 - 2024 = 2
  year_level = min(2 + 1, 4) = 3

Output: Year Level 3
Location: Vue component (client-side)
Recalculated: Every time filter is applied
```

### Semester Matching

```
Input:
  - AcademicTerm.semester = "first"
  - Multiple CurriculumSubject.semester values = "first", "second", "summer"

Filter:
  Keep only subjects where semester == activeTerm.semester

Result:
  Only "first" semester subjects appear in dropdown
```

---

## API Endpoints

### List Blocks (Spaghetti Code - Routes to BlockController via EnrollmentController)

```
GET /enrollments
├── Role check: program_head
├── Redirect to: BlockController.index()
├── Returns: Inertia render with blocks data
└── Used by: ProgramHeadManage.vue
```

### Get Available Subjects (NEW - Direct to BlockController)

```
GET /api/blocks/{block}/available-subjects
├── No role check (in block resource route)
├── Parameter: block (Block model, auto-resolved)
├── Returns: JSON { subjects: [...] }
├── Used by: fetch() call in Vue component
└── Method: BlockController.getAvailableSubjects()
```

### Create Schedule

```
POST /scheduled-subjects
├── Data: curriculum_subject_id, day, room, time_start, time_end, academic_term_id, block_id, instructor_id
├── Validates: All fields, scheduling conflicts
├── Creates: ScheduledSubject record
├── Returns: redirect with flash message
└── Used by: scheduleForm.post() in Vue
```

---

## Middleware & Roles

### Program Head Accessing This Page

```
Route: GET /enrollments

EnrollmentController.index($request)
  if ($user->role === 'program_head') {
    return app(BlockController::class)->index($request);
  }

BlockController.index($request)
  $user = Auth::user();
  if ($user->role === 'program_head') {
    $programIds = Program::where('program_head_id', $user->id)->pluck('id');
    $query->whereIn('program_id', $programIds);
  }
  // Returns only blocks for their program(s)
```

### Accessing Available Subjects API

```
Route: GET api/blocks/{block}/available-subjects
Location: In block resource routes (middleware: role:it_admin,dean,program_head)

Accessible by:
- IT Admin (all blocks)
- Dean (all blocks)
- Program Head (only blocks of their program)

NOTE: API endpoint has no explicit auth check
      Relies on route middleware to allow access
```

---

## Debugging Logs

### Browser Console Prefixes

All logs use `[BlockManage]` prefix for easy filtering:

```javascript
// Normal flow
[BlockManage] Fetching subjects for block 1
[BlockManage] Received subjects: {...}
[BlockManage] Filtering subjects for block BSCS-2024-A: {...}
[BlockManage] Final filtered subjects: 4 [...]

// Issues
[BlockManage] getRequiredSubjects: No block or subjects
[BlockManage] Error fetching subjects for block 1: ...
```

---

## Common Issues & Root Causes

| Issue                                 | Possible Root Causes                                                                                           |
| ------------------------------------- | -------------------------------------------------------------------------------------------------------------- |
| "No required subjects found"          | No active curriculum, no subjects for year level, semester mismatch, API endpoint not found, calculation error |
| Subjects show but schedule won't save | Conflict detected (time/room/instructor), curriculum_subject_id doesn't exist, term not active                 |
| Block shows no students               | Students not enrolled, enrollment status wrong                                                                 |
| Can't create block                    | Unauthorized program, code not unique                                                                          |

---

## Important Notes

1. **Year level is calculated dynamically** - Not stored in database
2. **Semester filtering is case-sensitive** - Must be "first", "second", or "summer"
3. **Active curriculum is required** - Program must have one marked is_active = true
4. **Active term is required** - At least one academic term must be is_active = true
5. **Curriculum subjects must have year_level** - Values 1-4
6. **Admission year cannot exceed current year + 1**
7. **ScheduledSubject requires valid curriculum_subject_id** - Must exist in curriculum_subjects table

---

## Performance Considerations

- Blocks are paginated (15 per page)
- Curriculum subjects are loaded on demand (when viewing a block)
- ScheduledSubjects are eager-loaded with block
- No N+1 queries in index view
- Conflict checking uses efficient queries with orWhere optimizations

---

## Future Improvements

1. Add prerequisite checking before scheduling
2. Add student registration statistics to block view
3. Add schedule PDF export
4. Add bulk schedule creation
5. Add grade submission interface
6. Add attendance tracking
7. Split into separate smaller components (reduce file size)
