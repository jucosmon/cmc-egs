<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    account: {
        type: Object,
        required: true,
    },
    userType: {
        type: String,
        required: true,
    },
    student: {
        type: Object,
        default: null,
    },
    instructor: {
        type: Object,
        default: null,
    },
    program: {
        type: Object,
        default: null,
    },
    department: {
        type: Object,
        default: null,
    },
    canUpdate: {
        type: Boolean,
        default: false,
    },
    canResetPassword: {
        type: Boolean,
        default: false,
    },
});

const showResetPasswordModal = ref(false);

const resetPasswordForm = useForm({
    password: "",
    password_confirmation: "",
    type: props.userType,
});

const handleResetPassword = () => {
    resetPasswordForm.post(
        route("accounts.reset-password", { account: props.account.id }),
        {
            onSuccess: () => {
                showResetPasswordModal.value = false;
                resetPasswordForm.reset();
            },
        },
    );
};

const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
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
        <Head :title="`${account.full_name} - Account Details`" />

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
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

                <!-- Page Header -->
                <div class="mb-8 rounded-lg bg-white px-6 py-8 shadow">
                    <div class="flex items-center">
                        <div class="h-20 w-20 flex-shrink-0">
                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-600"
                            >
                                <span class="text-2xl font-medium text-white">
                                    {{
                                        account &&
                                        account.first_name &&
                                        account.last_name
                                            ? (
                                                  account.first_name.charAt(0) +
                                                  account.last_name.charAt(0)
                                              ).toUpperCase()
                                            : "?"
                                    }}
                                </span>
                            </div>
                        </div>
                        <div class="ml-8">
                            <h1 class="text-3xl font-bold text-gray-900">
                                {{ account.full_name }}
                            </h1>
                            <p class="mt-2 text-gray-600">
                                {{ getRoleLabel(userType) }} Account
                            </p>
                            <div class="mt-4 flex items-center space-x-4">
                                <span
                                    :class="[
                                        'inline-flex rounded-full px-3 py-1 text-xs font-semibold',
                                        account.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800',
                                    ]"
                                >
                                    {{
                                        account.is_active
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div class="mb-8 rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Account Information
                        </h2>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Email
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ account.email }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Official ID
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ account.official_id || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    First Name
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ account.first_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Middle Name
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ account.middle_name || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Last Name
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ account.last_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Phone
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ account.phone || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Date of Birth
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(account.date_of_birth) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Sex
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ account.sex || "N/A" }}
                                </dd>
                            </div>
                            <div v-if="account.role === 'program_head'">
                                <dt class="text-sm font-medium text-gray-500">
                                    Department
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        (account.programAsHead &&
                                            account.programAsHead.department &&
                                            account.programAsHead.department
                                                .name) ||
                                        (account.departmentAsProgramHead &&
                                            account.departmentAsProgramHead
                                                .name) ||
                                        "N/A"
                                    }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">
                                    Address
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ account.address || "N/A" }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Dean Information -->
                <div
                    v-if="account.role === 'dean'"
                    class="mb-8 rounded-lg bg-white shadow"
                >
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Department (as Dean)
                        </h2>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Department
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        (department && department.name) ||
                                        "Not assigned"
                                    }}
                                </dd>
                            </div>
                            <div v-if="department" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">
                                    Programs in Department
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <ul class="list-disc list-inside">
                                        <li
                                            v-for="prog in department.programs"
                                            :key="prog.id"
                                        >
                                            {{ prog.name }}
                                        </li>
                                        <li
                                            v-if="
                                                !department.programs ||
                                                department.programs.length === 0
                                            "
                                        >
                                            No programs
                                        </li>
                                    </ul>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Program Head Information -->
                <div
                    v-if="account.role === 'program_head'"
                    class="mb-8 rounded-lg bg-white shadow"
                >
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Program Head Information
                        </h2>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Program
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        (program && program.name) ||
                                        "Not assigned"
                                    }}
                                </dd>
                            </div>
                            <div v-if="program">
                                <dt class="text-sm font-medium text-gray-500">
                                    Program Department
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        (program.department &&
                                            program.department.name) ||
                                        "N/A"
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Student Information -->
                <div v-if="student" class="mb-8 rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Student Information
                        </h2>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Program
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ student.program?.name || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Enrollment Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(student.created_at) }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Year Level
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ student.year_level || "N/A" }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Status
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ student.status || "N/A" }}
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Block
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ student.block?.name || "N/A" }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Instructor Information -->
                <div v-if="instructor" class="mb-8 rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Instructor Information
                        </h2>
                    </div>
                    <div class="px-6 py-4">
                        <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Department
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ instructor.department?.name || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Hire Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(instructor.created_at) }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4">
                    <Link
                        v-if="canUpdate"
                        :href="
                            route('accounts.edit', {
                                account: account.id,
                                type: userType,
                            })
                        "
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
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
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                        </svg>
                        Update Account
                    </Link>
                    <button
                        v-if="canResetPassword"
                        @click="showResetPasswordModal = true"
                        class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
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
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
                            />
                        </svg>
                        Reset Password
                    </button>
                </div>

                <!-- Reset Password Modal -->
                <div
                    v-if="showResetPasswordModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
                >
                    <div
                        class="w-full max-w-md rounded-lg bg-white p-6 shadow-lg"
                    >
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">
                            Reset Password
                        </h2>
                        <form @submit.prevent="handleResetPassword">
                            <div class="mb-4">
                                <label
                                    for="password"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    New Password
                                </label>
                                <input
                                    id="password"
                                    v-model="resetPasswordForm.password"
                                    type="password"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                                <span
                                    v-if="resetPasswordForm.errors.password"
                                    class="mt-1 text-sm text-red-600"
                                >
                                    {{ resetPasswordForm.errors.password }}
                                </span>
                            </div>

                            <div class="mb-6">
                                <label
                                    for="password_confirmation"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Confirm Password
                                </label>
                                <input
                                    id="password_confirmation"
                                    v-model="
                                        resetPasswordForm.password_confirmation
                                    "
                                    type="password"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                />
                            </div>

                            <div class="flex justify-end space-x-4">
                                <button
                                    type="button"
                                    @click="showResetPasswordModal = false"
                                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="resetPasswordForm.processing"
                                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50"
                                >
                                    {{
                                        resetPasswordForm.processing
                                            ? "Saving..."
                                            : "Save"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
