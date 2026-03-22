# PSGC Address Component - Setup Checklist

## ✅ What Was Done

### 1. Created PsgcAddress.vue Component

**Location:** `resources/js/Components/PsgcAddress.vue`

**Features:**

- ✅ Cascading dropdowns (Province → City → Barangay)
- ✅ Uses free PSGC API (no auth needed)
- ✅ Auto-fetches data on mount
- ✅ Loading spinners on each dropdown
- ✅ Emits change events with full data
- ✅ Clean, professional Tailwind UI
- ✅ Supports initial/edit values

---

### 2. Updated User Forms

**Files Modified:**

- `resources/js/Pages/Accounts/Create.vue` - Now integrated with PsgcAddress
- `resources/js/Pages/Accounts/Edit.vue` - Now integrated with PsgcAddress

**Changes:**

- Added PsgcAddress component import
- Added address fields to form data
- Replaced plain textarea with cascading dropdowns
- Auto-generates full address from selections

---

### 3. Database Changes

**Migration Created:** `database/migrations/2026_03_23_000003_add_psgc_address_to_users_table.php`

**New Columns Added:**

```sql
province_code (varchar 10)
province_name (varchar 100)
city_code (varchar 10)
city_name (varchar 100)
barangay_code (varchar 10)
barangay_name (varchar 100)
```

**Model Updated:** `app/Models/User.php`

- Added all 6 fields to `$fillable` array

---

### 3. Documentation Created

- `PSGC_ADDRESS_INTEGRATION_GUIDE.md` - Complete integration guide
- `resources/js/Pages/PsgcAddressExamples.vue` - Example component with 3 use cases

---

## 🚀 Next Steps to Complete Setup

### Step 1: Run Database Migration

```bash
php artisan migrate
```

**Verify:**

```bash
php artisan tinker
>>> Schema::getColumns('users')
```

You should see 6 new columns for PSGC address fields.

---

### Step 2: Test Create Form

1. Go to Accounts → Create Student/User
2. Fill in basic fields (Name, Email, etc.)
3. Select Province → City → Barangay
4. Submit the form
5. Check database: `SELECT * FROM users ORDER BY id DESC;`

**Expected Result:**

- All 6 address fields populated
- `address` field contains full address string

---

### Step 3: Test Edit Form

1. Find a recently created user
2. Go to Edit
3. Verify province/city/barangay are pre-filled
4. Change selections
5. Submit
6. Verify database updated

---

### Step 4: (Optional) View Examples Page

Create a route if you want to see all examples:

**In `routes/web.php`:**

```php
Route::get('/examples/psgc-address', function () {
    return inertia('PsgcAddressExamples');
})->name('examples.psgc-address');
```

Then visit: `http://your-app/examples/psgc-address`

---

## 📋 Component Interface

### Props

```javascript
{
    modelValue: Object,  // Current selected address
    label: String,       // Section label
    required: Boolean,   // Show asterisk
    error: String        // Error message
}
```

### Emits

```javascript
@change="(data) => {}"      // Fires on any dropdown change
@update:modelValue="(data) => {}"  // v-model support
```

### Data Structure Emitted

```javascript
{
    province_code: "010000000",
    province_name: "ILOCOS NORTE",
    city_code: "010100000",
    city_name: "LAOAG CITY",
    barangay_code: "010100001",
    barangay_name: "SAN NICOLAS"
}
```

---

## 📝 Form Integration Pattern

### In Create Form

```vue
<PsgcAddress
    v-model="form"
    label="Address"
    :required="false"
    :error="form.errors.province_code"
    @change="
        (data) => {
            form.province_code = data.province_code;
            form.province_name = data.province_name;
            form.city_code = data.city_code;
            form.city_name = data.city_name;
            form.barangay_code = data.barangay_code;
            form.barangay_name = data.barangay_name;
            // Auto-generate full address
            const parts = [
                data.barangay_name,
                data.city_name,
                data.province_name,
            ].filter(Boolean);
            form.address = parts.join(', ');
        }
    "
/>
```

### In Backend (Controller)

```php
$validated = $request->validate([
    'province_code' => 'required|string',
    'province_name' => 'required|string',
    'city_code' => 'required|string',
    'city_name' => 'required|string',
    'barangay_code' => 'required|string',
    'barangay_name' => 'required|string',
]);

User::create($validated);
```

---

## 🔧 Troubleshooting

### Issue: Dropdowns empty, no data loading

**Possible Causes:**

- Internet connection issue
- Browser blocking API calls
- CORS restrictions

**Solution:**

