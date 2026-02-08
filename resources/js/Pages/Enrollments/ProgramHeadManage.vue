<script setup>
/**
 * ProgramHeadManage.vue
 *
 * Manages blocks, subject schedules, and student enrollments for Program Heads
 *
 * Data Flow:
 * 1. Display list of blocks for the program
 * 2. When viewing a block, fetch available curriculum subjects from the backend
 * 3. Filter subjects based on: curriculum year level + current term semester
 * 4. Allow program head to schedule subjects for a block
 *
 * Year Level Calculation:
 * - Block has admission_year (e.g., 2024)
 * - Current year = new Date().getFullYear()
 * - Years since admission = currentYear - admission_year
 * - Current year level = Math.min(yearsSinceAdmission + 1, 4)
 * - Example: Admitted in 2024, current year 2026 → yearsSinceAdmission = 2 → year level = 3
 */

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    blocks: Object,
    programs: Array,
    activeTerm: Object,
    instructors: Array,
    filters: Object,
});

// Modals
const showCreateBlock = ref(false);
const showEditBlock = ref(false);
const showDeleteBlock = ref(false);
const showCreateSchedule = ref(false);
const showEditSchedule = ref(false);
const showDeleteSchedule = ref(false);
const showViewStudents = ref(false);

const selectedBlock = ref(null);
const selectedSchedule = ref(null);
const availableSubjects = ref([]); // Curriculum subjects from the active curriculum

// Forms
const blockForm = useForm({
    code: "",
    program_id: props.programs[0]?.id || null,
    admission_year: new Date().getFullYear(),
    status: "active",
});

const scheduleForm = useForm({
    curriculum_subject_id: null,
    day: "",
    room: "",
    time_start: "",
    time_end: "",
    instructor_id: null,
    academic_term_id: props.activeTerm?.id || null,
    block_id: null,
});

// View Block Details
const viewBlock = (block) => {
    selectedBlock.value = block;

    // Reset available subjects
    availableSubjects.value = [];

    // Fetch available subjects for this block
    fetchAvailableSubjects(block.id);
};

const fetchAvailableSubjects = async (blockId) => {
    try {
        console.log(`[BlockManage] Fetching subjects for block ${blockId}`);

        const response = await fetch(
            `/api/blocks/${blockId}/available-subjects`,
        );

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log(`[BlockManage] Received subjects:`, data);

        availableSubjects.value = data.subjects || [];

        if (availableSubjects.value.length === 0) {
            console.warn(
                `[BlockManage] No subjects found for block ${blockId}`,
            );
        }
    } catch (error) {
        console.error(
            `[BlockManage] Error fetching subjects for block ${blockId}:`,
            error,
        );
        availableSubjects.value = [];
    }
};

// Block CRUD
const openCreateBlock = () => {
    blockForm.reset();
    blockForm.program_id = props.programs[0]?.id || null;
    showCreateBlock.value = true;
};

const createBlock = () => {
    blockForm.post(route("blocks.store"), {
        onSuccess: () => {
            showCreateBlock.value = false;
            blockForm.reset();
        },
    });
};

const openEditBlock = (block) => {
    selectedBlock.value = block;
    blockForm.code = block.code;
    blockForm.program_id = block.program_id;
    blockForm.admission_year = block.admission_year;
    blockForm.status = block.status;
    showEditBlock.value = true;
};

const updateBlock = () => {
    blockForm.put(route("blocks.update", selectedBlock.value.id), {
        onSuccess: () => {
            showEditBlock.value = false;
            selectedBlock.value = null;
        },
    });
};

const confirmDeleteBlock = (block) => {
    selectedBlock.value = block;
    showDeleteBlock.value = true;
};

const deleteBlock = () => {
    router.delete(route("blocks.destroy", selectedBlock.value.id), {
        onSuccess: () => {
            showDeleteBlock.value = false;
            selectedBlock.value = null;
        },
    });
};

// Schedule CRUD
const openCreateSchedule = (block) => {
    scheduleForm.reset();
    scheduleForm.block_id = block.id;
    scheduleForm.academic_term_id = props.activeTerm?.id || null;
    selectedBlock.value = block;
    showCreateSchedule.value = true;
};

const createSchedule = () => {
    scheduleForm.post(route("scheduled-subjects.store"), {
        onSuccess: () => {
            showCreateSchedule.value = false;
            scheduleForm.reset();
            router.reload({ only: ["blocks"] });
        },
    });
};

