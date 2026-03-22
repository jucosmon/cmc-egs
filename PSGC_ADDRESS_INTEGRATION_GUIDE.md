# PSGC Address Component - Integration Guide

## Overview

The `PsgcAddress.vue` component provides cascading dropdowns for Philippine addresses using the free PSGC API.

**Features:**

- ✅ Zero dependencies (uses native fetch API)
- ✅ Loading spinners on each dropdown
- ✅ Cascading: Province → City/Municipality → Barangay
- ✅ Auto-generates full address string
- ✅ Emits data with both codes and names
- ✅ Supports initial values for edit forms
- ✅ Clean, intuitive UI with Tailwind CSS

---

## Component Props

```javascript
props = {
    modelValue: {
        type: Object,
        default: () => ({
            province_code: '',
            province_name: '',
            city_code: '',
            city_name: '',
            barangay_code: '',
            barangay_name: '',
        })
    },
    label: String,           // Section label (e.g., "Address")
    required: Boolean,       // Show required asterisk
    error: String,          // Display validation error
}

// Emits
@change="(data) => {}"  // Fires when any dropdown changes
@update:modelValue="(data) => {}"  // v-model support
```

---

## Usage Examples

### Example 1: Creating a New User/Student (Create.vue)

The component is already integrated in Create.vue. It:

1. Fetches and displays provinces on mount
2. Lets user select province → city → barangay
3. Auto-generates full address string
4. Sends all fields to backend

**Key fields sent to backend:**

```php
$form->validate([
    'province_code' => 'required|string',
    'province_name' => 'required|string',
    'city_code' => 'required|string',
    'city_name' => 'required|string',
    'barangay_code' => 'required|string',
    'barangay_name' => 'required|string',
    'address' => 'nullable|string', // Street address (optional)
]);
```

---

### Example 2: Editing Existing User/Student (Edit.vue)

The component shows previously selected values on load:

```vue
<PsgcAddress
    :model-value="{
        province_code: form.province_code,
        province_name: form.province_name,
        city_code: form.city_code,
        city_name: form.city_name,
        barangay_code: form.barangay_code,
        barangay_name: form.barangay_name,
    }"
    :label="'Address Details'"
    :required="false"
    @change="
        (data) => {
            form.province_code = data.province_code;
            form.province_name = data.province_name;
            form.city_code = data.city_code;
            form.city_name = data.city_name;
            form.barangay_code = data.barangay_code;
            form.barangay_name = data.barangay_name;
            // Auto-generate full address string
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

---

### Example 3: Using in a Custom Form Component

```vue
<script setup>
import { ref } from "vue";
import PsgcAddress from "@/Components/PsgcAddress.vue";

const addressData = ref({
    province_code: "",
    province_name: "",
    city_code: "",
    city_name: "",
    barangay_code: "",
    barangay_name: "",
});

const handleAddressChange = (data) => {
    addressData.value = data;
    console.log("Selected address:", data);
    // Send to API, update parent, etc.
};
</script>

<template>
    <form class="space-y-6">
        <PsgcAddress
            :model-value="addressData"
            label="Residential Address"
            :required="true"
            @change="handleAddressChange"
        />

        <div>
            <p class="text-sm text-gray-600">Full Address:</p>
            <p class="font-medium">
                {{ addressData.barangay_name }}, {{ addressData.city_name }},
                {{ addressData.province_name }}
            </p>
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</template>
```

---

## Handling Address Data in Controllers

### Storing New Record

```php
// app/Http/Controllers/AccountController.php

public function store(AddressStoreRequest $request)
{
    $validated = $request->validated();

    User::create([
        'first_name' => $validated['first_name'],
        'last_name' => $validated['last_name'],
        'email' => $validated['email'],
        'phone' => $validated['phone'],

        // PSGC Address Fields
        'province_code' => $validated['province_code'],
        'province_name' => $validated['province_name'],
        'city_code' => $validated['city_code'],
        'city_name' => $validated['city_name'],
        'barangay_code' => $validated['barangay_code'],
        'barangay_name' => $validated['barangay_name'],

        // Optional: Auto-generated full address
        'address' => $validated['address'] ?? implode(', ', [
            $validated['barangay_name'],
            $validated['city_name'],
            $validated['province_name'],
        ]),
    ]);

    return redirect()->route('accounts.index')->with('success', 'Account created!');
}
```

### Updating Existing Record

```php
public function update(AddressUpdateRequest $request, User $user)
{
    $validated = $request->validated();

    $user->update([
        'province_code' => $validated['province_code'],
        'province_name' => $validated['province_name'],
        'city_code' => $validated['city_code'],
        'city_name' => $validated['city_name'],
        'barangay_code' => $validated['barangay_code'],
        'barangay_name' => $validated['barangay_name'],
        'address' => $validated['address'] ?? implode(', ', [
            $validated['barangay_name'],
            $validated['city_name'],
            $validated['province_name'],
        ]),
    ]);

    return redirect()->route('accounts.show', $user)->with('success', 'Address updated!');
}
```

---

## Creating Request Classes

### AddressStoreRequest.php

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|max:20',

            // PSGC Address Validation
            'province_code' => 'required|string|max:10',
            'province_name' => 'required|string|max:100',
            'city_code' => 'required|string|max:10',
            'city_name' => 'required|string|max:100',
            'barangay_code' => 'required|string|max:10',
            'barangay_name' => 'required|string|max:100',
            'address' => 'nullable|string|max:500', // Street address
        ];
    }

    public function messages(): array
    {
        return [
            'province_code.required' => 'Province is required',
            'city_code.required' => 'City/Municipality is required',
            'barangay_code.required' => 'Barangay is required',
        ];
    }
}
```

