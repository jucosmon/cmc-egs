<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    stats: Object,
    byProgram: Array,
    byYearLevel: Array,
    byStatus: Array,
    enrollmentTrend: Array,
    academicTerms: Array,
    programs: Array,
    filters: Object,
});

const filterForm = useForm({
    academic_term_id: props.filters?.academic_term_id ?? "",
    program_id: props.filters?.program_id ?? "",
    year_level: props.filters?.year_level ?? "",
});

const yearLevelOptions = [1, 2, 3, 4, 5];

const applyFilters = () => {
    router.get(
        route("reports.enrollment-statistics"),
        {
            academic_term_id: filterForm.academic_term_id || undefined,
            program_id: filterForm.program_id || undefined,
            year_level: filterForm.year_level || undefined,
        },
        { preserveState: true, preserveScroll: true },
    );
};

const downloadReport = () => {
    window.location.href = route("reports.export-enrollment", {
        academic_term_id: filterForm.academic_term_id || undefined,
        program_id: filterForm.program_id || undefined,
        year_level: filterForm.year_level || undefined,
    });
};

const totalEnrollments = computed(() => props.stats?.total_enrollments ?? 0);
const hasData = computed(() => totalEnrollments.value > 0);

const maxProgramTotal = computed(() => {
    const totals = (props.byProgram || []).map((item) => item.total);
    return Math.max(...totals, 1);
});

const maxYearLevelTotal = computed(() => {
    const totals = (props.byYearLevel || []).map((item) => item.total);
    return Math.max(...totals, 1);
});

const maxStatusTotal = computed(() => {
    const totals = (props.byStatus || []).map((item) => item.total);
    return Math.max(...totals, 1);
});

const programTotal = computed(() =>
    (props.byProgram || []).reduce((sum, item) => sum + item.total, 0),
);

const chartColors = [
    "#2563eb",
    "#16a34a",
    "#f59e0b",
    "#ef4444",
    "#8b5cf6",
    "#0ea5e9",
    "#10b981",
];

const polarToCartesian = (cx, cy, radius, angle) => {
    const rad = ((angle - 90) * Math.PI) / 180;
    return {
        x: cx + radius * Math.cos(rad),
        y: cy + radius * Math.sin(rad),
    };
};

const describeArc = (cx, cy, radius, startAngle, endAngle) => {
    const start = polarToCartesian(cx, cy, radius, endAngle);
    const end = polarToCartesian(cx, cy, radius, startAngle);
    const largeArc = endAngle - startAngle <= 180 ? 0 : 1;
    return [
        "M",
        cx,
        cy,
        "L",
        start.x,
        start.y,
        "A",
        radius,
        radius,
        0,
        largeArc,
        0,
        end.x,
        end.y,
        "Z",
    ].join(" ");
};

const programPieSegments = computed(() => {
    const total = programTotal.value || 1;
    let start = 0;
    return (props.byProgram || []).map((item, index) => {
        const angle = (item.total / total) * 360;
        const end = start + angle;
        const path = describeArc(60, 60, 48, start, end);
        const color = chartColors[index % chartColors.length];
        start = end;
        return { ...item, path, color };
    });
});