const openEditSchedule = (schedule, block) => {
    selectedSchedule.value = schedule;
    selectedBlock.value = block;
    scheduleForm.day = schedule.day;
    scheduleForm.room = schedule.room;
    scheduleForm.time_start = schedule.time_start;
    scheduleForm.time_end = schedule.time_end;
    scheduleForm.instructor_id = schedule.instructor_id;
    showEditSchedule.value = true;
};

const updateSchedule = () => {
    scheduleForm.put(
        route("scheduled-subjects.update", selectedSchedule.value.id),
        {
            onSuccess: () => {
                showEditSchedule.value = false;
                selectedSchedule.value = null;
                router.reload({ only: ["blocks"] });
            },
        },
    );
};

const confirmDeleteSchedule = (schedule) => {
    selectedSchedule.value = schedule;
    showDeleteSchedule.value = true;
};

const deleteSchedule = () => {
    router.delete(
        route("scheduled-subjects.destroy", selectedSchedule.value.id),
        {
            onSuccess: () => {
                showDeleteSchedule.value = false;
                selectedSchedule.value = null;
                router.reload({ only: ["blocks"] });
            },
        },
    );
};

// View Students
const viewStudents = (block) => {
    selectedBlock.value = block;
    showViewStudents.value = true;
};

// Get schedules for active term
const getActiveTermSchedules = (block) => {
    if (!block.scheduled_subjects || !props.activeTerm) return [];
    return block.scheduled_subjects.filter(
        (s) => s.academic_term && s.academic_term.id === props.activeTerm.id,
    );
};

// Get the academic year start year (e.g., "2025-2026" -> 2025)
const getAcademicYearStartYear = () => {
    const academicYear = props.activeTerm?.academic_year;
    if (!academicYear) return new Date().getFullYear();

    const match = String(academicYear).match(/(\d{4})\s*-\s*(\d{4})/);
    if (match) return parseInt(match[1], 10);

    const year = parseInt(academicYear, 10);
    return Number.isNaN(year) ? new Date().getFullYear() : year;
};

// Get year level info for a block (uses program duration and academic year)
const getBlockYearInfo = (block) => {
    const startYear = getAcademicYearStartYear();
    const yearsSinceAdmission = startYear - block.admission_year;
    const rawYearLevel = Math.max(1, yearsSinceAdmission + 1);
    const durationYears = block.program?.duration_years || 4;

    return {
        yearLevel: rawYearLevel,
        durationYears,
        isGraduated: rawYearLevel > durationYears,
    };
};

// Get required subjects based on block year level
const getRequiredSubjects = (block) => {
    if (!block || !availableSubjects.value.length) {
        console.warn(
            `[BlockManage] getRequiredSubjects: No block or subjects. Block:`,
            block?.id,
            "Subjects:",
            availableSubjects.value.length,
        );
        return [];
    }

    const { yearLevel, durationYears, isGraduated } = getBlockYearInfo(block);
    if (isGraduated) return [];

    // Get semester from active term
    const currentSemester = props.activeTerm?.semester || "first";

    console.log(`[BlockManage] Filtering subjects for block ${block.code}:`, {
        admissionYear: block.admission_year,
        academicYearStart: getAcademicYearStartYear(),
        calculatedYearLevel: yearLevel,
        programDurationYears: durationYears,
        currentSemester,
        totalAvailableSubjects: availableSubjects.value.length,
    });

    // Filter curriculum subjects by year level and semester
    const filtered = availableSubjects.value.filter((s) => {
        const matches =
            s.year_level === yearLevel && s.semester === currentSemester;
        if (!matches) {
            console.log(
                `[BlockManage] Filtered out: ${s.subject_code} (year: ${s.year_level}, sem: ${s.semester})`,
            );
        }
        return matches;
    });

    console.log(
        `[BlockManage] Final filtered subjects:`,
        filtered.length,
        filtered,
    );
    return filtered;
};

// Get year level label
const getYearLevelLabel = (block) => {
    const { yearLevel, isGraduated } = getBlockYearInfo(block);
    if (isGraduated) return "Graduated";

    const suffixes = ["st", "nd", "rd", "th"];
    return `${yearLevel}${suffixes[yearLevel - 1] || "th"} Year`;
};

