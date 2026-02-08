# Quick Reference - Program Head Manage Fix

## TL;DR - What Was Fixed

| Problem                                                          | Solution                                  | File                  |
| ---------------------------------------------------------------- | ----------------------------------------- | --------------------- |
| API endpoint `/api/blocks/{id}/available-subjects` doesn't exist | Added `getAvailableSubjects()` method     | BlockController       |
| Subjects aren't filtered by year level                           | Year level calculated from admission_year | ProgramHeadManage.vue |
| Subjects aren't filtered by semester                             | Added semester comparison in filter       | ProgramHeadManage.vue |

---

## Test It Now

1. **Open**: Program Head Manage page
2. **Click**: "View Details" on any block
3. **Wait**: For available subjects to load
4. **Check**: Browser console (F12) for `[BlockManage]` logs
5. **See**: "Required Subjects for Active Term" section populated

If empty: Check **Troubleshooting** section below

---

## Year Level Calculation

```
Year Level = min((Current Year - Admission Year) + 1, 4)

Examples:
- Admitted 2024, Current 2026 → Year 3
- Admitted 2025, Current 2026 → Year 2
- Admitted 2023, Current 2026 → Year 4
```

---

## Files Modified

```
✏️ app/Http/Controllers/BlockController.php
   └── Added: getAvailableSubjects() method

✏️ routes/web.php
   └── Added: GET /api/blocks/{block}/available-subjects route

✏️ resources/js/Pages/Enrollments/ProgramHeadManage.vue
   └── Improved: fetchAvailableSubjects() method
   └── Improved: getRequiredSubjects() method
```

---

## Data Checklist

Before testing, verify in Admin:

- [ ] **Active Curriculum** exists for the Program
- [ ] Curriculum has **Subjects** with:
    - [ ] year_level: 1, 2, 3, or 4
    - [ ] semester: "first", "second", or "summer"
- [ ] **Active Academic Term** exists with:
    - [ ] is_active: ✓
    - [ ] semester: matches curriculum subjects

---

## If "No required subjects found"

### Quick Checks

1. Is there an **Active Curriculum**?
    - Admin → Programs → [Program] → Curriculums → Check "Active"

2. Does curriculum have **subjects for this year**?
    - Admin → Programs → [Program] → Curriculum Details → Check year_level = 3

3. Is **Academic Term** active?
    - Admin → Academic Terms → [Term] → Check "Active"

4. Do **semester values match**?
    - Curriculum Subject semester = "first"
    - Academic Term semester = "first"
    - Block admission_year calculates correctly

### Debug in Console

F12 → Console and look for:

```
[BlockMarage] Fetching subjects for block 1       // ✓ API called
[BlockManage] Received subjects: {subjects: Array} // ✓ API response ok
[BlockManage] Filtering subjects...                // ✓ Filter running
[BlockManage] Final filtered subjects: 0          // ✗ Nothing matches
```

If you see "Final filtered subjects: 0", the calculation or semester doesn't match.

---

## API Endpoint

### Request

```
GET /api/blocks/1/available-subjects
```

### Response

```json
{
    "subjects": [
        {
            "id": 5,
            "subject_code": "CS101",
            "subject_title": "Introduction to Programming",
            "units": 3,
            "year_level": 1,
            "semester": "first",
            "has_laboratory": false
        },
        {
            "id": 6,
            "subject_code": "CS102",
            "subject_title": "Programming II",
            "units": 3,
            "year_level": 1,
            "semester": "first",
            "has_laboratory": true
        }
    ]
}
```

---

## Console Logs Overview

### Success

```
[BlockManage] Fetching subjects for block BSCS-2024-A
[BlockManage] Received subjects: {subjects: Array(15)}
[BlockManage] Filtering subjects for block BSCS-2024-A: {
  admissionYear: 2024,
  currentYear: 2026,
  yearsSinceAdmission: 2,
  calculatedYearLevel: 3,         ← Calculated from admission_year
  currentSemester: "first",        ← From active academic term
  totalAvailableSubjects: 15
}
[BlockManage] Final filtered subjects: 5 [
  {id: 10, subject_code: "CS301", year_level: 3, semester: "first"},
  {id: 11, subject_code: "CS302", year_level: 3, semester: "first"},
  ...
]
```

### Error

```
[BlockManage] Error fetching subjects for block 1: TypeError: Failed to fetch
  → Check Network tab, verify route is correct

[BlockManage] getRequiredSubjects: No block or subjects
  → Block not selected, or API returned empty array

[BlockManage] Received subjects: {subjects: []}
  → Curriculum has no subjects, or test with curriculum first
```

---

## Database Tables

### curriculum_subjects

```sql
CREATE TABLE curriculum_subjects (
  id BIGINT PRIMARY KEY,
  year_level INT,           -- 1, 2, 3, or 4
  semester ENUM('first', 'second', 'summer'),
  course_type ENUM('major', 'elective', 'minor'),
  has_laboratory BOOLEAN,
  subject_id BIGINT,
  curriculum_id BIGINT,
  created_at, updated_at
);
```

### blocks

```sql
CREATE TABLE blocks (
  id BIGINT PRIMARY KEY,
  code VARCHAR(20),        -- e.g., "BSCS-2024-A"
  admission_year INT,      -- e.g., 2024 (used for year level calculation)
  status ENUM('active', 'inactive', 'graduated'),
  program_id BIGINT,
  created_at, updated_at
);
```

---

## Common Errors

| Error                                     | Cause                                        | Fix                                                        |
| ----------------------------------------- | -------------------------------------------- | ---------------------------------------------------------- |
| 404 on `/api/blocks/1/available-subjects` | Route not registered                         | Clear route cache: `php artisan route:clear`               |
| `[]` empty subjects array                 | No active curriculum                         | Create & activate curriculum in admin                      |
| Subjects don't match                      | Semester mismatch                            | Check curriculum_subject.semester = academic_term.semester |
| `null` curriculum subjects                | Active curriculum exists but has no subjects | Add subjects to curriculum                                 |

---

## One-Minute Setup Test

```bash
# 1. Clear cache
php artisan route:clear
php artisan config:clear

# 2. Check if route exists
php artisan route:list | grep "available-subjects"
# Should output: GET api/blocks/{block}/available-subjects

# 3. Test with tinker
php artisan tinker

# Check if Block → Program → Curriculum chain works
> use App\Models\Block;
> $block = Block::find(1);
> $curriculum = $block->program->curriculums->where('is_active', true)->first();
> $curriculum ? "✓ Curriculum found" : "✗ No curriculum"

# Check if CurriculumSubjects exist
> use App\Models\CurriculumSubject;
> CurriculumSubject::where('curriculum_id', $curriculum->id)->count();
# Should return > 0

# Check active term
> use App\Models\AcademicTerm;
> AcademicTerm::where('is_active', true)->first();
# Should show semester value
```

---

## Support

### Create a GitHub Issue with:

1. Screenshot of "No required subjects found"
2. Browser console logs (paste `[BlockManage]` logs)
3. Result of: `php artisan tinker` → `AcademicTerm::where('is_active', true)->first()`
4. Result of: admin panel → Curriculums → Is there an active one?
5. Result of: admin panel → That curriculum → Does it have subjects?

---

## Files to Read More

- **TROUBLESHOOTING_GUIDE.md** - Detailed debugging steps
- **ARCHITECTURE.md** - Full system architecture & data flow
- **CHANGES_SUMMARY.md** - What changed and why
