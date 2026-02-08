<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    enrollments: Array,
    academicTerms: Array,
    activeTermId: Number,
    filters: Object,
});

const sortedEnrollments = computed(() => {
    const list = [...(props.enrollments || [])];
    return list.sort((a, b) => b.academic_term_id - a.academic_term_id);
});

const currentEnrollment = computed(() => {
    return sortedEnrollments.value.find(
        (enrollment) => enrollment.academic_term_id === props.activeTermId,
    );
});

const previousEnrollments = computed(() => {
    return sortedEnrollments.value.filter(
        (enrollment) => enrollment.academic_term_id !== props.activeTermId,
    );
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="My Grades" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Manage Grades (Student)
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div
                    v-if="!sortedEnrollments.length"
                    class="bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-center text-gray-500">
                        No subjects enrolled yet.
                    </div>
                </div>

                <div v-else class="space-y-6">
                    <div
                        v-if="currentEnrollment"
                        class="bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">
                                Current Term Grades
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{
                                    currentEnrollment.academic_term
                                        ?.academic_year
                                }}
                                -
                                {{ currentEnrollment.academic_term?.semester }}
                            </p>
                            <div class="mt-4 overflow-x-auto">
                                <table
                                    class="min-w-full divide-y divide-gray-200"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Subject
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Midterm
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Final
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 bg-white"
                                    >
                                        <tr
                                            v-for="subject in currentEnrollment.enrolled_subjects"
                                            :key="subject.id"
                                        >
                                            <td class="px-4 py-3 text-sm">
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    {{
                                                        subject
                                                            .scheduled_subject
                                                            ?.curriculum_subject
                                                            ?.subject?.code
                                                    }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{
                                                        subject
                                                            .scheduled_subject
                                                            ?.curriculum_subject
                                                            ?.subject?.title
                                                    }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{
                                                    subject.midterm_grade ?? "-"
                                                }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ subject.final_grade ?? "-" }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 capitalize"
                                                >
                                                    {{ subject.status }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div
                        v-for="enrollment in previousEnrollments"
                        :key="enrollment.id"
                        class="bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{ enrollment.academic_term?.academic_year }} -
                                {{ enrollment.academic_term?.semester }}
                            </h3>
                            <div class="mt-4 overflow-x-auto">
                                <table
                                    class="min-w-full divide-y divide-gray-200"
                                >
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Subject
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Midterm
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Final
                                            </th>
                                            <th
                                                class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                            >
                                                Status
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody
                                        class="divide-y divide-gray-200 bg-white"
                                    >
                                        <tr
                                            v-for="subject in enrollment.enrolled_subjects"
                                            :key="subject.id"
                                        >
                                            <td class="px-4 py-3 text-sm">
                                                <p
                                                    class="font-medium text-gray-900"
                                                >
                                                    {{
                                                        subject
                                                            .scheduled_subject
                                                            ?.curriculum_subject
                                                            ?.subject?.code
                                                    }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500"
                                                >
                                                    {{
                                                        subject
                                                            .scheduled_subject
                                                            ?.curriculum_subject
                                                            ?.subject?.title
                                                    }}
                                                </p>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{
                                                    subject.midterm_grade ?? "-"
                                                }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                {{ subject.final_grade ?? "-" }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span
                                                    class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 capitalize"
                                                >
                                                    {{ subject.status }}
                                                </span>
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