// Group blocks by admission year (newest first)
const getGroupedBlocks = () => {
    if (!props.blocks.data) return [];

    const grouped = {};
    props.blocks.data.forEach((block) => {
        if (!grouped[block.admission_year]) {
            grouped[block.admission_year] = [];
        }
        grouped[block.admission_year].push(block);
    });

    const yearEntries = Object.keys(grouped).map((year) => {
        const blocks = grouped[year];
        const yearLevel = blocks.length
            ? getBlockYearInfo(blocks[0]).yearLevel
            : 0;
        return {
            admissionYear: Number(year),
            yearLevel,
            blocks,
        };
    });

    // Sort so newest admission years are on top
    yearEntries.sort((a, b) => b.admissionYear - a.admissionYear);

    return yearEntries;
};

// Generate a suggested block code (server will generate final code)
const getAutoBlockCode = () => {
    const program = props.programs.find((p) => p.id === blockForm.program_id);
    if (!program || !blockForm.admission_year) return "—";

    const base = `${program.code}-${blockForm.admission_year}-`;
    const existingLetters = (props.blocks?.data || [])
        .filter(
            (b) =>
                b.program_id === program.id &&
                b.admission_year === blockForm.admission_year &&
                b.code &&
                b.code.startsWith(base),
        )
        .map((b) => b.code.slice(base.length).toUpperCase())
        .filter(Boolean);

    const used = new Set(existingLetters.map((l) => l[0]));
    let letter = "A";
    for (let i = 0; i < 26; i += 1) {
        const candidate = String.fromCharCode(65 + i);
        if (!used.has(candidate)) {
            letter = candidate;
            break;
        }
    }

    return `${base}${letter}`;
};
</script>

