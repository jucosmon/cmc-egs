<script setup>
import PsgcAddress from "@/Components/PsgcAddress.vue";
import { reactive, ref } from "vue";

// Example 1: Basic form with all new address fields
const basicForm = reactive({
    name: "",
    email: "",
    province_code: "",
    province_name: "",
    city_code: "",
    city_name: "",
    barangay_code: "",
    barangay_name: "",
    address: "", // Street address
});

// Example 2: Standalone address selector (for modal/dialog)
const selectedAddress = ref({
    province_code: "",
    province_name: "",
    city_code: "",
    city_name: "",
    barangay_code: "",
    barangay_name: "",
});

// Example 3: Multiple addresses (for cases like shipping + billing)
const multipleAddresses = reactive({
    permanent_address: {
        province_code: "",
        province_name: "",
        city_code: "",
        city_name: "",
        barangay_code: "",
        barangay_name: "",
    },
    current_address: {
        province_code: "",
        province_name: "",
        city_code: "",
        city_name: "",
        barangay_code: "",
        barangay_name: "",
    },
});

const activeTab = ref("basic");
const validationErrors = ref({});

const submitBasicForm = async () => {
    validationErrors.value = {};

    // Simple validation
    if (!basicForm.name) validationErrors.value.name = "Name is required";
    if (!basicForm.email) validationErrors.value.email = "Email is required";
    if (!basicForm.province_code)
        validationErrors.value.province = "Province is required";
    if (!basicForm.city_code) validationErrors.value.city = "City is required";
    if (!basicForm.barangay_code)
        validationErrors.value.barangay = "Barangay is required";

    if (Object.keys(validationErrors.value).length === 0) {
        // Here you would send data to your API
        console.log("Form data to submit:", basicForm);
        alert("Form submitted! Check console for data.");
    }
};

