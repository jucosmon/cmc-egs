<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const flash = computed(() => page.props.flash || {});

const props = defineProps({
    accounts: {
        type: Object,
        required: true,
    },
    userType: {
        type: String,
        required: true,
    },
    visibleUserTypes: {
        type: Array,
        required: true,
    },
    canCreate: {
        type: Boolean,
        default: false,
    },
    currentUserRole: {
        type: String,
        required: true,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const selectedType = ref(props.userType);
const searchQuery = ref("");

// Filter accounts based on search query
const filteredAccounts = computed(() => {
    if (!searchQuery.value) {
        return props.accounts.data || [];
    }

    return (props.accounts.data || []).filter((account) => {
        const fullName =
            `${account.first_name} ${account.middle_name} ${account.last_name}`.toLowerCase();
        const email = account.email.toLowerCase();
        const query = searchQuery.value.toLowerCase();
        return fullName.includes(query) || email.includes(query);
    });
});

// Get the role label
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

// Format date
const formatDate = (date) => {
    if (!date) return "N/A";
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manage Accounts" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900">
                        Manage Accounts
                    </h1>
                    <p class="mt-2 text-gray-600">
                        View and manage user accounts
                    </p>
                </div>

                <!-- Flash Messages -->
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

                <!-- Error Messages -->
                <div
                    v-if="errors && Object.keys(errors).length > 0"
                    class="mb-4 rounded-md bg-red-50 p-4"
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
                            <h3 class="text-sm font-medium text-red-800">
                                Please fix the following errors:
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-inside list-disc space-y-1">
                                    <li
                                        v-for="(error, field) in errors"
                                        :key="field"
                                        class="capitalize"
                                    >
                                        {{ field }}: {{ error[0] }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Type Tabs -->
                <div class="mb-8 border-b border-gray-200">
                    <div class="flex space-x-8">
                        <Link
                            v-for="type in visibleUserTypes"
                            :key="type.value"
                            :href="
                                route('accounts.index', { type: type.value })
                            "
                            :class="[
                                'px-1 py-4 border-b-2 font-medium text-sm',
                                selectedType === type.value
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            ]"
                        >
                            {{ type.name }}
                        </Link>
                    </div>
                </div>

                <!-- Actions Bar -->
                <div class="mb-6 flex items-center justify-between">
                    <!-- Search Box -->
                    <div class="flex-1">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by name or email..."
                            class="w-full max-w-md rounded-md border-gray-300 px-4 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>

                    <!-- Create Button -->
                    <Link
                        v-if="canCreate"
                        :href="route('accounts.create', { type: selectedType })"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Create {{ getRoleLabel(selectedType) }}
                    </Link>
                </div>

                <!-- Accounts Table -->
                <div
                    v-if="filteredAccounts.length > 0"
                    class="overflow-x-auto rounded-lg shadow"
                >
                    <table class="min-w-full divide-y divide-gray-200 bg-white">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Name
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Email
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Department
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Official ID
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr
                                v-for="account in filteredAccounts"
                                :key="account.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ account.full_name }}
                                    </div>
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                >
                                    {{ account.email }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                >
                                    {{ account.department_name || "N/A" }}
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-sm text-gray-500"
                                >
                                    {{ account.official_id || "N/A" }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        :class="[
                                            'inline-flex rounded-full px-2 text-xs font-semibold leading-5',
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
                                </td>
                                <td
                                    class="whitespace-nowrap px-6 py-4 text-center text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route('accounts.show', {
                                                account: account.id,
                                                type: selectedType,
                                            })
                                        "
                                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs text-white hover:bg-indigo-700"
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- No Accounts Message -->
                <div
                    v-else
                    class="rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-8 py-12 text-center"
                >
                    <div class="mb-4 flex justify-center">
                        <svg
                            class="h-12 w-12 text-gray-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10a3 3 0 11-6 0 3 3 0 016 0zM15 20H9m6 0H9m6 0a6.001 6.001 0 01-6 6H3a6 6 0 016-6h6z"
                            />
                        </svg>
                    </div>
                    <p class="text-lg font-medium text-gray-900">
                        No users found.
                    </p>
                    <p class="mt-1 text-gray-600">
                        {{
                            canCreate
                                ? "Create the first one to get started."
                                : ""
                        }}
                    </p>
                    <div v-if="canCreate" class="mt-6">
                        <Link
                            :href="
                                route('accounts.create', { type: selectedType })
                            "
                            class="inline-flex rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Create {{ getRoleLabel(selectedType) }}
                        </Link>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="
                        props.accounts.links && props.accounts.links.length > 3
                    "
                    class="mt-6 flex justify-center space-x-1"
                >
                    <Link
                        v-for="link in props.accounts.links"
                        :key="link.label"
                        :href="link.url || '#'"
                        :class="[
                            'px-3 py-2 text-sm font-medium rounded-md',
                            link.active
                                ? 'bg-indigo-600 text-white'
                                : link.url
                                  ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
                                  : 'text-gray-400 cursor-not-allowed',
                        ]"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
