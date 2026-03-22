<script setup>
import PsgcAddress from "@/Components/PsgcAddress.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const page = usePage();
const currentUserRole = computed(() => page.props.auth?.user?.role ?? null);
const flash = computed(() => page.props.flash || {});

const props = defineProps({
    userType: {
        type: String,
        required: true,
    },
    departments: {
        type: Object,
        default: () => ({}),
    },
    programs: {
        type: Object,
        default: () => ({}),
    },
    blocks: {
        type: Array,
        default: () => [],
    },
    student_statuses: {
        type: Array,
        default: () => [],
    },
    year_levels: {
        type: Array,
        default: () => [],
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    email: "",
    personal_email: "",
    first_name: "",
    middle_name: "",
    last_name: "",
    official_id: "",
    phone: "",
    address: "",
    province_code: "",
    province_name: "",
    city_code: "",
    city_name: "",
    barangay_code: "",
    barangay_name: "",
    date_of_birth: "",
    sex: "",
    department_id: "",
    program_id: "",
    year_level: "",
    status: "",
    block_id: "",
    type: props.userType,
});

// Show programs only for student creation
const showPrograms = computed(() => props.userType === "student");
const showStudentFields = computed(() => props.userType === "student");

// Show departments only for IT Admin or when relevant
const showDepartments = computed(
    () =>
        ["it_admin", "dean"].includes(currentUserRole.value) &&
        ["student", "instructor", "program_head"].includes(props.userType),
);

const programBlocks = computed(() => {
    if (!form.program_id) return [];
    return props.blocks.filter(
        (block) => String(block.program_id) === String(form.program_id),
    );
});

const latestAdmissionYear = computed(() => {
    if (!programBlocks.value.length) return null;
    return Math.max(...programBlocks.value.map((b) => b.admission_year || 0));
});

const availableBlocks = computed(() => {
    if (!latestAdmissionYear.value) return [];
    return programBlocks.value.filter(
        (block) => block.admission_year === latestAdmissionYear.value,
    );
});

const blockCapacity = (block) => Number(block.max_students ?? 50);
const blockOccupancy = (block) => Number(block.students_count ?? 0);
const isBlockFull = (block) => blockOccupancy(block) >= blockCapacity(block);

const hasOpenBlock = computed(() =>
    availableBlocks.value.some((block) => !isBlockFull(block)),
);

const namePattern = "^[A-Za-z]+(?:[\\s'-][A-Za-z]+)*$";
const phonePattern = "^9\\d{9}$";
const maxBirthDate = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - 18);
    return date.toISOString().split("T")[0];
});

const normalizePhoneInput = (value) => {
    let digits = String(value || "").replace(/\D/g, "");

    if (digits.startsWith("639")) {
        digits = digits.slice(2);
    } else if (digits.startsWith("09")) {
        digits = digits.slice(1);
    }

    if (digits && !digits.startsWith("9")) {
        const firstNine = digits.indexOf("9");
        digits = firstNine >= 0 ? digits.slice(firstNine) : "";
    }

    return digits.slice(0, 10);
};

const phoneLocal = ref("");

const onPhoneInput = (event) => {
    phoneLocal.value = normalizePhoneInput(event.target.value);
};

watch(
    () => form.program_id,
    () => {
        const firstOpenBlock = availableBlocks.value.find(
            (block) => !isBlockFull(block),
        );

        if (firstOpenBlock) {
            form.block_id = String(firstOpenBlock.id);
        } else {
            form.block_id = "";
        }
    },
);

const submit = () => {
    form.phone = phoneLocal.value ? `+63${phoneLocal.value}` : "";

    const addressParts = [
        form.barangay_name,
        form.city_name,
        form.province_name,
    ].filter(Boolean);
    if (addressParts.length > 0) {
        form.address = addressParts.join(", ");
    }

    form.post(route("accounts.store"), {
        onSuccess: () => {
            form.reset();
        },
    });
};

const applyPsgcAddress = (data) => {
    form.province_code = data.province_code;
    form.province_name = data.province_name;
    form.city_code = data.city_code;
    form.city_name = data.city_name;
    form.barangay_code = data.barangay_code;
    form.barangay_name = data.barangay_name;

    const parts = [
        data.barangay_name,
        data.city_name,
        data.province_name,
    ].filter(Boolean);
    form.address = parts.join(", ");
};

