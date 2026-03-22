<script setup>
import InputError from "@/Components/InputError.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    scheduledSubject: Object,
    canEditMidterm: Boolean,
    canEditFinal: Boolean,
    midtermOpen: Boolean,
    finalOpen: Boolean,
    midtermSubmittedAt: String,
    finalSubmittedAt: String,
});

const activePeriod = ref(null);
const showConfirm = ref(false);
// CMC Grade Codes: DR=Dropped, NA=Never Attended, INC=Incomplete, W=Withdrawn, NG=No Grade
const finalGradeCodes = ["DR", "NA", "INC", "W", "NG"];

const form = useForm({
    grade_type: "",
    grades: [],
});

const enrolledStudents = computed(() => {
    return (props.scheduledSubject?.enrolled_subjects || []).filter(
        (item) => item.status !== "dropped",
    );
});

const canSubmitMidterm = computed(() => {
    return props.canEditMidterm && !props.midtermSubmittedAt;
});

const canSubmitFinal = computed(() => {
    return props.canEditFinal && !props.finalSubmittedAt;
});

const startEditing = (period) => {
    activePeriod.value = period;
    form.grade_type = period;
    form.grades = enrolledStudents.value.map((item) => ({
        enrolled_subject_id: item.id,
        grade: period === "midterm" ? item.midterm_grade : item.final_grade,
        grade_code: period === "final" ? (item.final_grade_code ?? "") : "",
    }));
    form.clearErrors();
};

const cancelEditing = () => {
    activePeriod.value = null;
    form.reset();
    form.clearErrors();
};

const requestConfirm = () => {
    showConfirm.value = true;
};

