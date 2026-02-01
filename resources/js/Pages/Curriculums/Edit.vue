<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    curriculum: Object,
    programs: Array,
    subjects: Array,
});

const page = usePage();
const isProgramHead = computed(
    () => page.props.auth?.user?.role === "program_head",
);

const form = ref({
    name: props.curriculum.name,
    year_effective: props.curriculum.year_effective,
    program_id: props.curriculum.program_id,
    is_active: props.curriculum.is_active,
    subjects: props.curriculum.curriculum_subjects.map((cs) => ({
        id: cs.id,
        subject_id: cs.subject_id,
        year_level: cs.year_level,
        semester: cs.semester,
        course_type: cs.course_type,
        has_laboratory: cs.has_laboratory,
        prerequisites: cs.prerequisites.map((p) => p.subject_id),
    })),
});

const errors = ref({});
const isSubmitting = ref(false);
const showAddSubjectForm = ref(false);
const editingSubjectIndex = ref(null);
const showEditSubjectForm = ref(false);
const newSubjectData = ref({
    subject_id: "",
    year_level: 1,
    semester: "first",
    course_type: "major",
    has_laboratory: false,
    prerequisites: [],
});

const editSubjectData = ref({
    subject_id: "",
    year_level: 1,
    semester: "first",
    course_type: "major",
    has_laboratory: false,
    prerequisites: [],
});

const resetAddSubjectForm = () => {
    newSubjectData.value = {
        subject_id: "",
        year_level: 1,
        semester: "first",
        course_type: "major",
        has_laboratory: false,
        prerequisites: [],
    };
    showAddSubjectForm.value = false;
};

const resetEditSubjectForm = () => {
    editingSubjectIndex.value = null;
    showEditSubjectForm.value = false;
    editSubjectData.value = {
        subject_id: "",
        year_level: 1,
        semester: "first",
        course_type: "major",
        has_laboratory: false,
        prerequisites: [],
    };
};

const addSubject = () => {
    if (!newSubjectData.value.subject_id) {
        alert("Please select a subject");
        return;
    }

    form.value.subjects.push({
        id: null,
        ...newSubjectData.value,
    });

    resetAddSubjectForm();
    alert("You added a new subject!");
};

const startEditSubject = (index) => {
    editingSubjectIndex.value = index;
    editSubjectData.value = { ...form.value.subjects[index] };
    showEditSubjectForm.value = true;
};

const saveEditSubject = () => {
    if (!editSubjectData.value.subject_id) {
        alert("Please select a subject");
        return;
    }

    form.value.subjects[editingSubjectIndex.value] = {
        ...editSubjectData.value,
    };

    resetEditSubjectForm();
    alert("You updated a curriculum subject!");
};

const removeSubject = (index) => {
    if (confirm("Are you sure you want to remove this subject?")) {
        form.value.subjects.splice(index, 1);
        alert("You removed a subject!");
    }
};

const submitForm = () => {
    isSubmitting.value = true;
    router.put(route("curriculums.update", props.curriculum.id), form.value, {
        onError: (pageErrors) => {
            errors.value = pageErrors;
            isSubmitting.value = false;
        },
        onSuccess: () => {
            isSubmitting.value = false;
        },
    });
};

const getSubjectTitle = (subjectId) => {
    const subject = props.subjects.find((s) => s.id == subjectId);
    return subject ? subject.title : "Select a subject";
};

const getSubjectCode = (subjectId) => {
    const subject = props.subjects.find((s) => s.id == subjectId);
    return subject ? subject.code : "---";
};

const getSemesterLabel = (semester) => {
    const labels = {
        first: "1st Semester",
        second: "2nd Semester",
        summer: "Summer",
    };
    return labels[semester] || semester;
};

