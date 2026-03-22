<script setup>
import AvatarUpload from "@/Components/AvatarUpload.vue";
import PsgcAddress from "@/Components/PsgcAddress.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";

const page = usePage();
const currentUserRole = computed(() => page.props.auth?.user?.role ?? null);

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
    userType: {
        type: String,
        required: true,
    },
    departments: {
        type: Object,
        default: () => ({}),
    },
    programs: {
        type: Array,
        default: () => [],
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
    program: {
        type: Object,
        default: null,
    },
    department: {
        type: Object,
        default: null,
    },
});

const form = useForm({
    email: props.account.email || "",
    personal_email: props.account.personal_email || "",
    first_name: props.account.first_name || "",
    middle_name: props.account.middle_name || "",
    last_name: props.account.last_name || "",
    official_id: props.account.official_id || "",
    phone: props.account.phone || "",
    address: props.account.address || "",
    province_code: props.account.province_code || "",
    province_name: props.account.province_name || "",
    city_code: props.account.city_code || "",
    city_name: props.account.city_name || "",
    barangay_code: props.account.barangay_code || "",
    barangay_name: props.account.barangay_name || "",
    date_of_birth: props.account.date_of_birth
        ? String(props.account.date_of_birth).slice(0, 10)
        : "",
    sex: (() => {
        const value = props.account.sex
            ? String(props.account.sex).toLowerCase()
            : "";
        if (value === "male") return "Male";
        if (value === "female") return "Female";
        return "";
    })(),
    department_id: (() => {
        if (props.program && props.program.department_id) {
            return String(props.program.department_id);
        }
        if (props.department && props.department.id) {
            return String(props.department.id);
        }
        if (
            props.account.programAsHead &&
            props.account.programAsHead.department_id
        ) {
            return String(props.account.programAsHead.department_id);
        }
        if (
            props.account.instructor &&
            props.account.instructor.department_id
        ) {
            return String(props.account.instructor.department_id);
        }
        if (
            props.account.student &&
            props.account.student.program &&
            props.account.student.program.department_id
        ) {
            return String(props.account.student.program.department_id);
        }
        return "";
    })(),
    program_id: (() => {
        if (props.program && props.program.id) {
            return String(props.program.id);
        }
        if (props.account.programAsHead && props.account.programAsHead.id) {
            return String(props.account.programAsHead.id);
        }
        if (props.account.student && props.account.student.program_id) {
            return String(props.account.student.program_id);
        }
        return "";
    })(),
    year_level:
        props.account.student && props.account.student.year_level
            ? props.account.student.year_level
            : "",
    status:
        props.account.student && props.account.student.status
            ? props.account.student.status
            : "",
    block_id:
        props.account.student && props.account.student.block_id
            ? String(props.account.student.block_id)
            : "",
    avatar: null,
    type: props.userType,
});

const editingRole = computed(() => props.userType || props.account.role);

const showPrograms = computed(() =>
    ["student", "program_head"].includes(editingRole.value),
);

const showDepartments = computed(
    () =>
        ["student", "instructor", "program_head"].includes(editingRole.value) &&
        ["it_admin", "dean", "registrar"].includes(currentUserRole.value),
);

const programOptions = computed(() => props.programs || []);

const filteredPrograms = computed(() => {
    if (!form.department_id) return programOptions.value;
    if (!programOptions.value.length) return [];
    const hasDepartment = programOptions.value.some(
        (program) => program.department_id,
    );
    if (!hasDepartment) return programOptions.value;
    return programOptions.value.filter(
        (program) =>
            String(program.department_id) === String(form.department_id),
    );
});

const programBlocks = computed(() => {
    if (!form.program_id) return [];
    return (props.blocks || []).filter(
        (block) => String(block.program_id) === String(form.program_id),
    );
});

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

const phoneLocal = ref(normalizePhoneInput(props.account.phone || ""));

const onPhoneInput = (event) => {
    phoneLocal.value = normalizePhoneInput(event.target.value);
};