const formatSemester = (semester) => {
    if (!semester) return "";
    return semester.charAt(0).toUpperCase() + semester.slice(1);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Enrollment Statistics" />

        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Enrollment Statistics
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div
                            class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between"
                        >
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Filters
                                </h3>
                                <p class="text-sm text-gray-600">
                                    Refine the statistics by term, program, and
                                    year level.
                                </p>
                            </div>
                            <button
                                type="button"
                                class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                @click="downloadReport"
                            >
                                Download PDF
                            </button>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Term</label
                                >
                                <select
                                    v-model="filterForm.academic_term_id"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    @change="applyFilters"
                                >
                                    <option value="">All terms</option>
                                    <option
                                        v-for="term in academicTerms || []"
                                        :key="term.id"
                                        :value="term.id"
                                    >
                                        {{ term.academic_year }} -
                                        {{ formatSemester(term.semester) }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Program</label
                                >
                                <select
                                    v-model="filterForm.program_id"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    @change="applyFilters"
                                >
                                    <option value="">All programs</option>
                                    <option
                                        v-for="program in programs || []"
                                        :key="program.id"
                                        :value="program.id"
                                    >
                                        {{ program.code }} - {{ program.name }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Year Level</label
                                >
                                <select
                                    v-model="filterForm.year_level"
                                    class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    @change="applyFilters"
                                >
                                    <option value="">All year levels</option>
                                    <option
                                        v-for="level in yearLevelOptions"
                                        :key="level"
                                        :value="level"
                                    >
                                        Year {{ level }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="!hasData" class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-10 text-center text-sm text-gray-500">
                        No data found.
                    </div>
                </div>

                <div v-else class="space-y-6">
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <p class="text-sm text-gray-500">Total</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ stats.total_enrollments }}
                                </p>
                            </div>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <p class="text-sm text-gray-500">Enrolled</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ stats.enrolled }}
                                </p>
                            </div>
                        </div>
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <p class="text-sm text-gray-500">Completed</p>
                                <p class="text-2xl font-semibold text-gray-900">
                                    {{ stats.completed }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4
                                    class="text-base font-semibold text-gray-800"
                                >
                                    Enrollment by Program
                                </h4>
                                <div
                                    v-if="programPieSegments.length"
                                    class="mt-4 grid gap-4 sm:grid-cols-[140px_1fr]"
                                >
                                    <div
                                        class="flex items-center justify-center"
                                    >
                                        <svg
                                            width="120"
                                            height="120"
                                            viewBox="0 0 120 120"
                                            aria-hidden="true"
                                        >
                                            <circle
                                                cx="60"
                                                cy="60"
                                                r="48"
                                                fill="#f3f4f6"
                                            ></circle>
                                            <path
                                                v-for="segment in programPieSegments"
                                                :key="segment.name"
                                                :d="segment.path"
                                                :fill="segment.color"
                                            ></path>
                                            <circle
                                                cx="60"
                                                cy="60"
                                                r="26"
                                                fill="#fff"
                                            ></circle>
                                        </svg>
                                    </div>
                                    <div class="space-y-2">
                                        <div
                                            v-for="segment in programPieSegments"
                                            :key="segment.name"
                                            class="flex items-center justify-between text-sm"
                                        >
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <span
                                                    class="h-3 w-3 rounded-sm"
                                                    :style="{
                                                        backgroundColor:
                                                            segment.color,
                                                    }"
                                                ></span>
                                                <span class="text-gray-700">
                                                    {{ segment.name }}
                                                </span>
                                            </div>
                                            <span
                                                class="font-medium text-gray-900"
                                            >
                                                {{ segment.total }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <p v-else class="mt-6 text-sm text-gray-500">
                                    No data found.
                                </p>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4
                                    class="text-base font-semibold text-gray-800"
                                >
                                    Enrollment by Year Level
                                </h4>
                                <div
                                    v-if="byYearLevel && byYearLevel.length"
                                    class="mt-4"
                                >
                                    <svg
                                        class="h-40 w-full"
                                        viewBox="0 0 360 160"
                                        aria-hidden="true"
                                    >
                                        <g
                                            v-for="(item, index) in byYearLevel"
                                            :key="item.year_level"
                                            :transform="`translate(0, ${index * 24})`"
                                        >
                                            <text x="0" y="14" font-size="10">
                                                Y{{ item.year_level }}
                                            </text>
                                            <rect
                                                x="30"
                                                y="4"
                                                :width="
                                                    (item.total /
                                                        maxYearLevelTotal) *
                                                    260
                                                "
                                                height="12"
                                                fill="#10b981"
                                                rx="2"
                                            ></rect>
                                            <text
                                                :x="
                                                    35 +
                                                    (item.total /
                                                        maxYearLevelTotal) *
                                                        260
                                                "
                                                y="14"
                                                font-size="10"
                                            >
                                                {{ item.total }}
                                            </text>
                                        </g>
                                    </svg>
                                </div>
                                <p v-else class="mt-6 text-sm text-gray-500">
                                    No data found.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-2">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4
                                    class="text-base font-semibold text-gray-800"
                                >
                                    Enrollment Status Breakdown
                                </h4>
                                <div
                                    v-if="byStatus && byStatus.length"
                                    class="mt-4"
                                >
                                    <svg
                                        class="h-36 w-full"
                                        viewBox="0 0 360 140"
                                        aria-hidden="true"
                                    >
                                        <g
                                            v-for="(item, index) in byStatus"
                                            :key="item.status"
                                            :transform="`translate(0, ${index * 24})`"
                                        >
                                            <text x="0" y="14" font-size="10">
                                                {{ item.status.toUpperCase() }}
                                            </text>
                                            <rect
                                                x="110"
                                                y="4"
                                                :width="
                                                    (item.total /
                                                        maxStatusTotal) *
                                                    200
                                                "
                                                height="12"
                                                fill="#f59e0b"
                                                rx="2"
                                            ></rect>
                                            <text
                                                :x="
                                                    115 +
                                                    (item.total /
                                                        maxStatusTotal) *
                                                        200
                                                "
                                                y="14"
                                                font-size="10"
                                            >
                                                {{ item.total }}
                                            </text>
                                        </g>
                                    </svg>
                                </div>
                                <p v-else class="mt-6 text-sm text-gray-500">
                                    No data found.
                                </p>
                            </div>
                        </div>

                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h4
                                    class="text-base font-semibold text-gray-800"
                                >
                                    Enrollment Trend (Last 5 Terms)
                                </h4>
                                <div
                                    v-if="
                                        enrollmentTrend &&
                                        enrollmentTrend.length
                                    "
                                    class="mt-4 space-y-3"
                                >
                                    <div
                                        v-for="item in enrollmentTrend"
                                        :key="`${item.academic_year}-${item.semester}`"
                                        class="flex items-center justify-between rounded-md border border-gray-200 px-4 py-3"
                                    >
                                        <div>
                                            <p
                                                class="text-sm font-medium text-gray-700"
                                            >
                                                {{ item.academic_year }} -
                                                {{
                                                    formatSemester(
                                                        item.semester,
                                                    )
                                                }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Total enrollments
                                            </p>
                                        </div>
                                        <span
                                            class="text-lg font-semibold text-gray-900"
                                        >
                                            {{ item.total }}
                                        </span>
                                    </div>
                                </div>
                                <p v-else class="mt-6 text-sm text-gray-500">
                                    No data found.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