const getCourseTypeLabel = (type) => {
    const labels = {
        major: "Major",
        elective: "Elective",
        minor: "Minor",
    };
    return labels[type] || type;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit Curriculum - ${curriculum.name}`" />

        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-6">
                            Edit Curriculum: {{ curriculum.name }}
                        </h2>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Basic Information
                                </h3>

                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Program
                                        <span class="text-red-500"
                                            >*</span
                                        ></label
                                    >
                                    <div v-if="isProgramHead" class="mt-1">
                                        <p class="px-3 py-2 bg-gray-50 rounded">
                                            {{
                                                props.programs &&
                                                props.programs[0]
                                                    ? props.programs[0].name +
                                                      " (" +
                                                      props.programs[0].code +
                                                      ")"
                                                    : "N/A"
                                            }}
                                        </p>
                                        <input
                                            type="hidden"
                                            v-model="form.program_id"
                                        />
                                    </div>
                                    <div v-else>
                                        <select
                                            v-model="form.program_id"
                                            id="program_id"
                                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            :class="
                                                errors.program_id
                                                    ? 'border-red-500'
                                                    : ''
                                            "
                                        >
                                            <option value="">
                                                Select a program
                                            </option>
                                            <option
                                                v-for="program in programs"
                                                :key="program.id"
                                                :value="program.id"
                                            >
                                                {{ program.name }} ({{
                                                    program.code
                                                }})
                                            </option>
                                        </select>
                                    </div>
                                    <div
                                        v-if="errors.program_id"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.program_id[0] }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="name"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Curriculum Name
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.name"
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="
                                            errors.name ? 'border-red-500' : ''
                                        "
                                    />
                                    <div
                                        v-if="errors.name"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.name[0] }}
                                    </div>
                                </div>

                                <div>
                                    <label
                                        for="year_effective"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Year Effective
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model.number="form.year_effective"
                                        id="year_effective"
                                        type="number"
                                        :min="2000"
                                        :max="new Date().getFullYear() + 5"
                                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="
                                            errors.year_effective
                                                ? 'border-red-500'
                                                : ''
                                        "
                                    />
                                    <div
                                        v-if="errors.year_effective"
                                        class="mt-1 text-sm text-red-600"
                                    >
                                        {{ errors.year_effective[0] }}
                                    </div>
                                </div>

                                <div class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        id="is_active"
                                        type="checkbox"
                                        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                    />
                                    <label
                                        for="is_active"
                                        class="ml-2 block text-sm text-gray-700"
                                    >
                                        Set as Active Curriculum
                                    </label>
                                </div>
                            </div>

                            <!-- Curriculum Subjects -->
                            <div class="space-y-4 border-t pt-6">
                                <div class="flex justify-between items-center">
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Curriculum Subjects
                                    </h3>
                                    <button
                                        v-if="!showAddSubjectForm"
                                        type="button"
                                        @click="showAddSubjectForm = true"
                                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm"
                                    >
                                        + Add Subject
                                    </button>
                                </div>

                                <!-- Add Subject Form -->
                                <div
                                    v-if="showAddSubjectForm"
                                    class="bg-green-50 p-4 rounded-lg space-y-3 border-2 border-green-200"
                                >
                                    <div
                                        class="flex justify-between items-center"
                                    >
                                        <h4 class="font-semibold text-gray-900">
                                            Add New Subject
                                        </h4>
                                        <button
                                            type="button"
                                            @click="resetAddSubjectForm"
                                            class="text-red-600 hover:text-red-900 text-sm"
                                        >
                                            Cancel
                                        </button>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700"
                                            >
                                                Subject
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>
                                            <select
                                                v-model="
                                                    newSubjectData.subject_id
                                                "
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option value="">
                                                    Select subject
                                                </option>
                                                <option
                                                    v-for="subj in subjects"
                                                    :key="subj.id"
                                                    :value="subj.id"
                                                >
                                                    {{ subj.code }} -
                                                    {{ subj.title }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700"
                                            >
                                                Year Level
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>
                                            <select
                                                v-model.number="
                                                    newSubjectData.year_level
                                                "
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option value="1">
                                                    1st Year
                                                </option>
                                                <option value="2">
                                                    2nd Year
                                                </option>
                                                <option value="3">
                                                    3rd Year
                                                </option>
                                                <option value="4">
                                                    4th Year
                                                </option>
                                                <option value="5">
                                                    5th Year
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700"
                                            >
                                                Semester
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>
                                            <select
                                                v-model="
                                                    newSubjectData.semester
                                                "
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option value="first">
                                                    1st Semester
                                                </option>
                                                <option value="second">
                                                    2nd Semester
                                                </option>
                                                <option value="summer">
                                                    Summer
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700"
                                            >
                                                Course Type
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>
                                            <select
                                                v-model="
                                                    newSubjectData.course_type
                                                "
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option value="major">
                                                    Major
                                                </option>
                                                <option value="elective">
                                                    Elective
                                                </option>
                                                <option value="minor">
                                                    Minor
                                                </option>
                                            </select>
                                        </div>

                                        <div class="flex items-center pt-6">
                                            <input
                                                v-model="
                                                    newSubjectData.has_laboratory
                                                "
                                                id="new_lab"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                            <label
                                                for="new_lab"
                                                class="ml-2 block text-sm text-gray-700"
                                            >
                                                Has Laboratory
                                            </label>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2 pt-4">
                                        <button
                                            type="button"
                                            @click="resetAddSubjectForm"
                                            class="px-3 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 text-sm"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            @click="addSubject"
                                            class="px-3 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm"
                                        >
                                            Add Subject
                                        </button>
                                    </div>
                                </div>

                                <!-- Subjects List -->
                                <div
                                    v-if="form.subjects.length === 0"
                                    class="text-center py-6 bg-gray-50 rounded"
                                >
                                    <p class="text-gray-500">
                                        No subjects added yet. Click "Add
                                        Subject" to get started.
                                    </p>
                                </div>

                                <div v-else class="space-y-3">
                                    <div
                                        v-for="(
                                            subject, index
                                        ) in form.subjects"
                                        :key="index"
                                        class="bg-gray-50 p-4 rounded-lg border border-gray-200"
                                    >
                                        <div
                                            class="flex justify-between items-start mb-3"
                                        >
                                            <div>
                                                <h4
                                                    class="font-semibold text-gray-900"
                                                >
                                                    {{
                                                        getSubjectCode(
                                                            subject.subject_id,
                                                        )
                                                    }}
                                                    -
                                                    {{
                                                        getSubjectTitle(
                                                            subject.subject_id,
                                                        )
                                                    }}
                                                </h4>
                                                <p
                                                    class="text-sm text-gray-600"
                                                >
                                                    Year
                                                    {{ subject.year_level }}
                                                    |
                                                    {{
                                                        getSemesterLabel(
                                                            subject.semester,
                                                        )
                                                    }}
                                                    |
                                                    {{
                                                        getCourseTypeLabel(
                                                            subject.course_type,
                                                        )
                                                    }}
                                                    <span
                                                        v-if="
                                                            subject.has_laboratory
                                                        "
                                                        class="ml-2"
                                                    >
                                                        | Lab
                                                    </span>
                                                </p>
                                            </div>
                                            <div class="flex gap-2">
                                                <button
                                                    type="button"
                                                    @click="
                                                        startEditSubject(index)
                                                    "
                                                    class="text-blue-600 hover:text-blue-900 text-sm px-3 py-1 border border-blue-300 rounded hover:bg-blue-50"
                                                >
                                                    Update
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="
                                                        removeSubject(index)
                                                    "
                                                    class="text-red-600 hover:text-red-900 text-sm px-3 py-1 border border-red-300 rounded hover:bg-red-50"
                                                >
                                                    Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Subject Modal -->
                            <div
                                v-if="showEditSubjectForm"
                                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
                            >
                                <div
                                    class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 space-y-4"
                                >
                                    <h3
                                        class="text-lg font-semibold text-gray-900"
                                    >
                                        Update Subject
                                    </h3>

                                    <div class="space-y-3">
                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700"
                                            >
                                                Subject
                                                <span class="text-red-500"
                                                    >*</span
                                                >
                                            </label>
                                            <select
                                                v-model="
                                                    editSubjectData.subject_id
                                                "
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option value="">
                                                    Select subject
                                                </option>
                                                <option
                                                    v-for="subj in subjects"
                                                    :key="subj.id"
                                                    :value="subj.id"
                                                >
                                                    {{ subj.code }} -
                                                    {{ subj.title }}
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700"
                                            >
                                                Year Level
                                            </label>
                                            <select
                                                v-model.number="
                                                    editSubjectData.year_level
                                                "
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option value="1">
                                                    1st Year
                                                </option>
                                                <option value="2">
                                                    2nd Year
                                                </option>
                                                <option value="3">
                                                    3rd Year
                                                </option>
                                                <option value="4">
                                                    4th Year
                                                </option>
                                                <option value="5">
                                                    5th Year
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700"
                                            >
                                                Semester
                                            </label>
                                            <select
                                                v-model="
                                                    editSubjectData.semester
                                                "
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option value="first">
                                                    1st Semester
                                                </option>
                                                <option value="second">
                                                    2nd Semester
                                                </option>
                                                <option value="summer">
                                                    Summer
                                                </option>
                                            </select>
                                        </div>

                                        <div>
                                            <label
                                                class="block text-sm font-medium text-gray-700"
                                            >
                                                Course Type
                                            </label>
                                            <select
                                                v-model="
                                                    editSubjectData.course_type
                                                "
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                                <option value="major">
                                                    Major
                                                </option>
                                                <option value="elective">
                                                    Elective
                                                </option>
                                                <option value="minor">
                                                    Minor
                                                </option>
                                            </select>
                                        </div>

                                        <div class="flex items-center">
                                            <input
                                                v-model="
                                                    editSubjectData.has_laboratory
                                                "
                                                id="edit_lab"
                                                type="checkbox"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                            <label
                                                for="edit_lab"
                                                class="ml-2 block text-sm text-gray-700"
                                            >
                                                Has Laboratory
                                            </label>
                                        </div>
                                    </div>

                                    <div
                                        class="flex justify-end gap-2 border-t pt-4"
                                    >
                                        <button
                                            type="button"
                                            @click="resetEditSubjectForm"
                                            class="px-3 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
                                        >
                                            Cancel
                                        </button>
                                        <button
                                            type="button"
                                            @click="saveEditSubject"
                                            class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                                        >
                                            Save
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end gap-4 border-t pt-6">
                                <a
                                    :href="
                                        route('curriculums.show', curriculum.id)
                                    "
                                    class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="isSubmitting"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{
                                        isSubmitting
                                            ? "Saving..."
                                            : "Save Changes"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
