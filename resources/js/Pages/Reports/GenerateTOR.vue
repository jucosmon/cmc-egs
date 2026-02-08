<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    students: Array,
    search: String,
    studentRecord: Object,
    torRecords: Array,
    torSummary: Object,
    torMessage: String,
});

const searchForm = useForm({
    search: props.search || "",
});

const searchStudent = () => {
    router.get(
        route("reports.generate-tor"),
        { search: searchForm.search || undefined },
        { preserveState: true, preserveScroll: true },
    );
};

const selectStudent = (student) => {
    router.get(
        route("reports.generate-tor"),
        {
            search: searchForm.search || undefined,
            student_id: student.id,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const hasSearch = computed(() => searchForm.search.trim().length > 0);
const hasResults = computed(
    () => Array.isArray(props.students) && props.students.length > 0,
);

const summary = computed(() => {
    if (props.torSummary) return props.torSummary;
    const allSubjects = (props.torRecords || []).flatMap(
        (group) => group.subjects || [],
    );

    const totalUnits = allSubjects.reduce(
        (sum, subject) => sum + (subject.units || 0),
        0,
    );
    const totalGradePoints = allSubjects.reduce(
        (sum, subject) =>
            sum + (subject.final_grade || 0) * (subject.units || 0),
        0,
    );
    const overallGwa = totalUnits > 0 ? totalGradePoints / totalUnits : 0;

    return {
        total_units: totalUnits,
        total_subjects: allSubjects.length,
        overall_gwa: Number(overallGwa.toFixed(2)),
    };
});

const downloadTor = () => {
    if (!props.studentRecord) return;
    window.location.href = route(
        "reports.download-tor",
        props.studentRecord.id,
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Generate TOR" />

        <template #header>
            <h2
                class="text-lg font-semibold leading-tight text-[#1f7fa3] sm:text-xl"
            >
                Generate TOR
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Search Student
                        </h3>
                        <p class="text-sm text-gray-600">
                            Search by student ID or full name to generate the
                            Transcript of Records.
                        </p>
                        <form
                            @submit.prevent="searchStudent"
                            class="mt-4 flex flex-col gap-3 md:flex-row"
                        >
                            <input
                                v-model="searchForm.search"
                                type="text"
                                placeholder="Enter Student ID or name"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <button
                                type="submit"
                                class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                Search
                            </button>
                        </form>
                        <p v-if="torMessage" class="mt-3 text-sm text-red-600">
                            {{ torMessage }}
                        </p>
                    </div>
                </div>

                <div v-if="hasSearch" class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-base font-semibold text-gray-800">
                            Search Results
                        </h4>

                        <div
                            v-if="!hasResults"
                            class="mt-4 rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500"
                        >
                            No student found.
                        </div>

                        <div v-else class="mt-4 divide-y">
                            <div
                                v-for="student in students"
                                :key="student.id"
                                class="flex flex-col gap-2 py-4 md:flex-row md:items-center md:justify-between"
                            >
                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ student.user.first_name }}
                                        {{ student.user.last_name }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ student.user.official_id }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ student.program?.name }}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md border border-indigo-600 px-4 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50"
                                    @click="selectStudent(student)"
                                >
                                    View Record
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="studentRecord"
                    class="bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6">
                        <div
                            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                        >
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ studentRecord.user.first_name }}
                                    {{ studentRecord.user.last_name }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ studentRecord.user.official_id }}
                                </p>
                            </div>
                            <div class="text-sm text-gray-600">
                                <p>
                                    Program:
                                    {{ studentRecord.program?.name || "N/A" }}
                                </p>
                                <p>
                                    Year Level:
                                    {{ studentRecord.year_level || "N/A" }}
                                </p>
                                <p>
                                    Block:
                                    {{ studentRecord.block?.code || "N/A" }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-6 grid gap-4 rounded-lg border border-gray-200 bg-gray-50 p-4 md:grid-cols-3"
                        >
                            <div>
                                <p class="text-xs text-gray-500">Total Units</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ summary.total_units }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Subjects</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ summary.total_subjects }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">GWA</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ summary.overall_gwa }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="studentRecord"
                    class="bg-white shadow-sm sm:rounded-lg"
                >
                    <div class="p-6">
                        <div
                            class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                        >
                            <h4 class="text-lg font-semibold text-gray-900">
                                Completed Subjects
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                    @click="downloadTor"
                                >
                                    Download PDF
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="!torRecords || !torRecords.length"
                            class="mt-6 rounded-lg border border-dashed border-gray-300 p-6 text-center text-sm text-gray-500"
                        >
                            No completed subjects found.
                        </div>

                        <div v-else class="mt-6 space-y-6">
                            <div
                                v-for="group in torRecords"
                                :key="group.term"
                                class="rounded-lg border border-gray-200"
                            >
                                <div
                                    class="flex flex-col gap-2 bg-gray-50 px-4 py-3 md:flex-row md:items-center md:justify-between"
                                >
                                    <p class="font-semibold text-gray-800">
                                        {{ group.term }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Year Level:
                                        {{ group.year_level || "N/A" }}
                                    </p>
                                </div>
                                <div class="overflow-x-auto">
                                    <table
                                        class="min-w-full divide-y divide-gray-200"
                                    >
                                        <thead class="bg-white">
                                            <tr>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                                >
                                                    Subject
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                                >
                                                    Units
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                                >
                                                    Final Grade
                                                </th>
                                                <th
                                                    class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500"
                                                >
                                                    Remarks
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody
                                            class="divide-y divide-gray-200 bg-white"
                                        >
                                            <tr
                                                v-for="subject in group.subjects"
                                                :key="`${group.term}-${subject.code}`"
                                            >
                                                <td class="px-4 py-3 text-sm">
                                                    <p
                                                        class="font-medium text-gray-900"
                                                    >
                                                        {{ subject.code }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-gray-500"
                                                    >
                                                        {{ subject.title }}
                                                    </p>
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-700"
                                                >
                                                    {{ subject.units }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-700"
                                                >
                                                    {{ subject.final_grade }}
                                                </td>
                                                <td
                                                    class="px-4 py-3 text-sm text-gray-700"
                                                >
                                                    {{ subject.remarks }}
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
        </div>
    </AuthenticatedLayout>
</template>