<template>
    <Head title="Manage Enrollment - Program Head" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Manage Enrollment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Header -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h1 class="text-3xl font-bold text-gray-900">
                                    Manage Enrollment
                                </h1>
                                <p class="mt-2 text-gray-600">
                                    Manage blocks, schedules, and instructor
                                    assignments
                                </p>
                                <p
                                    v-if="activeTerm"
                                    class="mt-1 text-sm text-indigo-600 font-medium"
                                >
                                    Active Term:
                                    {{ activeTerm.academic_year }} -
                                    {{
                                        activeTerm.semester
                                            .charAt(0)
                                            .toUpperCase() +
                                        activeTerm.semester.slice(1)
                                    }}
                                </p>
                            </div>
                            <button
                                @click="openCreateBlock"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                + Create Block
                            </button>
                        </div>
                    </div>
                </div>

                <!-- No Active Term Warning -->
                <div
                    v-if="!activeTerm"
                    class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"
                >
                    <p class="text-yellow-800">
                        ⚠️ No active academic term. Please contact the
                        registrar.
                    </p>
                </div>

                <!-- Blocks List - Grouped by Admission Year -->
                <div
                    v-else-if="blocks.data && blocks.data.length > 0"
                    class="space-y-8"
                >
                    <!-- Group by Admission Year -->
                    <div
                        v-for="entry in getGroupedBlocks()"
                        :key="entry.admissionYear"
                        class="space-y-4"
                    >
                        <!-- Year Group Header -->
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div
                                    class="w-full border-t border-gray-300"
                                ></div>
                            </div>
                            <div class="relative flex justify-start">
                                <span class="bg-white px-4 py-2 pr-6">
                                    <span
                                        class="text-lg font-bold text-gray-900"
                                    >
                                        Admission Year
                                        {{ entry.admissionYear }}
                                    </span>
                                    <span class="ml-3 text-sm text-gray-500">
                                        AY
                                        {{ activeTerm?.academic_year || "N/A" }}
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Blocks in this Year Group -->
                        <div
                            class="space-y-4 pl-4 border-l-4 border-indigo-300"
                        >
                            <div
                                v-for="block in entry.blocks"
                                :key="block.id"
                                class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition"
                            >
                                <!-- Block Header -->
                                <div
                                    class="p-4 border-b border-gray-200 bg-gray-50"
                                >
                                    <div
                                        class="flex justify-between items-start"
                                    >
                                        <div class="flex-1">
                                            <div
                                                class="flex items-center gap-3"
                                            >
                                                <h3
                                                    class="text-lg font-bold text-gray-900"
                                                >
                                                    {{ block.code }}
                                                </h3>
                                                <span
                                                    class="text-xs font-semibold px-2 py-1 rounded-full"
                                                    :class="{
                                                        'bg-slate-200 text-slate-800':
                                                            getYearLevelLabel(
                                                                block,
                                                            ) === 'Graduated',
                                                        'bg-indigo-100 text-indigo-700':
                                                            getYearLevelLabel(
                                                                block,
                                                            ) !== 'Graduated',
                                                    }"
                                                >
                                                    {{
                                                        getYearLevelLabel(block)
                                                    }}
                                                </span>
                                                <span
                                                    class="text-xs font-semibold px-2 py-1 rounded-full"
                                                    :class="{
                                                        'bg-green-100 text-green-700':
                                                            block.status ===
                                                            'active',
                                                        'bg-yellow-100 text-yellow-700':
                                                            block.status ===
                                                            'inactive',
                                                        'bg-gray-100 text-gray-700':
                                                            block.status ===
                                                            'graduated',
                                                    }"
                                                >
                                                    {{ block.status }}
                                                </span>
                                            </div>
                                            <p
                                                class="text-sm text-gray-600 mt-1"
                                            >
                                                {{ block.program?.name }} |
                                                Students:
                                                {{ block.students_count }}
                                            </p>
                                        </div>
                                        <div class="flex gap-2">
                                            <button
                                                @click="viewBlock(block)"
                                                class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                                            >
                                                View Details
                                            </button>
                                            <button
                                                @click="openEditBlock(block)"
                                                class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                @click="
                                                    confirmDeleteBlock(block)
                                                "
                                                class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Block Content -->
                                <div
                                    v-if="selectedBlock?.id === block.id"
                                    class="p-4"
                                >
                                    <!-- Required Subjects Section -->
                                    <div class="mb-6">
                                        <div
                                            class="flex justify-between items-center mb-3"
                                        >
                                            <h4
                                                class="font-semibold text-gray-800"
                                            >
                                                Required Subjects for Active
                                                Term
                                            </h4>
                                            <button
                                                @click="
                                                    openCreateSchedule(block)
                                                "
                                                class="px-3 py-1 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700"
                                            >
                                                + Add Schedule
                                            </button>
                                        </div>

                                        <div
                                            v-if="
                                                getRequiredSubjects(block)
                                                    .length > 0
                                            "
                                            class="grid grid-cols-1 md:grid-cols-2 gap-3"
                                        >
                                            <div
                                                v-for="subject in getRequiredSubjects(
                                                    block,
                                                )"
                                                :key="subject.id"
                                                class="p-3 border rounded-lg bg-gray-50"
                                            >
                                                <p class="font-medium text-sm">
                                                    {{ subject.subject_code }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-600"
                                                >
                                                    {{ subject.subject_title }}
                                                </p>
                                                <p
                                                    class="text-xs text-gray-500 mt-1"
                                                >
                                                    Units: {{ subject.units }} |
                                                    Year
                                                    {{ subject.year_level }} -
                                                    {{ subject.semester }}
                                                </p>
                                            </div>
                                        </div>
                                        <p v-else class="text-gray-500 text-sm">
                                            No required subjects found
                                        </p>
                                    </div>

                                    <!-- Existing Schedules -->
                                    <div class="mb-6">
                                        <h4
                                            class="font-semibold text-gray-800 mb-3"
                                        >
                                            Created Schedules
                                        </h4>
                                        <div
                                            v-if="
                                                getActiveTermSchedules(block)
                                                    .length > 0
                                            "
                                            class="space-y-3"
                                        >
                                            <div
                                                v-for="schedule in getActiveTermSchedules(
                                                    block,
                                                )"
                                                :key="schedule.id"
                                                class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50"
                                            >
                                                <div class="flex-1">
                                                    <p class="font-medium">
                                                        {{
                                                            schedule
                                                                .curriculum_subject
                                                                ?.subject?.code
                                                        }}
                                                        -
                                                        {{
                                                            schedule
                                                                .curriculum_subject
                                                                ?.subject?.title
                                                        }}
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-600 mt-1"
                                                    >
                                                        {{ schedule.day }} |
                                                        {{
                                                            schedule.time_start
                                                        }}
                                                        -
                                                        {{ schedule.time_end }}
                                                        | Room
                                                        {{ schedule.room }}
                                                    </p>
                                                    <p
                                                        class="text-sm text-gray-600"
                                                    >
                                                        Instructor:
                                                        {{
                                                            schedule.instructor
                                                                ?.user
                                                                ?.first_name
                                                        }}
                                                        {{
                                                            schedule.instructor
                                                                ?.user
                                                                ?.last_name ||
                                                            "Not assigned"
                                                        }}
                                                    </p>
                                                </div>
                                                <div class="flex gap-2">
                                                    <button
                                                        @click="
                                                            openEditSchedule(
                                                                schedule,
                                                                block,
                                                            )
                                                        "
                                                        class="px-3 py-1 text-sm text-blue-600 hover:text-blue-800"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        @click="
                                                            confirmDeleteSchedule(
                                                                schedule,
                                                            )
                                                        "
                                                        class="px-3 py-1 text-sm text-red-600 hover:text-red-800"
                                                    >
                                                        Drop
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <p v-else class="text-gray-500 text-sm">
                                            No schedules created for this term
                                        </p>
                                    </div>

                                    <!-- Students Section -->
                                    <div>
                                        <div
                                            class="flex justify-between items-center mb-3"
                                        >
                                            <h4
                                                class="font-semibold text-gray-800"
                                            >
                                                Students
                                            </h4>
                                            <button
                                                @click="viewStudents(block)"
                                                class="px-3 py-1 text-sm text-indigo-600 hover:text-indigo-800"
                                            >
                                                View All ({{
                                                    block.students_count
                                                }})
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-else
                    class="bg-white rounded-lg shadow-sm p-12 text-center"
                >
                    <p class="text-gray-500 mb-4">No blocks found</p>
                    <button
                        @click="openCreateBlock"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                    >
                        Create Your First Block
                    </button>
                </div>

                <!-- CREATE BLOCK MODAL -->
                <div
                    v-if="showCreateBlock"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
                >
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-bold mb-4">Create New Block</h3>
                        <form @submit.prevent="createBlock" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Block Code</label
                                >
                                <input
                                    :value="getAutoBlockCode()"
                                    type="text"
                                    readonly
                                    class="w-full rounded-md border-gray-300 bg-gray-50"
                                />
                                <p class="text-xs text-gray-500 mt-1">
                                    Auto-generated on save.
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Program *</label
                                >
                                <select
                                    v-if="programs.length > 1"
                                    v-model="blockForm.program_id"
                                    required
                                    class="w-full rounded-md border-gray-300"
                                >
                                    <option
                                        v-for="program in programs"
                                        :key="program.id"
                                        :value="program.id"
                                    >
                                        {{ program.name }}
                                    </option>
                                </select>
                                <input
                                    v-else
                                    type="text"
                                    :value="programs[0]?.name || 'N/A'"
                                    readonly
                                    class="w-full rounded-md border-gray-300 bg-gray-50"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Admission Year *</label
                                >
                                <input
                                    v-model="blockForm.admission_year"
                                    type="number"
                                    required
                                    min="2000"
                                    :max="new Date().getFullYear() + 1"
                                    class="w-full rounded-md border-gray-300"
                                />
                            </div>

                            <div class="flex gap-3 justify-end pt-4">
                                <button
                                    type="button"
                                    @click="showCreateBlock = false"
                                    class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="blockForm.processing"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{
                                        blockForm.processing
                                            ? "Creating..."
                                            : "Create Block"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- EDIT BLOCK MODAL -->
                <div
                    v-if="showEditBlock"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
                >
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-bold mb-4">Edit Block</h3>
                        <form @submit.prevent="updateBlock" class="space-y-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Block Code</label
                                >
                                <input
                                    :value="getAutoBlockCode()"
                                    type="text"
                                    readonly
                                    class="w-full rounded-md border-gray-300 bg-gray-50"
                                />
                                <p class="text-xs text-gray-500 mt-1">
                                    Auto-updates when admission year changes.
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Admission Year *</label
                                >
                                <input
                                    v-model="blockForm.admission_year"
                                    type="number"
                                    required
                                    min="2000"
                                    :max="new Date().getFullYear() + 1"
                                    class="w-full rounded-md border-gray-300"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Status *</label
                                >
                                <select
                                    v-model="blockForm.status"
                                    required
                                    class="w-full rounded-md border-gray-300"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="graduated">Graduated</option>
                                </select>
                            </div>

                            <div class="flex gap-3 justify-end pt-4">
                                <button
                                    type="button"
                                    @click="showEditBlock = false"
                                    class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="blockForm.processing"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- DELETE BLOCK MODAL -->
                <div
                    v-if="showDeleteBlock"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
                >
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-bold mb-4">Confirm Delete</h3>
                        <p class="mb-6">
                            Are you sure you want to delete this block
                            completely?
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="showDeleteBlock = false"
                                class="px-4 py-2 bg-gray-200 rounded-md"
                            >
                                Cancel
                            </button>
                            <button
                                @click="deleteBlock"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CREATE SCHEDULE MODAL -->
                <div
                    v-if="showCreateSchedule"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto"
                >
                    <div class="bg-white rounded-lg p-6 max-w-lg w-full my-8">
                        <h3 class="text-lg font-bold mb-4">
                            Add Subject Schedule
                        </h3>
                        <form
                            @submit.prevent="createSchedule"
                            class="space-y-4"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Subject *</label
                                >
                                <select
                                    v-model="scheduleForm.curriculum_subject_id"
                                    required
                                    class="w-full rounded-md border-gray-300"
                                >
                                    <option value="">Select subject...</option>
                                    <option
                                        v-for="subject in getRequiredSubjects(
                                            selectedBlock,
                                        )"
                                        :key="subject.id"
                                        :value="subject.id"
                                    >
                                        {{ subject.subject_code }} -
                                        {{ subject.subject_title }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Day *</label
                                >
                                <select
                                    v-model="scheduleForm.day"
                                    required
                                    class="w-full rounded-md border-gray-300"
                                >
                                    <option value="">Select day...</option>
                                    <option value="MWF">MWF</option>
                                    <option value="TTH">TTH</option>
                                    <option value="MW">MW</option>
                                    <option value="MONDAY">Monday</option>
                                    <option value="TUESDAY">Tuesday</option>
                                    <option value="WEDNESDAY">Wednesday</option>
                                    <option value="THURSDAY">Thursday</option>
                                    <option value="FRIDAY">Friday</option>
                                    <option value="SATURDAY">Saturday</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Start Time *</label
                                    >
                                    <input
                                        v-model="scheduleForm.time_start"
                                        type="time"
                                        required
                                        class="w-full rounded-md border-gray-300"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >End Time *</label
                                    >
                                    <input
                                        v-model="scheduleForm.time_end"
                                        type="time"
                                        required
                                        :min="
                                            scheduleForm.time_start || undefined
                                        "
                                        class="w-full rounded-md border-gray-300"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Room *</label
                                >
                                <input
                                    v-model="scheduleForm.room"
                                    type="text"
                                    required
                                    placeholder="e.g., Room 301"
                                    class="w-full rounded-md border-gray-300"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Instructor (Optional)</label
                                >
                                <select
                                    v-model="scheduleForm.instructor_id"
                                    class="w-full rounded-md border-gray-300"
                                >
                                    <option :value="null">Not assigned</option>
                                    <option
                                        v-for="instructor in instructors"
                                        :key="instructor.id"
                                        :value="instructor.id"
                                    >
                                        {{ instructor.user?.first_name }}
                                        {{ instructor.user?.last_name }}
                                    </option>
                                </select>
                            </div>

                            <div
                                v-if="scheduleForm.errors"
                                class="text-red-600 text-sm"
                            >
                                <p
                                    v-for="(error, key) in scheduleForm.errors"
                                    :key="key"
                                >
                                    {{ error }}
                                </p>
                            </div>

                            <div class="flex gap-3 justify-end pt-4">
                                <button
                                    type="button"
                                    @click="showCreateSchedule = false"
                                    class="px-4 py-2 bg-gray-200 rounded-md"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="scheduleForm.processing"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    {{
                                        scheduleForm.processing
                                            ? "Creating..."
                                            : "Create Schedule"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- EDIT SCHEDULE MODAL -->
                <div
                    v-if="showEditSchedule"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto"
                >
                    <div class="bg-white rounded-lg p-6 max-w-lg w-full my-8">
                        <h3 class="text-lg font-bold mb-4">Edit Schedule</h3>
                        <form
                            @submit.prevent="updateSchedule"
                            class="space-y-4"
                        >
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Day *</label
                                >
                                <select
                                    v-model="scheduleForm.day"
                                    required
                                    class="w-full rounded-md border-gray-300"
                                >
                                    <option value="">Select day...</option>
                                    <option value="MWF">MWF</option>
                                    <option value="TTH">TTH</option>
                                    <option value="MW">MW</option>
                                    <option value="MONDAY">Monday</option>
                                    <option value="TUESDAY">Tuesday</option>
                                    <option value="WEDNESDAY">Wednesday</option>
                                    <option value="THURSDAY">Thursday</option>
                                    <option value="FRIDAY">Friday</option>
                                    <option value="SATURDAY">Saturday</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >Start Time *</label
                                    >
                                    <input
                                        v-model="scheduleForm.time_start"
                                        type="time"
                                        required
                                        class="w-full rounded-md border-gray-300"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-1"
                                        >End Time *</label
                                    >
                                    <input
                                        v-model="scheduleForm.time_end"
                                        type="time"
                                        required
                                        :min="
                                            scheduleForm.time_start || undefined
                                        "
                                        class="w-full rounded-md border-gray-300"
                                    />
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Room *</label
                                >
                                <input
                                    v-model="scheduleForm.room"
                                    type="text"
                                    required
                                    class="w-full rounded-md border-gray-300"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Instructor</label
                                >
                                <select
                                    v-model="scheduleForm.instructor_id"
                                    class="w-full rounded-md border-gray-300"
                                >
                                    <option :value="null">Not assigned</option>
                                    <option
                                        v-for="instructor in instructors"
                                        :key="instructor.id"
                                        :value="instructor.id"
                                    >
                                        {{ instructor.user?.first_name }}
                                        {{ instructor.user?.last_name }}
                                    </option>
                                </select>
                            </div>

                            <div class="flex gap-3 justify-end pt-4">
                                <button
                                    type="button"
                                    @click="showEditSchedule = false"
                                    class="px-4 py-2 bg-gray-200 rounded-md"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="scheduleForm.processing"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- DELETE SCHEDULE MODAL -->
                <div
                    v-if="showDeleteSchedule"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
                >
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-bold mb-4">
                            Confirm Drop Schedule
                        </h3>
                        <p class="mb-6">
                            Do you confirm to drop this subject schedule for
                            this block enrollment?
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="showDeleteSchedule = false"
                                class="px-4 py-2 bg-gray-200 rounded-md"
                            >
                                Cancel
                            </button>
                            <button
                                @click="deleteSchedule"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            >
                                Drop Schedule
                            </button>
                        </div>
                    </div>
                </div>

                <!-- VIEW STUDENTS MODAL -->
                <div
                    v-if="showViewStudents"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto"
                >
                    <div
                        class="bg-white rounded-lg p-6 max-w-4xl w-full my-8 max-h-[90vh] overflow-y-auto"
                    >
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-bold text-gray-900">
                                Students in {{ selectedBlock?.code }}
                            </h3>
                            <button
                                @click="showViewStudents = false"
                                class="text-gray-500 hover:text-gray-700 text-xl"
                            >
                                ✕
                            </button>
                        </div>

                        <div
                            v-if="selectedBlock?.students?.length"
                            class="overflow-x-auto"
                        >
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                                        >
                                            Student ID
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                                        >
                                            Name
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                                        >
                                            Email
                                        </th>
                                        <th
                                            class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase"
                                        >
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <tr
                                        v-for="student in selectedBlock.students"
                                        :key="student.id"
                                        class="hover:bg-gray-50"
                                    >
                                        <td class="px-4 py-3 text-sm">
                                            {{
                                                student.user?.official_id || "—"
                                            }}
                                        </td>
                                        <td
                                            class="px-4 py-3 text-sm font-medium"
                                        >
                                            {{ student.user?.first_name }}
                                            {{ student.user?.last_name }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            {{ student.user?.email }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span
                                                class="px-2 py-1 rounded-full text-xs font-medium"
                                                :class="{
                                                    'bg-green-100 text-green-700':
                                                        student.status ===
                                                        'regular',
                                                    'bg-yellow-100 text-yellow-700':
                                                        student.status ===
                                                        'irregular',
                                                }"
                                            >
                                                {{ student.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="text-center py-12 text-gray-500">
                            No students enrolled in this block.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