const submitGrades = () => {
    form.put(route("grades.update", props.scheduledSubject.id), {
        onSuccess: () => {
            showConfirm.value = false;
            activePeriod.value = null;
            form.reset();
        },
        onFinish: () => {
            showConfirm.value = false;
        },
    });
};

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

    // Incomplete - amber/warning color
    if (["INC"].includes(normalized)) {
        return "bg-amber-100 text-amber-800";
    }

    // Withdrawn, Dropped, Never Attended - muted/gray color
    if (["DR", "W", "NA"].includes(normalized)) {
        return "bg-slate-100 text-slate-700";
    }

    // No Grade - neutral
    if (["NG"].includes(normalized)) {
        return "bg-gray-100 text-gray-700";
    }

    // Numeric grades
    const numValue = Number(normalized);
    if (!Number.isNaN(numValue) && numValue > 0) {
        if (numValue <= 3.0) {
            return "bg-emerald-100 text-emerald-800";
        } else if (numValue <= 4.0) {
            return "bg-yellow-100 text-yellow-800";
        } else {
            return "bg-rose-100 text-rose-800";
        }
    }

    return "bg-gray-100 text-gray-700";
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Submit Grades" />

        <template #header>
            <h2
                class="text-lg font-semibold leading-tight text-[#1f7fa3] sm:text-xl"
            >
                Grade Submission
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex flex-col gap-2">
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
                                Block:
                                <span class="font-medium">
                                    {{ scheduledSubject.block?.code || "N/A" }}
                                </span>
                                • Schedule:
                                <span class="font-medium">
                                    {{ scheduledSubject.day }}
                                    {{ scheduledSubject.time_start }} -
                                    {{ scheduledSubject.time_end }}
                                </span>
                            </p>
                            <p class="text-xs text-gray-500">
                                Midterm:
                                <span
                                    class="font-medium"
                                    :class="
                                        midtermOpen
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    {{
                                        midtermSubmittedAt
                                            ? "Submitted"
                                            : midtermOpen
                                              ? "Open"
                                              : "Closed"
                                    }}
                                </span>
                                • Final:
                                <span
                                    class="font-medium"
                                    :class="
                                        finalOpen
                                            ? 'text-green-600'
                                            : 'text-gray-500'
                                    "
                                >
                                    {{
                                        finalSubmittedAt
                                            ? "Submitted"
                                            : finalOpen
                                              ? "Open"
                                              : "Closed"
                                    }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-4">
                        <div
                            class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
                        >
                            <h4 class="text-lg font-semibold text-gray-900">
                                Enrolled Students
                            </h4>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-if="canSubmitMidterm"
                                    @click="startEditing('midterm')"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Submit Midterm Grades
                                </button>
                                <button
                                    v-if="canSubmitFinal"
                                    @click="startEditing('final')"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                >
                                    Submit Final Grades
                                </button>
                                <button
                                    v-if="activePeriod"
                                    @click="cancelEditing"
                                    class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="!enrolledStudents.length"
                            class="rounded-lg border border-dashed p-8 text-center text-gray-500"
                        >
                            No students enrolled in this class schedule.
                        </div>

                        <div v-else class="overflow-x-auto">
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
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="(
                                            item, index
                                        ) in enrolledStudents"
                                        :key="item.id"
                                    >
                                        <td class="px-4 py-3 text-sm">
                                            <p
                                                class="font-medium text-gray-900"
                                            >
                                                {{
                                                    item.enrollment?.student
                                                        ?.user?.first_name
                                                }}
                                                {{
                                                    item.enrollment?.student
                                                        ?.user?.last_name
                                                }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{
                                                    item.enrollment?.student
                                                        ?.user?.official_id
                                                }}
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div
                                                v-if="
                                                    activePeriod === 'midterm'
                                                "
                                            >
                                                <input
                                                    v-model="
                                                        form.grades[index].grade
                                                    "
                                                    type="number"
                                                    min="1"
                                                    max="5"
                                                    step="0.01"
                                                    class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                />
                                                <InputError
                                                    :message="
                                                        form.errors[
                                                            `grades.${index}.grade`
                                                        ]
                                                    "
                                                    class="mt-1"
                                                />
                                            </div>
                                            <span v-else>
                                                <template
                                                    v-if="
                                                        isNumericGradeDisplay(
                                                            getGradeDisplay(
                                                                item,
                                                                'midterm',
                                                            ),
                                                        )
                                                    "
                                                >
                                                    {{
                                                        getGradeDisplay(
                                                            item,
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
                                                                item,
                                                                'midterm',
                                                            ),
                                                        )
                                                    "
                                                >
                                                    {{
                                                        getGradeDisplay(
                                                            item,
                                                            "midterm",
                                                        )
                                                    }}
                                                </span>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <div
                                                v-if="activePeriod === 'final'"
                                            >
                                                <input
                                                    v-model="
                                                        form.grades[index].grade
                                                    "
                                                    type="number"
                                                    min="1"
                                                    max="5"
                                                    step="0.01"
                                                    class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    @input="
                                                        () => {
                                                            if (
                                                                form.grades[
                                                                    index
                                                                ].grade !==
                                                                    null &&
                                                                form.grades[
                                                                    index
                                                                ].grade !== ''
                                                            ) {
                                                                form.grades[
                                                                    index
                                                                ].grade_code =
                                                                    '';
                                                            }
                                                        }
                                                    "
                                                />
                                                <p
                                                    class="mt-1 text-xs text-gray-500"
                                                >
                                                    Numeric: 1.00 to 5.00
                                                </p>
                                                <select
                                                    v-model="
                                                        form.grades[index]
                                                            .grade_code
                                                    "
                                                    class="mt-2 w-28 rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    @change="
                                                        () => {
                                                            if (
                                                                form.grades[
                                                                    index
                                                                ].grade_code
                                                            ) {
                                                                form.grades[
                                                                    index
                                                                ].grade = null;
                                                            }
                                                        }
                                                    "
                                                >
                                                    <option value="">
                                                        Numeric
                                                    </option>
                                                    <option
                                                        v-for="code in finalGradeCodes"
                                                        :key="code"
                                                        :value="code"
                                                    >
                                                        {{ code }}
                                                    </option>
                                                </select>
                                                <p
                                                    class="mt-1 text-xs text-gray-500"
                                                >
                                                    Or choose a final code.
                                                </p>
                                                <InputError
                                                    :message="
                                                        form.errors[
                                                            `grades.${index}.grade`
                                                        ]
                                                    "
                                                    class="mt-1"
                                                />
                                                <InputError
                                                    :message="
                                                        form.errors[
                                                            `grades.${index}.grade_code`
                                                        ]
                                                    "
                                                    class="mt-1"
                                                />
                                            </div>
                                            <span v-else>
                                                <template
                                                    v-if="
                                                        isNumericGradeDisplay(
                                                            getGradeDisplay(
                                                                item,
                                                                'final',
                                                            ),
                                                        )
                                                    "
                                                >
                                                    {{
                                                        getGradeDisplay(
                                                            item,
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
                                                                item,
                                                                'final',
                                                            ),
                                                        )
                                                    "
                                                >
                                                    {{
                                                        getGradeDisplay(
                                                            item,
                                                            "final",
                                                        )
                                                    }}
                                                </span>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-if="activePeriod" class="flex justify-end">
                            <PrimaryButton @click="requestConfirm">
                                Submit
                                {{
                                    activePeriod === "midterm"
                                        ? "Midterm"
                                        : "Final"
                                }}
                                Grades
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="showConfirm" @close="showConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    Confirm Submission
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Do you confirm that all grades entered are accurate?
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <SecondaryButton @click="showConfirm = false">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="submitGrades">
                        Confirm
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