const getRoleLabel = (role) => {
    const labels = {
        dean: "Dean",
        program_head: "Program Head",
        registrar: "Registrar",
        instructor: "Instructor",
        student: "Student",
    };
    return labels[role] || role;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Create ${getRoleLabel(userType)} Account`" />

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <!-- Back Button -->
                <div class="mb-6">
                    <Link
                        :href="route('accounts.index', { type: userType })"
                        class="inline-flex items-center text-indigo-600 hover:text-indigo-900"
                    >
                        <svg
                            class="mr-2 h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                        Back to {{ getRoleLabel(userType) }}s
                    </Link>
                </div>

                <!-- Alerts -->
                <div
                    v-if="flash.success"
                    class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800"
                >
                    {{ flash.success }}
                </div>
                <div
                    v-if="flash.info"
                    class="mb-4 rounded-md bg-blue-50 p-4 text-sm text-blue-800"
                >
                    {{ flash.info }}
                </div>
                <div
                    v-if="flash.warning"
                    class="mb-4 rounded-md bg-yellow-50 p-4 text-sm text-yellow-800"
                >
                    {{ flash.warning }}
                </div>
                <div
                    v-if="flash.error"
                    class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800"
                >
                    {{ flash.error }}
                </div>

                <!-- Form Card -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Create {{ getRoleLabel(userType) }} Account
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Fill in the details below to create a new
                            {{ getRoleLabel(userType) }}
                            account
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 px-6 py-4">
                        <!-- Error Messages -->
                        <div
                            v-if="
                                form.errors &&
                                Object.keys(form.errors).length > 0
                            "
                            class="rounded-md bg-red-50 p-4"
                        >
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg
                                        class="h-5 w-5 text-red-400"
                                        viewBox="0 0 20 20"
                                        fill="currentColor"
                                    >
                                        <path
                                            fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3
                                        class="text-sm font-medium text-red-800"
                                    >
                                        Please fix the following errors:
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul
                                            class="list-inside list-disc space-y-1"
                                        >
                                            <li
                                                v-for="(
                                                    error, field
                                                ) in form.errors"
                                                :key="field"
                                                class="capitalize"
                                            >
                                                {{ field }}: {{ error }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="rounded-md bg-blue-50 p-4">
                            <p class="text-sm text-blue-700">
                                <strong>Note:</strong> Official email will be
                                automatically generated based on the first and
                                last name (e.g., john.doe@cmc.edu.ph)
                            </p>
                        </div>

                        <!-- Personal Email -->
                        <div>
                            <label
                                for="personal_email"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Personal Email *
                            </label>
                            <input
                                id="personal_email"
                                v-model="form.personal_email"
                                type="email"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- First Name -->
                        <div>
                            <label
                                for="first_name"
                                class="block text-sm font-medium text-gray-700"
                            >
                                First Name *
                            </label>
                            <input
                                id="first_name"
                                v-model="form.first_name"
                                type="text"
                                required
                                :pattern="namePattern"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Middle Name -->
                        <div>
                            <label
                                for="middle_name"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Middle Name
                            </label>
                            <input
                                id="middle_name"
                                v-model="form.middle_name"
                                type="text"
                                :pattern="namePattern"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Last Name -->
                        <div>
                            <label
                                for="last_name"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Last Name *
                            </label>
                            <input
                                id="last_name"
                                v-model="form.last_name"
                                type="text"
                                required
                                :pattern="namePattern"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Official ID -->
                        <div class="rounded-md bg-blue-50 p-4">
                            <p class="text-sm text-blue-700">
                                <strong>Note:</strong> Official ID will be
                                automatically generated (e.g., DEAN0001,
                                STU0123)
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label
                                for="phone"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Phone *
                            </label>
                            <div
                                class="mt-1 flex items-center overflow-hidden rounded-md border border-gray-300 shadow-sm focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500"
                            >
                                <span
                                    class="bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700"
                                >
                                    +63
                                </span>
                                <input
                                    id="phone"
                                    :value="phoneLocal"
                                    @input="onPhoneInput"
                                    type="text"
                                    inputmode="numeric"
                                    required
                                    maxlength="10"
                                    minlength="10"
                                    :pattern="phonePattern"
                                    placeholder="9XXXXXXXXX"
                                    class="block w-full border-0 px-3 py-2 focus:ring-0"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Enter 10 digits starting with 9 (example:
                                9123456789).
                            </p>
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label
                                for="date_of_birth"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Date of Birth *
                            </label>
                            <input
                                id="date_of_birth"
                                v-model="form.date_of_birth"
                                type="date"
                                required
                                :max="maxBirthDate"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Sex -->
                        <div>
                            <label
                                for="sex"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Sex *
                            </label>
                            <select
                                id="sex"
                                v-model="form.sex"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <!-- Address with PSGC Cascading Dropdowns -->
                        <PsgcAddress
                            :model-value="{
                                province_code: form.province_code,
                                province_name: form.province_name,
                                city_code: form.city_code,
                                city_name: form.city_name,
                                barangay_code: form.barangay_code,
                                barangay_name: form.barangay_name,
                            }"
                            :label="'Address'"
                            :required="false"
                            :error="
                                form.errors.province_code ||
                                form.errors.city_code ||
                                form.errors.barangay_code
                            "
                            @change="applyPsgcAddress"
                        />

                        <!-- Generated full address -->
                        <div>
                            <label
                                for="address"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Generated Address
                            </label>
                            <input
                                id="address"
                                v-model="form.address"
                                type="text"
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Department (if applicable) -->
                        <div
                            v-if="
                                showDepartments &&
                                Object.keys(departments).length > 0
                            "
                        >
                            <label
                                for="department_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Department
                            </label>
                            <select
                                id="department_id"
                                v-model="form.department_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select a department...</option>
                                <option
                                    v-for="(name, id) in departments"
                                    :key="id"
                                    :value="id"
                                >
                                    {{ name }}
                                </option>
                            </select>
                        </div>

                        <!-- Program (if applicable) -->
                        <div
                            v-if="
                                showPrograms && Object.keys(programs).length > 0
                            "
                        >
                            <label
                                for="program_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Program
                            </label>
                            <select
                                id="program_id"
                                v-model="form.program_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select a program...</option>
                                <option
                                    v-for="(name, id) in programs"
                                    :key="id"
                                    :value="id"
                                >
                                    {{ name }}
                                </option>
                            </select>
                        </div>

                        <!-- Student Fields -->
                        <div v-if="showStudentFields" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Year Level *
                                </label>
                                <select
                                    v-model="form.year_level"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">
                                        Select year level...
                                    </option>
                                    <option
                                        v-for="yl in props.year_levels"
                                        :key="yl"
                                        :value="yl"
                                    >
                                        {{ yl }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Status *
                                </label>
                                <select
                                    v-model="form.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select status...</option>
                                    <option
                                        v-for="st in props.student_statuses"
                                        :key="st"
                                        :value="st"
                                    >
                                        {{ st }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Block *
                                </label>
                                <select
                                    v-model="form.block_id"
                                    :disabled="!hasOpenBlock"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select block...</option>
                                    <option
                                        v-for="block in availableBlocks"
                                        :key="block.id"
                                        :value="block.id"
                                        :disabled="isBlockFull(block)"
                                    >
                                        {{
                                            `${block.code} (${block.admission_year}) - ${blockOccupancy(block)}/${blockCapacity(block)}${isBlockFull(block) ? " (Full)" : ""}`
                                        }}
                                    </option>
                                </select>
                                <p
                                    v-if="
                                        form.program_id &&
                                        !availableBlocks.length
                                    "
                                    class="mt-1 text-sm text-gray-500"
                                >
                                    No active blocks found for the latest
                                    admission year.
                                </p>
                                <p
                                    v-else-if="form.program_id && !hasOpenBlock"
                                    class="mt-1 text-sm text-amber-600"
                                >
                                    All latest-year blocks are full. Please
                                    create another block or increase block
                                    capacity.
                                </p>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div
                            class="flex justify-end space-x-4 border-t border-gray-200 pt-6"
                        >
                            <Link
                                :href="
                                    route('accounts.index', { type: userType })
                                "
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                            >
                                {{
                                    form.processing
                                        ? "Creating..."
                                        : "Create Account"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
