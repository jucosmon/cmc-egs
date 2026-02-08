# Program Head Manage - Troubleshooting Guide

## Issues Found & Fixed

### 1. **Missing API Endpoint** ❌→✅

**Problem:** Frontend was calling `/api/blocks/{blockId}/available-subjects` but this endpoint didn't exist.

**Fix Applied:**

- Added `getAvailableSubjects(Block $block)` method to `BlockController`
- Added route: `Route::get('api/blocks/{block}/available-subjects', [BlockController::class, 'getAvailableSubjects'])`

**What it does:**

- Gets the block's program
- Fetches the program's active curriculum
- Returns all curriculum subjects with their year_level and semester info

---

### 2. **Block Year Level Not Stored** ❌→✅

**Problem:** Block only had `admission_year`, but no direct `year_level` field.

**How it's calculated (Frontend):**

```javascript
const currentYear = new Date().getFullYear();
const yearsSinceAdmission = currentYear - block.admission_year;
const currentYearLevel = Math.min(yearsSinceAdmission + 1, 4);
```

**Example:**

- Block admitted in: 2024
- Current year: 2026
- Years since admission: 2026 - 2024 = 2
- Current year level: 2 + 1 = **Year 3**

**Note:** This is calculated dynamically. Update the Block's admission_year if needed to adjust the year level.

---

### 3. **No Semester Match** ❌→✅

**Problem:** Curriculum subjects are stored with semester (first, second, summer), but active term's semester wasn't being compared properly.

**Fix Applied:**

- Updated filter logic: `s.semester === currentSemester`
- Ensures only subjects for the current term's semester are shown

**Data Structure:**

```
Curriculum Subject:
- year_level: 1-4
- semester: "first" | "second" | "summer"

Academic Term:
- semester: "first" | "second" | "summer"
```

---

## Debugging Checklist

### Step 1: Verify Database Setup

```bash
# Check if curriculum_subjects table has required columns
php artisan tinker
> DB::table('curriculum_subjects')->first()
```

Should show:

- `id`, `year_level`, `semester`, `curriculum_id`, `subject_id`, `course_type`, `has_laboratory`

---

### Step 2: Check Active Curriculum

```bash
php artisan tinker
> use App\Models\Program;
> $program = Program::find(1); // Replace with your program ID
> $curriculum = $program->curriculums()->where('is_active', true)->first();
> $curriculum ? "✓ Active curriculum found" : "✗ No active curriculum"
```

**If no active curriculum:**

1. Go to `Curriculums` section in admin
2. Create/activate a curriculum for the program
3. Add curriculum subjects with year levels and semesters

---

### Step 3: Check Academic Term

```bash
php artisan tinker
> use App\Models\AcademicTerm;
> $term = AcademicTerm::where('is_active', true)->first();
> if($term) { dd($term->only(['id', 'academic_year', 'semester', 'is_active'])); }
```

**Expected output:**

```
academic_year: "2025-2026"
semester: "first" (or "second", "summer")
is_active: true
```

---

### Step 4: Test API Endpoint

```bash
# In browser developer console or using curl
curl "http://localhost:8000/api/blocks/1/available-subjects"
```

