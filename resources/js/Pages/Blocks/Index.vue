<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();

defineProps({
    blocks: Object,
    programs: Array,
    filters: Object,
});

const isProgramHead = computed(
    () => page.props.auth.user.role === "program_head",
);

const deleteConfirm = ref({ show: false, id: null });

const updateFilter = (key, value) => {
    const current = new URLSearchParams(window.location.search);
    if (value) {
        current.set(key, value);
    } else {
        current.delete(key);
    }
    router.get("/blocks", Object.fromEntries(current));
};

const confirmDelete = (id) => {
    deleteConfirm.value = { show: true, id };
};

const deleteBlock = () => {
    router.delete(`/blocks/${deleteConfirm.value.id}`, {
        onSuccess: () => {
            deleteConfirm.value = { show: false, id: null };
        },
    });
};
</script>

<template>
    <Head title="Blocks Management" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Blocks Management
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Header -->
                        <div class="flex justify-between items-center mb-8">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">
                                    Blocks
                                </h1>
                                <p class="mt-2 text-gray-600">
                                    Manage class blocks for your programs
                                </p>
                            </div>
                            <Link
                                href="/blocks/create"
                                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
                            >
                                + Create Block
                            </Link>
                        </div>

                        <!-- Filters -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-8">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- Program filter - hide for program heads -->
                                <div v-if="!isProgramHead">
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Program
                                    </label>
                                    <select
                                        :value="filters?.program_id || ''"
                                        @change="
                                            updateFilter(
                                                'program_id',
                                                $event.target.value,
                                            )
                                        "
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">All Programs</option>
                                        <option
                                            v-for="program in programs"
                                            :key="program.id"
                                            :value="program.id"
                                        >
                                            {{ program.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Status
                                    </label>
                                    <select
                                        :value="filters?.status || ''"
                                        @change="
                                            updateFilter(
                                                'status',
                                                $event.target.value,
                                            )
                                        "
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">All Statuses</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">
                                            Inactive
                                        </option>
                                        <option value="graduated">
                                            Graduated
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2"
                                    >
                                        Admission Year
                                    </label>
                                    <input
                                        type="number"
                                        :value="filters?.admission_year || ''"
                                        @change="
                                            updateFilter(
                                                'admission_year',
                                                $event.target.value,
                                            )
                                        "
                                        placeholder="e.g., 2023"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Blocks Table -->
                        <div class="overflow-x-auto">
                            <table
                                class="w-full text-sm text-left text-gray-600"
                            >
                                <thead
                                    class="text-xs font-semibold text-gray-900 bg-gray-100 uppercase"
                                >
                                    <tr>
                                        <th class="px-6 py-3">Code</th>
                                        <th class="px-6 py-3">Program</th>
                                        <th class="px-6 py-3">
                                            Admission Year
                                        </th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="block in blocks.data"
                                        :key="block.id"
                                        class="border-b hover:bg-gray-50"
                                    >
                                        <td class="px-6 py-4 font-medium">
                                            {{ block.code }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{
                                                block.program
                                                    ? block.program.name
                                                    : "-"
                                            }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ block.admission_year }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <span
                                                :class="{
                                                    'bg-green-100 text-green-800':
                                                        block.status ===
                                                        'active',
                                                    'bg-gray-100 text-gray-800':
                                                        block.status ===
                                                        'inactive',
                                                    'bg-blue-100 text-blue-800':
                                                        block.status ===
                                                        'graduated',
                                                }"
                                                class="px-3 py-1 rounded-full text-xs font-medium"
                                            >
                                                {{
                                                    block.status
                                                        .charAt(0)
                                                        .toUpperCase() +
                                                    block.status.slice(1)
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex gap-2">
                                                <Link
                                                    :href="`/blocks/${block.id}`"
                                                    class="text-blue-600 hover:text-blue-800"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="`/blocks/${block.id}/edit`"
                                                    class="text-green-600 hover:text-green-800"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="
                                                        confirmDelete(block.id)
                                                    "
                                                    class="text-red-600 hover:text-red-800"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Delete Confirmation Modal -->
                        <div
                            v-if="deleteConfirm.show"
                            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                        >
                            <div class="bg-white rounded-lg p-6 max-w-sm">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Confirm Delete
                                </h3>
                                <p class="mt-2 text-gray-600">
                                    Are you sure you want to delete this block?
                                    This action cannot be undone.
                                </p>
                                <div class="mt-4 flex gap-3">
                                    <button
                                        @click="deleteConfirm.show = false"
                                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        @click="deleteBlock"
                                        class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
