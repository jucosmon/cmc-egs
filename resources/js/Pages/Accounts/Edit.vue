<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

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
        type: Object,
        default: () => ({}),
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
    first_name: props.account.first_name || "",
    middle_name: props.account.middle_name || "",
    last_name: props.account.last_name || "",
    official_id: props.account.official_id || "",
    phone: props.account.phone || "",
    address: props.account.address || "",
    // date_of_birth should be a YYYY-MM-DD string for <input type="date">
    date_of_birth: props.account.date_of_birth
        ? String(props.account.date_of_birth).slice(0, 10)
        : "",
    sex: (() => {
        const s = props.account.sex
            ? String(props.account.sex).toLowerCase()
            : "";
        if (s === "male") return "Male";
        if (s === "female") return "Female";
        return "";
    })(),
    // Prefill department/program from related models when available
    department_id: (() => {
        // Prefer explicit program/department props passed from controller
        if (props.program && props.program.department_id) {
            return String(props.program.department_id);
        }
        if (props.department && props.department.id) {
            return String(props.department.id);
        }
        // Then fall back to account nested relations
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
        // Prefer explicit program prop
        if (props.program && props.program.id) {
            return String(props.program.id);
        }
        // Then fall back to account nested relations
        if (props.account.programAsHead && props.account.programAsHead.id) {
            return String(props.account.programAsHead.id);
        }
        if (props.account.student && props.account.student.program_id) {
            return String(props.account.student.program_id);
        }
        return "";
    })(),
    type: props.userType,
});

// Show programs for student editing and for assigning a Program Head
const showPrograms = computed(() =>
    ["student", "program_head"].includes(props.userType),
);

// Show departments for relevant account types (student, instructor, program_head)
// and when the current user has permission to edit
const showDepartments = computed(
    () =>
        ["student", "instructor", "program_head"].includes(props.userType) &&
        ["it_admin", "dean"].includes(currentUserRole.value),
);

const submit = () => {
    form.put(route("accounts.update", { account: props.account.id }), {
        onSuccess: () => {
            form.reset();
        },
    });
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
                <!-- Back Button -->
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

                <!-- Form Card -->
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
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Official ID -->
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

                        <!-- Phone -->
                        <div>
                            <label
                                for="phone"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Phone
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Date of Birth -->
                        <div>
                            <label
                                for="date_of_birth"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Date of Birth
                            </label>
                            <input
                                id="date_of_birth"
                                v-model="form.date_of_birth"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Sex -->
                        <div>
                            <label
                                for="sex"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Sex
                            </label>
                            <select
                                id="sex"
                                v-model="form.sex"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">Select...</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>

                        <!-- Address -->
                        <div>
                            <label
                                for="address"
                                class="block text-sm font-medium text-gray-700"
                            >
                                Address
                            </label>
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                        </div>

                        <!-- Department (editable for allowed roles, otherwise read-only if available) -->
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
                        <!-- Display current department for program head (readonly) -->
                        <div
                            v-else-if="
                                props.userType === 'program_head' &&
                                account.programAsHead &&
                                account.programAsHead.department
                            "
                        >
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Department</label
                            >
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
                                >Department</label
                            >
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
                        <!-- Display current program for program head (readonly) - only when no select available -->
                        <div
                            v-else-if="
                                props.userType === 'program_head' &&
                                Object.keys(programs).length === 0
                            "
                        >
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Program</label
                            >
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

                        <!-- Form Actions -->
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
