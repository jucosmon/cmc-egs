<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    student: Object,
    curriculum: Object,
    groupedSubjects: Object,
    activeTerm: Object,
    subjectStatuses: Object,
});

const getSemesterLabel = (semester) => {
    const labels = {
        first: "1st Semester",
        second: "2nd Semester",
        summer: "Summer",
    };
    return labels[semester] || semester;
};

const getYearLabel = (year) => {
    const labels = {
        1: "1st Year",
        2: "2nd Year",
        3: "3rd Year",
        4: "4th Year",
        5: "5th Year",
    };
    return labels[year] || `Year ${year}`;
};

const getCourseTypeLabel = (type) => {
    const labels = {
        major: "Major",
        elective: "Elective",
        minor: "Minor",
    };
    return labels[type] || type;
};

const getCourseTypeColor = (type) => {
    const colors = {
        major: "bg-blue-100 text-blue-800",
        elective: "bg-purple-100 text-purple-800",
        minor: "bg-orange-100 text-orange-800",
    };
    return colors[type] || "bg-gray-100 text-gray-800";
};

const getSubjectStatus = (curriculumSubjectId) => {
    return (
        props.subjectStatuses?.[curriculumSubjectId] || {
            status: "not_taken",
            final_grade: null,
        }
    );
};

const getStatusLabel = (status) => {
    const labels = {
        completed: "Completed",
        in_progress: "Enrolled",
        failed: "Failed",
        not_taken: "Not Taken",
    };
    return labels[status] || status;
};

const getStatusClass = (status) => {
    const classes = {
        completed: "bg-green-100 text-green-800",
        in_progress: "bg-yellow-100 text-yellow-800",
        failed: "bg-red-100 text-red-800",
        not_taken: "bg-gray-100 text-gray-700",
    };
    return classes[status] || "bg-gray-100 text-gray-700";
};

const getRowClass = (status) => {
    const classes = {
        completed: "bg-green-50",
        in_progress: "bg-yellow-50",
        failed: "bg-red-50",
        not_taken: "",
    };
    return classes[status] || "";
};

const getUnitsSummary = (subjects) => {
    const summary = {
        requiredUnits: 0,
        completedUnits: 0,
        inProgressUnits: 0,
        failedUnits: 0,
    };

    subjects.forEach((currSubject) => {
        const units = currSubject.subject?.units || 0;
        const status = getSubjectStatus(currSubject.id).status;

        summary.requiredUnits += units;

        if (status === "completed") {
            summary.completedUnits += units;
        } else if (status === "in_progress") {
            summary.inProgressUnits += units;
        } else if (status === "failed") {
            summary.failedUnits += units;
        }
    });

    return summary;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Curriculum Checklist" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div
                            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                        >
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">
                                    Curriculum Checklist
                                </h2>
                                <p class="text-sm text-gray-600">
                                    {{ student?.user?.first_name }}
                                    {{ student?.user?.last_name }}
                                    · {{ student?.program?.name }}
                                </p>
                            </div>
                            <div class="text-sm text-gray-600">
                                <div v-if="curriculum">
                                    {{ curriculum.name }}
                                    ({{ curriculum.year_effective }})
                                </div>
                                <div v-else>No active curriculum</div>
                                <div
                                    v-if="activeTerm"
                                    class="text-xs text-gray-500"
                                >
                                    Active Term:
                                    {{ activeTerm.academic_year }} -
                                    {{ activeTerm.semester }}
                                </div>
                                <div v-else class="text-xs text-gray-500">
                                    No active term
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-wrap gap-2">
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-green-500"
                                ></span>
                                Completed
                            </span>
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-800"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-yellow-500"
                                ></span>
                                Enrolled
                            </span>
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-800"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-red-500"
                                ></span>
                                Failed
                            </span>
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-gray-400"
                                ></span>
                                Not Taken
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    v-if="!curriculum"
                    class="bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-center text-gray-500">
                        No active curriculum found for your program.
                    </div>
                </div>

                <div
                    v-else-if="!Object.keys(groupedSubjects || {}).length"
                    class="bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-center text-gray-500">
                        No subjects found in this curriculum.
                    </div>
                </div>

                <div v-else class="space-y-6">
                    <div
                        v-for="(subjects, yearSemester) in groupedSubjects"
                        :key="yearSemester"
                        class="bg-white shadow-sm sm:rounded-lg"
                    >
                        <div class="bg-indigo-50 px-6 py-4">
                            <div
                                class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
                            >
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{
                                        getYearLabel(
                                            parseInt(
                                                yearSemester.split("-")[0],
                                            ),
                                        )
                                    }}
                                    -
                                    {{
                                        getSemesterLabel(
                                            yearSemester.split("-")[1],
                                        )
                                    }}
                                </h3>
                                <div class="text-sm text-gray-600">
                                    <template
                                        v-if="
                                            getUnitsSummary(subjects)
                                                .requiredUnits
                                        "
                                    >
                                        Completed
                                        {{
                                            getUnitsSummary(subjects)
                                                .completedUnits
                                        }}
                                        /
                                        {{
                                            getUnitsSummary(subjects)
                                                .requiredUnits
                                        }}
                                        units
                                        <span
                                            v-if="
                                                getUnitsSummary(subjects)
                                                    .inProgressUnits
                                            "
                                            class="text-yellow-700"
                                        >
                                            · In progress
                                            {{
                                                getUnitsSummary(subjects)
                                                    .inProgressUnits
                                            }}
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Code
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Subject
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Units
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Type
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Final Grade
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="currSubject in subjects"
                                        :key="currSubject.id"
                                        :class="
                                            getRowClass(
                                                getSubjectStatus(currSubject.id)
                                                    .status,
                                            )
                                        "
                                    >
                                        <td
                                            class="px-6 py-4 text-sm font-medium text-gray-900"
                                        >
                                            {{ currSubject.subject.code }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-700"
                                        >
                                            {{ currSubject.subject.title }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-700"
                                        >
                                            {{ currSubject.subject.units }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span
                                                :class="
                                                    getCourseTypeColor(
                                                        currSubject.course_type,
                                                    )
                                                "
                                                class="inline-flex rounded-full px-2 py-1 text-xs font-semibold"
                                            >
                                                {{
                                                    getCourseTypeLabel(
                                                        currSubject.course_type,
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <span
                                                :class="
                                                    getStatusClass(
                                                        getSubjectStatus(
                                                            currSubject.id,
                                                        ).status,
                                                    )
                                                "
                                                class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                                            >
                                                {{
                                                    getStatusLabel(
                                                        getSubjectStatus(
                                                            currSubject.id,
                                                        ).status,
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-700"
                                        >
                                            {{
                                                getSubjectStatus(currSubject.id)
                                                    .final_grade ?? "-"
                                            }}
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
