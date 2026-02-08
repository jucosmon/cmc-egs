<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    classesBySubject: Array,
    selectedSchedule: Object,
    students: Array,
    academicTerms: Array,
    activeTerm: Object,
    filters: Object,
});

const selectedTermId = computed(() => {
    return props.filters?.academic_term_id ?? props.activeTerm?.id ?? "";
});

const onTermChange = (event) => {
    const termId = event.target.value || undefined;

    router.get(
        route("enrollments.instructor-classes"),
        {
            academic_term_id: termId,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const scheduleLinkParams = (schedule) => ({
    scheduled_subject_id: schedule.id,
    academic_term_id: selectedTermId.value || undefined,
});

const isSelectedSchedule = (scheduleId) =>
    props.selectedSchedule?.id === scheduleId;
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Class Enrollments" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div
                        class="flex flex-col gap-4 p-6 md:flex-row md:items-center md:justify-between"
                    >
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800">
                                My Class Enrollments
                            </h2>
                            <p class="mt-1 text-gray-600">
                                View assigned class schedules and enrolled
                                students.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <label
                                for="term"
                                class="text-sm font-medium text-gray-600"
                            >
                                Term
                            </label>
                            <select
                                id="term"
                                class="rounded-md border border-gray-300 px-3 py-2 text-sm"
                                :value="selectedTermId"
                                @change="onTermChange"
                            >
                                <option value="">All Terms</option>
                                <option
                                    v-for="term in academicTerms"
                                    :key="term.id"
                                    :value="term.id"
                                >
                                    {{ term.academic_year }} -
                                    {{
                                        term.semester.charAt(0).toUpperCase() +
                                        term.semester.slice(1)
                                    }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!classesBySubject || classesBySubject.length === 0"
                    class="bg-white p-6 text-center text-gray-600 shadow-sm sm:rounded-lg"
                >
                    No class schedule found.
                </div>

                <div v-else class="space-y-6">
                    <div
                        v-for="subject in classesBySubject"
                        :key="subject.subject_id"
                        class="bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="border-b p-6">
                            <h3 class="text-lg font-semibold text-gray-800">
                                {{ subject.subject_code }} -
                                {{ subject.subject_title }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                Units: {{ subject.units }}
                            </p>
                        </div>
                        <div class="overflow-x-auto p-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Block
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Program
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Day
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Time
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Room
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Students
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="schedule in subject.schedules"
                                        :key="schedule.id"
                                        :class="
                                            isSelectedSchedule(schedule.id)
                                                ? 'bg-indigo-50'
                                                : 'hover:bg-gray-50'
                                        "
                                    >
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                        >
                                            {{ schedule.block_code }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                        >
                                            {{ schedule.program_name }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                        >
                                            {{ schedule.day }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                        >
                                            {{ schedule.time }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                        >
                                            {{ schedule.room }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'enrollments.instructor-classes',
                                                        scheduleLinkParams(
                                                            schedule,
                                                        ),
                                                    )
                                                "
                                                class="font-semibold text-indigo-600 hover:underline"
                                            >
                                                {{ schedule.student_count }}
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Enrolled Students
                        </h3>

                        <p v-if="!selectedSchedule" class="mt-2 text-gray-600">
                            Select a class schedule to view enrolled students.
                        </p>

                        <div v-else class="mt-4 space-y-4">
                            <div
                                class="flex flex-wrap gap-4 text-sm text-gray-600"
                            >
                                <span>
                                    <span class="font-medium">Class:</span>
                                    {{ selectedSchedule.subject_code }} -
                                    {{ selectedSchedule.subject_title }}
                                </span>
                                <span>
                                    <span class="font-medium">Block:</span>
                                    {{ selectedSchedule.block_code }}
                                </span>
                                <span>
                                    <span class="font-medium">Program:</span>
                                    {{ selectedSchedule.program_name }}
                                </span>
                                <span>
                                    <span class="font-medium">Schedule:</span>
                                    {{ selectedSchedule.day }}
                                    {{ selectedSchedule.time }}
                                </span>
                                <span>
                                    <span class="font-medium">Room:</span>
                                    {{ selectedSchedule.room }}
                                </span>
                            </div>

                            <div
                                v-if="!students || students.length === 0"
                                class="text-gray-600"
                            >
                                No students found.
                            </div>

                            <div v-else class="overflow-x-auto">
                                <table
                                    class="min-w-full divide-y divide-gray-200"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Student ID
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Name
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Program
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Year Level
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Block
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                            >
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 bg-white"
                                    >
                                        <tr
                                            v-for="student in students"
                                            :key="student.id"
                                            class="hover:bg-gray-50"
                                        >
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900"
                                            >
                                                {{ student.official_id }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-700"
                                            >
                                                {{ student.name }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                            >
                                                {{
                                                    student.program_code ||
                                                    "N/A"
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                            >
                                                {{
                                                    student.year_level || "N/A"
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                            >
                                                {{
                                                    student.block_code || "N/A"
                                                }}
                                            </td>
                                            <td
                                                class="whitespace-nowrap px-6 py-4 text-sm text-gray-600"
                                            >
                                                {{ student.status }}
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