**Expected response:**

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
    ...
  ]
}
```

---

### Step 5: Check Browser Console

Open browser DevTools (F12) → Console tab

**Look for log messages like:**

```
[BlockManage] Fetching subjects for block 1
[BlockManage] Received subjects: {subjects: Array(12)}
[BlockManage] Filtering subjects for block BSCS-2024-A: {
  admissionYear: 2024,
  currentYear: 2026,
  yearsSinceAdmission: 2,
  calculatedYearLevel: 3,
  currentSemester: "first",
  totalAvailableSubjects: 12
}
[BlockManage] Final filtered subjects: 4 (Array)
```

---

## Common Issues & Solutions

### Issue: "No required subjects found"

**Possible Causes & Solutions:**

1. **No Active Curriculum**
    - Go to Programs → View Program → Curriculums
    - Check if a curriculum is marked as "Active"
    - If not, activate one

2. **Curriculum has no subjects for this year level**
    - Edit the curriculum
    - Check if subjects are added for year levels 1-4
    - Example: If block is in Year 3, there should be subjects with year_level = 3

3. **Semester mismatch**
    - Check the active academic term's semester (first/second/summer)
    - Check if curriculum subjects have matching semester values
    - Edit curriculum subjects if needed

4. **API endpoint not working**
    - Check routes: `php artisan route:list | grep "available-subjects"`
    - Should show: `GET /api/blocks/{block}/available-subjects`
    - Check for 404 errors in browser console

5. **Block's admission year is wrong**
    - This affects the calculated year level
    - If block was admitted in 2025, current year is 2026, they're Year 1
    - If block was admitted in 2024, current year is 2026, they're Year 3

---

## Code Architecture

### Frontend Flow

```
ProgramHeadManage.vue
├── View Block (button click)
├── → viewBlock(block)
├── → fetchAvailableSubjects(block.id)
│    └── GET /api/blocks/{block}/available-subjects
│         └── Returns: { subjects: [...] }
├── → Stores in: availableSubjects (ref)
└── → getRequiredSubjects(block) filters by year_level + semester
     └── Displays in dropdown for creating schedule
```

### Backend Flow

```
BlockController.getAvailableSubjects(Block $block)
├── Load: block.program.curriculums
├── Get: active curriculum
├── Query: CurriculumSubject where curriculum_id = active_curriculum.id
├── Map: Include subject info (code, title, units, year_level, semester)
└── Return: JSON { subjects: [...] }
```

---

## Data Validation

### Block

```php
'code' => string (max 20, unique)
'program_id' => exists in programs table
'admission_year' => integer (2000 - current year + 1)
'status' => 'active|inactive|graduated'
```

### Curriculum Subject

```php
'year_level' => integer (1-4)
'semester' => 'first|second|summer'
'course_type' => 'major|elective|minor'
'has_laboratory' => boolean
'subject_id' => exists in subjects table
'curriculum_id' => exists in curriculums table
```

### Scheduled Subject

```php
'day' => string (e.g., "MWF", "TTH")
'room' => string (e.g., "Room 301")
'time_start' => time format (H:i)
'time_end' => time format (H:i)
'academic_term_id' => exists in academic_terms table
'block_id' => exists in blocks table
'instructor_id' => nullable
'curriculum_subject_id' => exists in curriculum_subjects table
```

---

## Quick Test Scenario

1. **Go to Admin** → Create/confirm:
    - [ ] Active Program
    - [ ] Active Curriculum with subjects (year_level 1-4, different semesters)
    - [ ] Active Academic Term (with valid semester)

2. **Go to Program Head** → Enrollments:
    - [ ] Create a Block (admission_year = 2024)
    - [ ] Click "View Details"
    - [ ] Check browser console for success messages
    - [ ] Verify subjects appear in the form

3. **Monitor Console:**
    - Open F12 → Console
    - Look for "[BlockManage]" logs
    - Match the year level and semester calculations

---

## File Changes Summary

### Modified Files

1. `app/Http/Controllers/BlockController.php`
    - Added: `getAvailableSubjects(Block $block)` method

2. `routes/web.php`
    - Added: `Route::get('api/blocks/{block}/available-subjects', ...)`

3. `resources/js/Pages/Enrollments/ProgramHeadManage.vue`
    - Improved: `fetchAvailableSubjects()` with better error handling
    - Improved: `getRequiredSubjects()` with console logging
    - Added: Detailed documentation comments

---

## Next Steps

1. **Test the changes:**
    - Create/edit a block
    - Click "View Details"
    - Check if curriculum subjects appear

2. **If still not working:**
    - Check all steps in "Debugging Checklist"
    - Monitor browser console logs
    - Share console output in issues

3. **Optional improvements you could make:**
    - Add prerequisite checking before allowing subject scheduling
    - Add student enrollment status display
    - Add schedule conflict warning
    - Add grade entry interface

---

## Support Notes

All changes are backward compatible. The new endpoint doesn't break existing functionality.

**For questions:**

- Check console logs: `[BlockManage]` prefix
- Verify database has data
- Test with simple Program & Block first
- Check that Admin has set up active curriculum and term
