<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    program: {
        type: Object,
        required: true,
    },
    activeTerm: {
        type: Object,
        default: null,
    },
    instructorLoads: {
        type: Array,
        default: () => [],
    },
});

const formatTerm = (term) => {
    if (!term) return "No active term";
    const semester = term.semester ? String(term.semester) : "";
    return `${term.academic_year} - ${semester.charAt(0).toUpperCase()}${semester.slice(1)}`;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Instructor Loads" />

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div class="mb-6 rounded-lg bg-white p-6 shadow">
                    <h1 class="text-2xl font-bold text-gray-900">
                        Instructor Loads
                    </h1>
                    <p class="mt-2 text-sm text-gray-600">
                        Program: {{ program.name }}
                    </p>
                    <p class="text-sm text-gray-600">
                        Active Term: {{ formatTerm(activeTerm) }}
                    </p>
                </div>

                <div
                    v-if="!activeTerm"
                    class="rounded-lg bg-yellow-50 p-6 text-sm text-yellow-800"
                >
                    No active academic term found. Set an active term to view
                    instructor loads.
                </div>

                <div
                    v-else-if="!instructorLoads.length"
                    class="rounded-lg bg-gray-50 p-6 text-sm text-gray-600"
                >
                    No scheduled subjects found for this program in the active
                    term.
                </div>

                <div v-else class="space-y-6">
                    <div
                        v-for="instructor in instructorLoads"
                        :key="instructor.id"
                        class="rounded-lg bg-white shadow"
                    >
                        <div class="border-b border-gray-200 px-6 py-4">
                            <div
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <div>
                                    <h2
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        {{ instructor.name }}
                                    </h2>
                                    <p class="text-sm text-gray-500">
                                        {{ instructor.official_id || "No ID" }}
                                    </p>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <span class="mr-4">
                                        Subjects:
                                        {{ instructor.total_subjects }}
                                    </span>
                                    <span class="mr-4">
                                        Units: {{ instructor.total_units }}
                                    </span>
                                    <span>
                                        Enrolled:
                                        {{ instructor.total_enrolled }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4">
                            <div class="overflow-x-auto">
                                <table
                                    class="min-w-full divide-y divide-gray-200"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Subject
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Block
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Schedule
                                            </th>
                                            <th
                                                class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Room
                                            </th>
                                            <th
                                                class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500"
                                            >
                                                Units
                                            </th>
                                            <th
                                                class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500"
                                            >
                                                Enrolled
                                            </th>
                                            <th
                                                class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500"
                                            >
                                                Completed
                                            </th>
                                            <th
                                                class="px-3 py-2 text-right text-xs font-medium uppercase text-gray-500"
                                            >
                                                Dropped
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        <tr
                                            v-for="detail in instructor.details"
                                            :key="detail.id"
                                        >
                                            <td
                                                class="px-3 py-2 text-sm text-gray-900"
                                            >
                                                <div class="font-medium">
                                                    {{ detail.subject_code }}
                                                </div>
                                                <div
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{ detail.subject_title }}
                                                </div>
                                            </td>
                                            <td
                                                class="px-3 py-2 text-sm text-gray-900"
                                            >
                                                {{ detail.block_code }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-sm text-gray-900"
                                            >
                                                {{ detail.day }}
                                                {{ detail.time }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-sm text-gray-900"
                                            >
                                                {{ detail.room || "N/A" }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-right text-sm text-gray-900"
                                            >
                                                {{ detail.units }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-right text-sm text-gray-900"
                                            >
                                                {{ detail.enrolled_count }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-right text-sm text-gray-900"
                                            >
                                                {{ detail.completed_count }}
                                            </td>
                                            <td
                                                class="px-3 py-2 text-right text-sm text-gray-900"
                                            >
                                                {{ detail.dropped_count }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