### AddressUpdateRequest.php

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'province_code' => 'nullable|string|max:10',
            'province_name' => 'nullable|string|max:100',
            'city_code' => 'nullable|string|max:10',
            'city_name' => 'nullable|string|max:100',
            'barangay_code' => 'nullable|string|max:10',
            'barangay_name' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
        ];
    }
}
```

---

## Database Migration

The migration `2026_03_23_000003_add_psgc_address_to_users_table.php` has been created and adds:

```php
$table->string('province_code', 10)->nullable();
$table->string('province_name', 100)->nullable();
$table->string('city_code', 10)->nullable();
$table->string('city_name', 100)->nullable();
$table->string('barangay_code', 10)->nullable();
$table->string('barangay_name', 100)->nullable();
```

**To run the migration:**

```bash
php artisan migrate
```

---

## Model Configuration

The User model's `$fillable` array has been updated to include:

- `province_code`
- `province_name`
- `city_code`
- `city_name`
- `barangay_code`
- `barangay_name`

---

## Displaying Address Information

### In User Profile/Show Page

```vue
<script setup>
import { defineProps } from "vue";

const props = defineProps({
    user: Object,
});
</script>

<template>
    <div class="space-y-4">
        <h3 class="text-lg font-semibold">Address Information</h3>

        <!-- PSGC Address -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Province</p>
                <p class="font-medium">{{ user.province_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">City/Municipality</p>
                <p class="font-medium">{{ user.city_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Barangay</p>
                <p class="font-medium">{{ user.barangay_name }}</p>
            </div>
        </div>

        <!-- Street Address (if any) -->
        <div v-if="user.address">
            <p class="text-sm text-gray-600">Street Address</p>
            <p class="font-medium">{{ user.address }}</p>
        </div>

        <!-- Full Address -->
        <div>
            <p class="text-sm text-gray-600">Full Address</p>
            <p class="font-medium text-blue-600">
                {{ user.barangay_name }}, {{ user.city_name }},
                {{ user.province_name }}
            </p>
        </div>
    </div>
</template>
```

---

## API Endpoints Used

The component uses the free PSGC API (no authentication required):

1. **Get All Provinces**

    ```
    GET https://psgc.gitlab.io/api/provinces/
    ```

2. **Get Cities for Province**

    ```
    GET https://psgc.gitlab.io/api/provinces/{provinceCode}/cities-municipalities/
    ```

3. **Get Barangays for City**
    ```
    GET https://psgc.gitlab.io/api/cities-municipalities/{cityCode}/barangays/
    ```

Response format:

```json
[
    {
        "code": "010000000",
        "name": "ILOCOS NORTE"
    }
]
```

---

## Troubleshooting

### Issue: Dropdowns are empty

- **Cause**: API not reachable
- **Solution**: Check internet connection. API is public and free, no authentication needed.

### Issue: Selected values not loading in edit form

- **Solution**: Ensure the `province_code`, `city_code`, `barangay_code` are passed from the backend with the user data.

### Issue: Address not being sent to backend

- **Solution**: In the form, all six fields (codes + names) must be in the `form` object. The @change handler should update all of them.

---

## Testing the Component

### Manual Test Case

1. Navigate to Create User form
2. Select a Province (should load)
3. Select a City (should show loading spinner, then load cities)
4. Select a Barangay (should show loading spinner, then load barangays)
5. Verify the full address is displayed
6. Submit the form
7. Edit the user - verify previous selections are loaded

---

## Complete Flow Diagram

```
Create/Edit Form
        ↓
User selects Province
        ↓
Component fetches cities for that province
        ↓
User selects City
        ↓
Component fetches barangays for that city
        ↓
User selects Barangay
        ↓
@change event emits all data
        ↓
Parent form updates (province_code, province_name, city_code, city_name, barangay_code, barangay_name)
        ↓
Auto-generate full address string
        ↓
User can add optional street address
        ↓
Form submitted with all data
        ↓
Controller stores/updates database
```

---

## Performance Notes

- First load fetches ~81 provinces (cached in component)
- Subsequent province selections fetch cities (~20-100 per province)
- Subsequent city selections fetch barangays (~10-100 per city)
- All requests use browser's native fetch (no jQuery or axios needed)
- Loading animations show during fetch operations

---

## Next Steps for Your System

1. ✅ Run migration: `php artisan migrate`
2. ✅ Test Create form with address component
3. ✅ Test Edit form with pre-filled address
4. ✅ Update any reports/displays showing addresses
5. ✅ (Optional) Add address filtering/search in listings

---

## Support & Reference

- **PSGC API Docs**: https://psgc.gitlab.io/api/
- **Vue 3 Docs**: https://vuejs.org/
- **Your Forms**: `/resources/js/Pages/Accounts/Create.vue` and `Edit.vue`
