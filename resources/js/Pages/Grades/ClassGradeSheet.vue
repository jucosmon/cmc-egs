<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    scheduledSubject: Object,
});

const getGradeDisplay = (subject, period) => {
    const displayKey = `${period}_grade_display`;
    const rawKey = `${period}_grade`;
    return subject?.[displayKey] ?? subject?.[rawKey] ?? "-";
};

const isNumericGradeDisplay = (value) => {
    if (value === null || value === undefined || value === "") {
        return false;
    }

    return !Number.isNaN(Number(value));
};

const getGradeBadgeClass = (value) => {
    const normalized = String(value || "").toUpperCase();

    if (["INC", "INP", "INE", "IP", "IN PROGRESS"].includes(normalized)) {
        return "bg-amber-100 text-amber-800";
    }

    if (["DRP", "DROPPED", "W"].includes(normalized)) {
        return "bg-slate-100 text-slate-700";
    }

    if (["UD", "FDA", "5", "5.0"].includes(normalized)) {
        return "bg-rose-100 text-rose-800";
    }

    if (["P", "AU"].includes(normalized)) {
        return "bg-emerald-100 text-emerald-800";
    }

    return "bg-gray-100 text-gray-700";
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Class Grade Sheet" />

        <template #header>
            <h2
                class="text-lg font-semibold leading-tight text-[#1f7fa3] sm:text-xl"
            >
                Class Grade Sheet
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-4">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900">
                                {{
                                    scheduledSubject.curriculum_subject?.subject
                                        ?.code
                                }}
                                -
                                {{
                                    scheduledSubject.curriculum_subject?.subject
                                        ?.title
                                }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                Instructor:
                                {{
                                    scheduledSubject.instructor?.user
                                        ?.first_name
                                }}
                                {{
                                    scheduledSubject.instructor?.user?.last_name
                                }}
                            </p>
                            <p class="text-sm text-gray-600">
                                Block: {{ scheduledSubject.block?.code }} •
                                {{ scheduledSubject.day }}
                                {{ scheduledSubject.time_start }} -
                                {{ scheduledSubject.time_end }}
                            </p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                        >
                                            Student
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
                                        v-for="subject in scheduledSubject.enrolled_subjects"
                                        :key="subject.id"
                                    >
                                        <td class="px-4 py-3 text-sm">
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                {{
                                                    subject.enrollment?.student
                                                        ?.user?.first_name
                                                }}
                                                {{
                                                    subject.enrollment?.student
                                                        ?.user?.last_name
                                                }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    subject.enrollment?.student
                                                        ?.user?.official_id
                                                }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <template
                                                v-if="
                                                    isNumericGradeDisplay(
                                                        getGradeDisplay(
                                                            subject,
                                                            'midterm',
                                                        ),
                                                    )
                                                "
                                            >
                                                {{
                                                    getGradeDisplay(
                                                        subject,
                                                        "midterm",
                                                    )
                                                }}
                                            </template>
                                            <span
                                                v-else
                                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                                :class="
                                                    getGradeBadgeClass(
                                                        getGradeDisplay(
                                                            subject,
                                                            'midterm',
                                                        ),
                                                    )
                                                "
                                            >
                                                {{
                                                    getGradeDisplay(
                                                        subject,
                                                        "midterm",
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <template
                                                v-if="
                                                    isNumericGradeDisplay(
                                                        getGradeDisplay(
                                                            subject,
                                                            'final',
                                                        ),
                                                    )
                                                "
                                            >
                                                {{
                                                    getGradeDisplay(
                                                        subject,
                                                        "final",
                                                    )
                                                }}
                                            </template>
                                            <span
                                                v-else
                                                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                                :class="
                                                    getGradeBadgeClass(
                                                        getGradeDisplay(
                                                            subject,
                                                            'final',
                                                        ),
                                                    )
                                                "
                                            >
                                                {{
                                                    getGradeDisplay(
                                                        subject,
                                                        "final",
                                                    )
                                                }}
                                            </span>
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
    </AuthenticatedLayout>
</template>
