<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    enrollment: Object,
    message: String,
});

const totalUnits = computed(() => {
    if (!props.enrollment?.enrolled_subjects) return 0;
    return props.enrollment.enrolled_subjects.reduce((sum, es) => {
        return (
            sum +
            (es.scheduled_subject?.curriculum_subject?.subject?.units || 0)
        );
    }, 0);
});

const downloadSchedule = () => {
    window.location.href = route(
        "enrollments.download-schedule",
        props.enrollment.id,
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Enrollment" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- No Enrollment Message -->
                <div
                    v-if="message && !enrollment"
                    class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center"
                >
                    <svg
                        class="mx-auto h-12 w-12 text-yellow-400 mb-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                        />
                    </svg>
                    <p class="text-lg font-semibold text-yellow-800">
                        {{ message }}
                    </p>
                </div>

                <!-- Enrollment Summary -->
                <div v-else-if="enrollment" class="space-y-6">
                    <!-- Header -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2
                                        class="text-2xl font-bold text-gray-800"
                                    >
                                        Enrollment Summary
                                    </h2>
                                    <p class="text-gray-600 mt-1">
                                        {{
                                            enrollment.academic_term
                                                .academic_year
                                        }}
                                        -
                                        {{
                                            enrollment.academic_term.semester
                                                .charAt(0)
                                                .toUpperCase() +
                                            enrollment.academic_term.semester.slice(
                                                1,
                                            )
                                        }}
                                        Semester
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span
                                        :class="
                                            enrollment.status === 'enrolled'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-blue-100 text-blue-800'
                                        "
                                        class="px-3 py-1 rounded-full text-sm font-semibold"
                                    >
                                        {{ enrollment.status.toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6"
                            >
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-600">Program</p>
                                    <p class="font-semibold text-gray-800">
                                        {{ enrollment.student.program.code }}
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-600">
                                        Year Level
                                    </p>
                                    <p class="font-semibold text-gray-800">
                                        {{ enrollment.year_level }}
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-600">Block</p>
                                    <p class="font-semibold text-gray-800">
                                        {{ enrollment.block?.code || "N/A" }}
                                    </p>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <p class="text-sm text-gray-600">
                                        Total Units
                                    </p>
                                    <p class="font-semibold text-gray-800">
                                        {{ totalUnits }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enrolled Subjects -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-gray-800">
                                    My Class Schedule ({{
                                        enrollment.enrolled_subjects?.length ||
                                        0
                                    }}
                                    Subjects)
                                </h3>
                                <div class="flex gap-2">
                                    <button
                                        @click="downloadSchedule"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 flex items-center gap-2"
                                    >
                                        <svg
                                            class="w-5 h-5"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                            />
                                        </svg>
                                        Download PDF
                                    </button>
                                </div>
                            </div>

                            <div
                                v-if="
                                    enrollment.enrolled_subjects &&
                                    enrollment.enrolled_subjects.length > 0
                                "
                                class="overflow-x-auto"
                            >
                                <table
                                    class="min-w-full divide-y divide-gray-200"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Subject Code
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Subject Name
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Instructor
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Schedule
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Room
                                            </th>
                                            <th
                                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                            >
                                                Units
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="bg-white divide-y divide-gray-200"
                                    >
                                        <tr
                                            v-for="es in enrollment.enrolled_subjects"
                                            :key="es.id"
                                            class="hover:bg-gray-50"
                                        >
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                            >
                                                {{
                                                    es.scheduled_subject
                                                        .curriculum_subject
                                                        .subject.code
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 text-sm text-gray-700"
                                            >
                                                {{
                                                    es.scheduled_subject
                                                        .curriculum_subject
                                                        .subject.title
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                            >
                                                {{
                                                    es.scheduled_subject
                                                        .instructor?.user
                                                        ?.first_name
                                                }}
                                                {{
                                                    es.scheduled_subject
                                                        .instructor?.user
                                                        ?.last_name
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                            >
                                                {{ es.scheduled_subject.day
                                                }}<br />
                                                {{
                                                    es.scheduled_subject
                                                        .time_start
                                                }}
                                                -
                                                {{
                                                    es.scheduled_subject
                                                        .time_end
                                                }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                            >
                                                {{ es.scheduled_subject.room }}
                                            </td>
                                            <td
                                                class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"
                                            >
                                                {{
                                                    es.scheduled_subject
                                                        .curriculum_subject
                                                        .subject.units
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p v-else class="text-gray-500 text-center py-8">
                                No subjects enrolled yet
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
@media print {
    nav,
    header,
    button,
    .no-print {
        display: none !important;
    }
}
</style>