- Check browser console (F12 → Console tab)
- Verify API is reachable: Visit https://psgc.gitlab.io/api/provinces/ in browser
- Check network tab for failed requests

---

### Issue: Selected values not showing in edit form

**Cause:** Missing field values from database

**Solution:**

1. Make sure migration was run: `php artisan migrate`
2. Verify user model has fields in `$fillable`
3. Check database directly: `SELECT province_code, city_code FROM users WHERE id = 1;`

---

### Issue: Form not accepting required validation

**Cause:** Validation not checking the fields

**Solution:**
Add validation in your request/controller:

```php
$request->validate([
    'province_code' => 'required',
    'city_code' => 'required',
    'barangay_code' => 'required',
]);
```

---

## 🎨 Customization

### Change Label Text

```vue
<PsgcAddress label="Residential Location" />
```

### Make Address Required

```vue
<PsgcAddress :required="true" />
```

### Custom Error Message

```vue
<PsgcAddress :error="form.errors.province_code || 'Please select a location'" />
```

### Styling

The component uses Tailwind CSS. Modify `PsgcAddress.vue` to customize colors, spacing, etc.

---

## 🧪 Testing Checklist

Use this to verify everything works:

- [ ] Migration ran successfully (`php artisan migrate`)
- [ ] Create form shows 3 cascading dropdowns
- [ ] Province dropdown loads on page load
- [ ] Selecting province loads cities
- [ ] Selecting city loads barangays
- [ ] Full address displays correctly
- [ ] Form submits with all 6 address fields
- [ ] Database has correct values
- [ ] Edit form pre-fills previous address
- [ ] Can change address in edit form
- [ ] All fields populate on change

---

## 📊 API Response Format Reference

The PSGC API returns simple JSON arrays:

**Provinces Response:**

```json
[
    {
        "code": "010000000",
        "name": "ILOCOS NORTE"
    },
    {
        "code": "020000000",
        "name": "ILOCOS SUR"
    }
]
```

**Cities/Municipalities Response:**

```json
[
    {
        "code": "010100000",
        "name": "LAOAG CITY"
    },
    {
        "code": "010200000",
        "name": "BATAC CITY"
    }
]
```

**Barangays Response:**

```json
[
    {
        "code": "010100001",
        "name": "SAN NICOLAS"
    },
    {
        "code": "010100002",
        "name": "KALIBO"
    }
]
```

---

## 🔗 References

- **Component:** `resources/js/Components/PsgcAddress.vue`
- **Create Form:** `resources/js/Pages/Accounts/Create.vue`
- **Edit Form:** `resources/js/Pages/Accounts/Edit.vue`
- **User Model:** `app/Models/User.php`
- **Migration:** `database/migrations/2026_03_23_000003_add_psgc_address_to_users_table.php`
- **Integration Guide:** `PSGC_ADDRESS_INTEGRATION_GUIDE.md`
- **Examples Page:** `resources/js/Pages/PsgcAddressExamples.vue`

---

## 💡 Tips for Using in Other Forms

### For Student Registration:

```vue
<PsgcAddress
    :model-value="studentData"
    label="Student Address"
    @change="updateStudentAddress"
/>
```

### For Instructor Management:

```vue
<PsgcAddress
    :model-value="instructorData"
    label="Office / Home Address"
    @change="updateInstructorAddress"
/>
```

### For Multiple Addresses:

```vue
<!-- Permanent -->
<PsgcAddress
    :model-value="permanentAddress"
    label="Permanent Address"
    @change="setPermanentAddress"
/>

<!-- Current -->
<PsgcAddress
    :model-value="currentAddress"
    label="Current Address"
    @change="setCurrentAddress"
/>
```

---

## ✨ Performance Notes

- First load: Fetches ~81 provinces from API
- Subsequent selections: Fetch cities (~20-100) then barangays (~10-100)
- All data cached in component (no redundant API calls)
- Total time: Usually < 2 seconds per selection
- No impact on page performance

---

## 🎯 Common Use Cases Covered

1. ✅ Create new user/student with address
2. ✅ Edit existing user/student address
3. ✅ Display selected address
4. ✅ Multiple address fields (permanent vs current)
5. ✅ Validate address selection
6. ✅ Auto-generate full address string

---

## Support

If you encounter any issues:

1. Check the integration guide: `PSGC_ADDRESS_INTEGRATION_GUIDE.md`
2. Review the examples: `resources/js/Pages/PsgcAddressExamples.vue`
3. Check browser console for API errors
4. Verify database migration ran
5. Ensure User model has fields in `$fillable`

---

**Status: Ready to Use** ✅

All files created and integrated. Just run migration and test!