const getFullAddress = (addressData) => {
    const parts = [
        addressData.barangay_name,
        addressData.city_name,
        addressData.province_name,
    ].filter(Boolean);
    return parts.join(", ") || "No address selected";
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 py-12">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-8 text-4xl font-bold text-gray-900">
                PSGC Address Component - Usage Examples
            </h1>

            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-gray-200">
                <div class="flex space-x-8">
                    <button
                        @click="activeTab = 'basic'"
                        :class="[
                            'border-b-2 px-1 py-4 font-medium',
                            activeTab === 'basic'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                        ]"
                    >
                        Basic Form
                    </button>
                    <button
                        @click="activeTab = 'standalone'"
                        :class="[
                            'border-b-2 px-1 py-4 font-medium',
                            activeTab === 'standalone'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                        ]"
                    >
                        Standalone Selector
                    </button>
                    <button
                        @click="activeTab = 'multiple'"
                        :class="[
                            'border-b-2 px-1 py-4 font-medium',
                            activeTab === 'multiple'
                                ? 'border-indigo-500 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
                        ]"
                    >
                        Multiple Addresses
                    </button>
                </div>
            </div>

            <!-- EXAMPLE 1: Basic Form -->
            <div v-show="activeTab === 'basic'" class="space-y-6">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-6 text-2xl font-bold text-gray-900">
                        Example 1: User Registration Form
                    </h2>

                    <form @submit.prevent="submitBasicForm" class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label
                                for="name"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Full Name *
                            </label>
                            <input
                                id="name"
                                v-model="basicForm.name"
                                type="text"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <p
                                v-if="validationErrors.name"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ validationErrors.name }}
                            </p>
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                for="email"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Email *
                            </label>
                            <input
                                id="email"
                                v-model="basicForm.email"
                                type="email"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <p
                                v-if="validationErrors.email"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ validationErrors.email }}
                            </p>
                        </div>

                        <!-- PSGC Address Component -->
                        <div
                            class="rounded-md border border-gray-200 bg-blue-50 p-4"
                        >
                            <PsgcAddress
                                v-model="basicForm"
                                label="Residential Address"
                                :required="true"
                                :error="
                                    validationErrors.province ||
                                    validationErrors.city ||
                                    validationErrors.barangay
                                "
                                @change="
                                    (data) => {
                                        basicForm.province_code =
                                            data.province_code;
                                        basicForm.province_name =
                                            data.province_name;
                                        basicForm.city_code = data.city_code;
                                        basicForm.city_name = data.city_name;
                                        basicForm.barangay_code =
                                            data.barangay_code;
                                        basicForm.barangay_name =
                                            data.barangay_name;
                                    }
                                "
                            />
                        </div>

                        <!-- Street/House Address (Optional) -->
                        <div>
                            <label
                                for="street-address"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Street Address / House # / Building (Optional)
                            </label>
                            <textarea
                                id="street-address"
                                v-model="basicForm.address"
                                rows="2"
                                placeholder="e.g., Lot 5, Block 10, Sunshine Avenue"
                                class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Form Display -->
                        <div
                            v-if="basicForm.province_code"
                            class="rounded-md bg-green-50 p-4"
                        >
                            <p class="text-sm font-medium text-green-800">
                                Current Selection:
                            </p>
                            <p class="mt-2 text-sm text-green-700">
                                <strong>Barangay:</strong>
                                {{ basicForm.barangay_name }}
                            </p>
                            <p class="text-sm text-green-700">
                                <strong>City:</strong> {{ basicForm.city_name }}
                            </p>
                            <p class="text-sm text-green-700">
                                <strong>Province:</strong>
                                {{ basicForm.province_name }}
                            </p>
                            <p
                                v-if="basicForm.address"
                                class="text-sm text-green-700"
                            >
                                <strong>Street Address:</strong>
                                {{ basicForm.address }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex space-x-4">
                            <button
                                type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                Submit Registration
                            </button>
                            <button
                                type="button"
                                @click="
                                    () => {
                                        basicForm = {
                                            name: '',
                                            email: '',
                                            province_code: '',
                                            province_name: '',
                                            city_code: '',
                                            city_name: '',
                                            barangay_code: '',
                                            barangay_name: '',
                                            address: '',
                                        };
                                        validationErrors = {};
                                    }
                                "
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- EXAMPLE 2: Standalone Address Selector -->
            <div v-show="activeTab === 'standalone'" class="space-y-6">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-6 text-2xl font-bold text-gray-900">
                        Example 2: Address Selector (For Modal/Dialog)
                    </h2>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Left: Address Component -->
                        <div>
                            <h3 class="mb-4 font-semibold text-gray-900">
                                Select Address:
                            </h3>
                            <PsgcAddress
                                :model-value="selectedAddress"
                                label="Choose Your Location"
                                @change="
                                    (data) => {
                                        selectedAddress.province_code =
                                            data.province_code;
                                        selectedAddress.province_name =
                                            data.province_name;
                                        selectedAddress.city_code =
                                            data.city_code;
                                        selectedAddress.city_name =
                                            data.city_name;
                                        selectedAddress.barangay_code =
                                            data.barangay_code;
                                        selectedAddress.barangay_name =
                                            data.barangay_name;
                                    }
                                "
                            />
                        </div>

                        <!-- Right: Display Selected Address -->
                        <div>
                            <h3 class="mb-4 font-semibold text-gray-900">
                                Selected Address:
                            </h3>
                            <div
                                v-if="selectedAddress.province_code"
                                class="space-y-4 rounded-md border border-gray-200 bg-gray-50 p-4"
                            >
                                <div>
                                    <p
                                        class="text-xs font-medium uppercase text-gray-600"
                                    >
                                        Province
                                    </p>
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ selectedAddress.province_name }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium uppercase text-gray-600"
                                    >
                                        City/Municipality
                                    </p>
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ selectedAddress.city_name }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-medium uppercase text-gray-600"
                                    >
                                        Barangay
                                    </p>
                                    <p
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ selectedAddress.barangay_name }}
                                    </p>
                                </div>
                                <div class="border-t border-gray-300 pt-4">
                                    <p
                                        class="text-xs font-medium uppercase text-gray-600"
                                    >
                                        Full Address
                                    </p>
                                    <p
                                        class="mt-2 text-base font-semibold text-indigo-600"
                                    >
                                        {{ getFullAddress(selectedAddress) }}
                                    </p>
                                </div>
                                <button
                                    @click="
                                        () => {
                                            selectedAddress = {
                                                province_code: '',
                                                province_name: '',
                                                city_code: '',
                                                city_name: '',
                                                barangay_code: '',
                                                barangay_name: '',
                                            };
                                        }
                                    "
                                    class="w-full rounded-md bg-red-100 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-200"
                                >
                                    Clear Selection
                                </button>
                            </div>
                            <div
                                v-else
                                class="rounded-md border-2 border-dashed border-gray-300 p-4 text-center"
                            >
                                <p class="text-gray-500">
                                    Select a barangay to display address
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EXAMPLE 3: Multiple Addresses -->
            <div v-show="activeTab === 'multiple'" class="space-y-6">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h2 class="mb-6 text-2xl font-bold text-gray-900">
                        Example 3: Multiple Address Fields
                    </h2>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Permanent Address -->
                        <div
                            class="rounded-md border border-gray-200 bg-amber-50 p-4"
                        >
                            <h3 class="mb-4 font-semibold text-amber-900">
                                Permanent Address
                            </h3>
                            <PsgcAddress
                                :model-value="
                                    multipleAddresses.permanent_address
                                "
                                label="Permanent Address"
                                @change="
                                    (data) => {
                                        multipleAddresses.permanent_address =
                                            data;
                                    }
                                "
                            />
                            <div
                                v-if="
                                    multipleAddresses.permanent_address
                                        .province_code
                                "
                                class="mt-4 rounded-md bg-white p-3"
                            >
                                <p class="text-xs text-gray-600">Selected:</p>
                                <p class="font-medium text-amber-900">
                                    {{
                                        getFullAddress(
                                            multipleAddresses.permanent_address,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <!-- Current Address -->
                        <div
                            class="rounded-md border border-gray-200 bg-blue-50 p-4"
                        >
                            <h3 class="mb-4 font-semibold text-blue-900">
                                Current Address
                            </h3>
                            <PsgcAddress
                                :model-value="multipleAddresses.current_address"
                                label="Current Address"
                                @change="
                                    (data) => {
                                        multipleAddresses.current_address =
                                            data;
                                    }
                                "
                            />
                            <div
                                v-if="
                                    multipleAddresses.current_address
                                        .province_code
                                "
                                class="mt-4 rounded-md bg-white p-3"
                            >
                                <p class="text-xs text-gray-600">Selected:</p>
                                <p class="font-medium text-blue-900">
                                    {{
                                        getFullAddress(
                                            multipleAddresses.current_address,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div
                        v-if="
                            multipleAddresses.permanent_address.province_code ||
                            multipleAddresses.current_address.province_code
                        "
                        class="mt-6 rounded-md border border-green-200 bg-green-50 p-4"
                    >
                        <h3 class="mb-4 font-semibold text-green-900">
                            Summary
                        </h3>
                        <dl class="space-y-3">
                            <div
                                v-if="
                                    multipleAddresses.permanent_address
                                        .province_code
                                "
                            >
                                <dt class="text-sm font-medium text-green-900">
                                    Permanent Address:
                                </dt>
                                <dd class="text-sm text-green-800">
                                    {{
                                        getFullAddress(
                                            multipleAddresses.permanent_address,
                                        )
                                    }}
                                </dd>
                            </div>
                            <div
                                v-if="
                                    multipleAddresses.current_address
                                        .province_code
                                "
                            >
                                <dt class="text-sm font-medium text-green-900">
                                    Current Address:
                                </dt>
                                <dd class="text-sm text-green-800">
                                    {{
                                        getFullAddress(
                                            multipleAddresses.current_address,
                                        )
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Code Reference -->
            <div class="mt-12 rounded-lg bg-gray-900 p-6 text-white">
                <h3 class="mb-4 text-lg font-bold">Quick Reference</h3>
                <div class="overflow-x-auto">
                    <pre class="text-sm"><code>&lt;PsgcAddress
  :model-value="addressData"
  label="Address"
  :required="true"
  :error="errors.province"
  @change="(data) => {
    form.province_code = data.province_code;
    form.province_name = data.province_name;
    form.city_code = data.city_code;
    form.city_name = data.city_name;
    form.barangay_code = data.barangay_code;
    form.barangay_name = data.barangay_name;
  }"
/></code></pre>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Utility classes */
.btn {
    @apply rounded-md px-4 py-2 text-sm font-medium transition-colors;
}

.btn-primary {
    @apply bg-indigo-600 text-white hover:bg-indigo-700;
}
</style>
