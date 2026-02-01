<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const currentUserRole = computed(() => page.props.auth?.user?.role ?? null);

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
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    email: "",
    first_name: "",
    middle_name: "",
    last_name: "",
    official_id: "",
    phone: "",
    address: "",
    date_of_birth: "",
    sex: "",
    department_id: "",
    program_id: "",
    type: props.userType,
});

// Show programs only for student creation
const showPrograms = computed(() => props.userType === "student");

// Show departments only for IT Admin or when relevant
const showDepartments = computed(
    () =>
        ["it_admin", "dean"].includes(currentUserRole.value) &&
        ["student", "instructor", "program_head"].includes(props.userType),
);

const submit = () => {
    form.post(route("accounts.store"), {
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
                                <strong>Note:</strong> Email will be
                                automatically generated based on the first and
                                last name (e.g., john.doe@cmc.edu.ph)
                            </p>
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
