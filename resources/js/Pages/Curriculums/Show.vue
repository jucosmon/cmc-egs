<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    curriculum: Object,
    groupedSubjects: Object,
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
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Curriculum - ${curriculum.name}`" />

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Card -->
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6"
                >
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-3xl font-bold text-gray-900">
                                    {{ curriculum.name }}
                                </h2>
                                <p class="text-gray-600 mt-2">
                                    {{ curriculum.program.name }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    :class="
                                        curriculum.is_active
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-red-100 text-red-800'
                                    "
                                    class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full"
                                >
                                    {{
                                        curriculum.is_active
                                            ? "Active"
                                            : "Inactive"
                                    }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 border-t pt-4">
                            <div>
                                <p class="text-sm text-gray-600">
                                    Year Effective
                                </p>
                                <p class="text-lg font-semibold">
                                    {{ curriculum.year_effective }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">
                                    Total Subjects
                                </p>
                                <p class="text-lg font-semibold">
                                    {{ curriculum.curriculum_subjects.length }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Total Units</p>
                                <p class="text-lg font-semibold">
                                    {{
                                        curriculum.curriculum_subjects.reduce(
                                            (sum, cs) =>
                                                sum + (cs.subject.units || 0),
                                            0,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 border-t pt-4">
                            <Link
                                :href="route('curriculums.edit', curriculum.id)"
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
                            >
                                Edit Curriculum
                            </Link>
                            <Link
                                :href="route('curriculums.index')"
                                class="text-gray-700 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                            >
                                Back
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Curriculum Subjects -->
                <div
                    v-if="curriculum.curriculum_subjects.length === 0"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6 text-center">
                        <p class="text-gray-500">
                            No subjects in this curriculum yet.
                        </p>
                    </div>
                </div>

                <div v-else class="space-y-6">
                    <div
                        v-for="(subjects, yearSemester) in groupedSubjects"
                        :key="yearSemester"
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="bg-indigo-50 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                {{
                                    getYearLabel(
                                        parseInt(yearSemester.split("-")[0]),
                                    )
                                }}
                                -
                                {{
                                    getSemesterLabel(yearSemester.split("-")[1])
                                }}
                            </h3>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Code
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Subject
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Units
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Type
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Laboratory
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Prerequisites
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="currSubject in subjects"
                                        :key="currSubject.id"
                                    >
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                        >
                                            {{ currSubject.subject.code }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            {{ currSubject.subject.title }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm"
                                        >
                                            {{ currSubject.subject.units }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="
                                                    getCourseTypeColor(
                                                        currSubject.course_type,
                                                    )
                                                "
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            >
                                                {{
                                                    getCourseTypeLabel(
                                                        currSubject.course_type,
                                                    )
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm"
                                        >
                                            <span
                                                v-if="
                                                    currSubject.has_laboratory
                                                "
                                                class="text-green-600"
                                            >
                                                ✓
                                            </span>
                                            <span v-else class="text-gray-400">
                                                -
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            <div
                                                v-if="
                                                    currSubject.prerequisites
                                                        .length > 0
                                                "
                                                class="flex flex-wrap gap-1"
                                            >
                                                <span
                                                    v-for="prereq in currSubject.prerequisites"
                                                    :key="prereq.id"
                                                    class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs"
                                                >
                                                    {{ prereq.subject.code }}
                                                </span>
                                            </div>
                                            <span v-else class="text-gray-400">
                                                None
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
