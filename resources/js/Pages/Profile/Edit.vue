<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    account: {
        type: Object,
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
});

const activePanel = ref("view");
const page = usePage();

const flashSuccess = computed(() => {
    const value = page.props.flash?.success;
    return typeof value === "function" ? value() : value;
});

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
    <Head title="My Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                My Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
                <div
                    v-if="flashSuccess"
                    class="rounded-md bg-green-50 p-4 text-sm text-green-700"
                >
                    {{ flashSuccess }}
                </div>

                <div class="rounded-lg bg-white px-6 py-8 shadow">
                    <div
                        class="flex flex-col gap-6 sm:flex-row sm:items-center"
                    >
                        <div class="h-20 w-20 flex-shrink-0">
                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-600"
                            >
                                <span class="text-2xl font-medium text-white">
                                    {{
                                        props.account.first_name &&
                                        props.account.last_name
                                            ? (
                                                  props.account.first_name.charAt(
                                                      0,
                                                  ) +
                                                  props.account.last_name.charAt(
                                                      0,
                                                  )
                                              ).toUpperCase()
                                            : "?"
                                    }}
                                </span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h1 class="text-3xl font-bold text-gray-900">
                                {{ props.account.full_name }}
                            </h1>
                            <p class="mt-2 text-gray-600">
                                {{ getRoleLabel(props.account.role) }} Account
                            </p>
                            <div class="mt-4 flex flex-wrap gap-3">
                                <button
                                    type="button"
                                    @click="activePanel = 'update'"
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Update Profile
                                </button>
                                <button
                                    type="button"
                                    @click="activePanel = 'password'"
                                    class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                                >
                                    Change Password
                                </button>
                                <button
                                    v-if="activePanel !== 'view'"
                                    type="button"
                                    @click="activePanel = 'view'"
                                    class="inline-flex items-center rounded-md bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-300"
                                >
                                    Back to Details
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="activePanel === 'view'"
                    class="rounded-lg bg-white shadow"
                >
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
                                    {{ props.account.email }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Official ID
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.account.official_id || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    First Name
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.account.first_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Middle Name
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.account.middle_name || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Last Name
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.account.last_name }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Phone
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.account.phone || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Date of Birth
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        formatDate(props.account.date_of_birth)
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Sex
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.account.sex || "N/A" }}
                                </dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">
                                    Address
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.account.address || "N/A" }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div
                    v-if="
                        activePanel === 'view' && props.account.role === 'dean'
                    "
                    class="rounded-lg bg-white shadow"
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
                                        (props.department &&
                                            props.department.name) ||
                                        "Not assigned"
                                    }}
                                </dd>
                            </div>
                            <div v-if="props.department" class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">
                                    Programs in Department
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <ul class="list-inside list-disc">
                                        <li
                                            v-for="prog in props.department
                                                .programs"
                                            :key="prog.id"
                                        >
                                            {{ prog.name }}
                                        </li>
                                        <li
                                            v-if="
                                                !props.department.programs ||
                                                props.department.programs
                                                    .length === 0
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

                <div
                    v-if="
                        activePanel === 'view' &&
                        props.account.role === 'program_head'
                    "
                    class="rounded-lg bg-white shadow"
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
                                        (props.program && props.program.name) ||
                                        "Not assigned"
                                    }}
                                </dd>
                            </div>
                            <div v-if="props.program">
                                <dt class="text-sm font-medium text-gray-500">
                                    Program Department
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        (props.program.department &&
                                            props.program.department.name) ||
                                        "N/A"
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div
                    v-if="activePanel === 'view' && props.student"
                    class="rounded-lg bg-white shadow"
                >
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
                                    {{ props.student.program?.name || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Enrollment Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ formatDate(props.student.created_at) }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Year Level
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.student.year_level || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Status
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.student.status || "N/A" }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Block
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ props.student.block?.name || "N/A" }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div
                    v-if="activePanel === 'view' && props.instructor"
                    class="rounded-lg bg-white shadow"
                >
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
                                    {{
                                        props.instructor.department?.name ||
                                        "N/A"
                                    }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">
                                    Hire Date
                                </dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{
                                        formatDate(props.instructor.created_at)
                                    }}
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div
                    v-if="activePanel === 'update'"
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        :account="props.account"
                        class="max-w-3xl"
                    />
                </div>

                <div
                    v-if="activePanel === 'password'"
                    class="bg-white p-4 shadow sm:rounded-lg sm:p-8"
                >
                    <UpdatePasswordForm
                        class="max-w-xl"
                        @updated="activePanel = 'view'"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