const blockCapacity = (block) => Number(block.max_students ?? 50);
const blockOccupancy = (block) => Number(block.students_count ?? 0);
const isBlockFull = (block) => blockOccupancy(block) >= blockCapacity(block);
const isCurrentBlock = (block) => String(block.id) === String(form.block_id);
const canSelectBlock = (block) => !isBlockFull(block) || isCurrentBlock(block);

const hasSelectableBlock = computed(() =>
    programBlocks.value.some((block) => canSelectBlock(block)),
);

watch(
    () => form.department_id,
    () => {
        if (!filteredPrograms.value.length) {
            form.program_id = "";
            form.block_id = "";
            return;
        }
        if (
            form.program_id &&
            !filteredPrograms.value.find(
                (program) => String(program.id) === String(form.program_id),
            )
        ) {
            form.program_id = String(filteredPrograms.value[0].id);
            form.block_id = "";
        }
    },
);

watch(
    () => form.program_id,
    () => {
        if (!programBlocks.value.length) {
            form.block_id = "";
        } else if (
            form.block_id &&
            !programBlocks.value.find(
                (block) =>
                    String(block.id) === String(form.block_id) &&
                    canSelectBlock(block),
            )
        ) {
            const firstSelectableBlock = programBlocks.value.find((block) =>
                canSelectBlock(block),
            );
            form.block_id = firstSelectableBlock
                ? String(firstSelectableBlock.id)
                : "";
        } else if (!form.block_id && programBlocks.value.length) {
            const firstSelectableBlock = programBlocks.value.find((block) =>
                canSelectBlock(block),
            );
            form.block_id = firstSelectableBlock
                ? String(firstSelectableBlock.id)
                : "";
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

    const targetRoute = route("accounts.update", { account: props.account.id });

    if (form.avatar) {
        form.transform((data) => ({
            ...data,
            _method: "put",
        })).post(targetRoute, {
            forceFormData: true,
            onSuccess: () => {
                form.reset("avatar");
            },
            onFinish: () => {
                form.transform((data) => data);
            },
        });
        return;
    }

    form.put(targetRoute, {
        onSuccess: () => {
            form.reset("avatar");
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
        <Head :title="`Edit ${account.full_name}`" />

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="mb-6">
                    <Link
                        :href="
                            route('accounts.show', {
                                account: account.id,
                                type: userType,
                            })
                        "
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
                        Back to Account
                    </Link>
                </div>

                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h1 class="text-2xl font-bold text-gray-900">
                            Edit {{ getRoleLabel(userType) }} Account
                        </h1>
                        <p class="mt-2 text-sm text-gray-600">
                            Update account information for
                            {{ account.full_name }}
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6 px-6 py-4">
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

                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Profile Picture
                            </label>
                            <div class="mt-2">
                                <AvatarUpload
                                    v-model="form.avatar"
                                    :current-avatar-url="account.avatar_url"
                                    :error="form.errors.avatar"
                                    label="Upload account photo"
                                />
                            </div>
                        </div>

                        <div>
                            <label
                                for="email"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Email *
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                disabled
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 px-3 py-2 text-gray-600"
                            />
                        </div>

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

                        <div>
                            <label
                                for="official_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Official ID
                            </label>
                            <input
                                id="official_id"
                                v-model="form.official_id"
                                type="text"
                                disabled
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 px-3 py-2 text-gray-600"
                            />
                        </div>

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

                        <div>
                            <label
                                for="address"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Address
                            </label>
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
                            :label="'Address Details'"
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
                        <div
                            v-else-if="
                                props.userType === 'program_head' &&
                                account.programAsHead &&
                                account.programAsHead.department
                            "
                        >
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Department
                            </label>
                            <input
                                type="text"
                                :value="
                                    (props.department &&
                                        props.department.name) ||
                                    (account.programAsHead &&
                                        account.programAsHead.department &&
                                        account.programAsHead.department
                                            .name) ||
                                    ''
                                "
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 px-3 py-2"
                            />
                        </div>
                        <div
                            v-else-if="
                                (account.instructor &&
                                    account.instructor.department) ||
                                (account.student &&
                                    account.student.program &&
                                    account.student.program.department) ||
                                (account.programAsHead &&
                                    account.programAsHead.department)
                            "
                        >
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Department
                            </label>
                            <input
                                type="text"
                                :value="
                                    (props.department &&
                                        props.department.name) ||
                                    (account.instructor &&
                                        account.instructor.department &&
                                        account.instructor.department.name) ||
                                    (account.student &&
                                        account.student.program &&
                                        account.student.program.department &&
                                        account.student.program.department
                                            .name) ||
                                    (account.programAsHead &&
                                        account.programAsHead.department &&
                                        account.programAsHead.department
                                            .name) ||
                                    ''
                                "
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 px-3 py-2"
                            />
                        </div>

                        <div v-if="showPrograms && filteredPrograms.length">
                            <label
                                for="program_id"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Program
                            </label>
                            <select
                                id="program_id"
                                v-model="form.program_id"
                                :disabled="
                                    showDepartments && !form.department_id
                                "
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select a program...</option>
                                <option
                                    v-for="program in filteredPrograms"
                                    :key="program.id"
                                    :value="program.id"
                                >
                                    {{ program.name }}
                                </option>
                            </select>
                            <p
                                v-if="showDepartments && !form.department_id"
                                class="mt-1 text-sm text-gray-500"
                            >
                                Select a department first to load programs.
                            </p>
                            <p
                                v-if="
                                    form.department_id &&
                                    !filteredPrograms.length
                                "
                                class="mt-1 text-sm text-gray-500"
                            >
                                No programs found for the selected department.
                            </p>
                        </div>
                        <div
                            v-else-if="
                                props.userType === 'program_head' &&
                                filteredPrograms.length === 0
                            "
                        >
                            <label
                                class="block text-sm font-medium text-gray-700"
                            >
                                Program
                            </label>
                            <input
                                type="text"
                                :value="
                                    (props.program && props.program.name) ||
                                    (account.programAsHead &&
                                        account.programAsHead.name) ||
                                    'Not assigned'
                                "
                                readonly
                                class="mt-1 block w-full rounded-md border-gray-200 bg-gray-50 px-3 py-2"
                            />
                        </div>

                        <div
                            v-if="account.role === 'student'"
                            class="space-y-4"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Year Level
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
                                    Status
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
                                    Block
                                </label>
                                <select
                                    v-model="form.block_id"
                                    :disabled="!hasSelectableBlock"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">Select block...</option>
                                    <option
                                        v-for="block in programBlocks"
                                        :key="block.id"
                                        :value="block.id"
                                        :disabled="
                                            isBlockFull(block) &&
                                            !isCurrentBlock(block)
                                        "
                                    >
                                        {{
                                            `${block.code} (${block.admission_year}) - ${blockOccupancy(block)}/${blockCapacity(block)}${isBlockFull(block) ? " (Full)" : ""}`
                                        }}
                                    </option>
                                </select>
                                <p
                                    v-if="
                                        form.program_id && !programBlocks.length
                                    "
                                    class="mt-1 text-sm text-gray-500"
                                >
                                    No blocks found for the selected program.
                                </p>
                                <p
                                    v-else-if="!form.program_id"
                                    class="mt-1 text-sm text-gray-500"
                                >
                                    Select a program first to load blocks.
                                </p>
                                <p
                                    v-else-if="
                                        form.program_id && !hasSelectableBlock
                                    "
                                    class="mt-1 text-sm text-amber-600"
                                >
                                    All blocks in this program are full.
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex justify-end space-x-4 border-t border-gray-200 pt-6"
                        >
                            <Link
                                :href="
                                    route('accounts.show', {
                                        account: account.id,
                                        type: userType,
                                    })
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
                                        ? "Saving..."
                                        : "Save Changes"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
