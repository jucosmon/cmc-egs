# Changes Summary - Program Head Manage Fix

## What Was Wrong

You had everything on one page to manage enrollments, but "Required Subjects for Active Term" was showing "No required subjects found" because:

1. **Missing API Endpoint** - Frontend was calling an endpoint that didn't exist
2. **Year Level Calculation** - Block needs to dynamically calculate year level from admission_year
3. **No Semester Filtering** - Subjects weren't being filtered by the term's semester

---

## What I Fixed

### 1. Added Missing API Endpoint

**File:** `app/Http/Controllers/BlockController.php`

Added new method:

```php
public function getAvailableSubjects(Block $block)
{
    // Get the program's active curriculum
    $block->load('program.curriculums');
    $curriculum = $block->program->curriculums()
        ->where('is_active', true)
        ->first();

    if (!$curriculum) {
        return response()->json(['subjects' => []]);
    }

    // Return curriculum subjects with year_level and semester
    $curriculumSubjects = CurriculumSubject::where('curriculum_id', $curriculum->id)
        ->with('subject')
        ->get()
        ->map(function ($cs) {
            return [
                'id' => $cs->id,
                'subject_code' => $cs->subject->code,
                'subject_title' => $cs->subject->title,
                'units' => $cs->subject->units,
                'year_level' => $cs->year_level,
                'semester' => $cs->semester,
                'has_laboratory' => $cs->has_laboratory,
            ];
        });

    return response()->json(['subjects' => $curriculumSubjects]);
}
```

**File:** `routes/web.php`

Added new route:

```php
Route::get('api/blocks/{block}/available-subjects', [BlockController::class, 'getAvailableSubjects'])
    ->name('api.blocks.available-subjects');
```

---

### 2. Improved Vue Component Logic

**File:** `resources/js/Pages/Enrollments/ProgramHeadManage.vue`

**Improved `fetchAvailableSubjects()`:**

- Added error handling
- Added console logging for debugging
- Added HTTP error status checking

**Improved `getRequiredSubjects()`:**

- Better validation
- Console logging for debugging
- Explanation of year level calculation:
    ```javascript
    // Calculate from admission_year
    const currentYear = new Date().getFullYear();
    const yearsSinceAdmission = currentYear - block.admission_year;
    const currentYearLevel = Math.min(yearsSinceAdmission + 1, 4);
    ```
- Filter by both year_level AND semester
- Detailed logging of filtering process

**Added Documentation:**

- Explained data flow
- Documented year level calculation
- Added references to database schema

---

## How It Works Now

### Step-by-Step Flow

1. **Program Head views block details** → "View Details" button
2. **Browser calls API** → `/api/blocks/{blockId}/available-subjects`
3. **Backend returns** → All curriculum subjects from program's active curriculum
4. **Frontend filters by:**
    - `year_level` = calculated from admission_year
    - `semester` = current term's semester
5. **Shows required subjects** → Displays filtered subjects in the form
6. **Program head can schedule** → Select a subject, add day/time/room/instructor

---

## Year Level Calculation Example

| Admission Year | Current Year | Calculation           | Result |
| -------------- | ------------ | --------------------- | ------ |
| 2024           | 2026         | (2026 - 2024) + 1 = 3 | Year 3 |
| 2025           | 2026         | (2026 - 2025) + 1 = 2 | Year 2 |
| 2023           | 2026         | (2026 - 2023) + 1 = 4 | Year 4 |

---

## Database Schema (Unchanged)

**curriculum_subjects table:**

```
- id (PK)
- year_level (1-4)
- semester ('first', 'second', 'summer')
- course_type ('major', 'elective', 'minor')
- has_laboratory (boolean)
- subject_id (FK)
- curriculum_id (FK)
- created_at, updated_at
```

**blocks table:**

```
- id (PK)
- code (string)
- admission_year (integer) ← Used to calculate year level
- status ('active', 'inactive', 'graduated')
- program_id (FK)
- created_at, updated_at
```

**academic_terms table:**

```
- id (PK)
- academic_year (string, e.g. "2025-2026")
- semester ('first', 'second', 'summer') ← Used for filtering
- is_active (boolean)
- ... other date fields
```

---

## Testing the Fix

### Checklist

- [ ] Admin has set up an Active Curriculum for the Program
- [ ] Curriculum has subjects with year_level and semester values
- [ ] An Active Academic Term exists with a valid semester
- [ ] Block was created with admission_year set correctly

### In Browser

1. Open DevTools (F12) → Console tab
2. Look for logs starting with `[BlockManage]`
3. Should see:
    - "Fetching subjects for block X"
    - "Received subjects: {...}"
    - Filtering logs with calculation details
    - "Final filtered subjects: [...]"

### Expected Result

When you click "View Details" on a block:

- "Required Subjects for Active Term" should show a list
- Each subject should have code, title, units, year level, and semester
- "Add Schedule" button appears next to the heading

---

## Files Changed

1. **app/Http/Controllers/BlockController.php** - Added `getAvailableSubjects()` method
2. **routes/web.php** - Added API route for available subjects
3. **resources/js/Pages/Enrollments/ProgramHeadManage.vue** - Improved fetching & filtering logic

No database migrations needed. All data structures already exist.

---

## Troubleshooting

If "No required subjects found" still appears:

1. **Check Admin Setup:**
    - Verify Program has an Active Curriculum
    - Verify Curriculum has subjects for year_level matching calculated year
    - Verify Academic Term is Active with correct semester

2. **Check Browser Console:**
    - Open F12 → Console
    - Look for error messages or logs
    - Check network tab for API response

3. **Check Database:**

    ```bash
    # Terminal
    php artisan tinker
    > use App\Models\AcademicTerm;
    > AcademicTerm::where('is_active', true)->first()
    ```

4. **See TROUBLESHOOTING_GUIDE.md for detailed debugging steps**

---

## No Breaking Changes

- ✅ All new code is additive
- ✅ Existing functionality unchanged
- ✅ Backward compatible
- ✅ No migrations needed
