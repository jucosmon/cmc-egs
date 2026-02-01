<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    programs: Array,
    subjects: Array,
});

const form = ref({
    name: "",
    year_effective: new Date().getFullYear(),
    program_id: "",
    is_active: false,
    subjects: [],
});

const errors = ref({});
const isSubmitting = ref(false);

const addSubject = () => {
    form.value.subjects.push({
        subject_id: "",
        year_level: 1,
        semester: "first",
        course_type: "major",
        has_laboratory: false,
        prerequisites: [],
    });
};

const removeSubject = (index) => {
    form.value.subjects.splice(index, 1);
};

const submitForm = () => {
    isSubmitting.value = true;
    router.post(route("curriculums.store"), form.value, {
        onError: (pageErrors) => {
            errors.value = pageErrors;
            isSubmitting.value = false;
        },
        onSuccess: () => {
            isSubmitting.value = false;
        },
    });
};

const getSelectedProgram = () => {
    return props.programs.find((p) => p.id == form.value.program_id);
};

const getSubjectTitle = (subjectId) => {
    const subject = props.subjects.find((s) => s.id == subjectId);
    return subject ? subject.title : "Select a subject";
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Curriculum" />

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-6">
                            Create New Curriculum
                        </h2>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Basic Information
                                </h3>

                                <div>
                                    <label
                                        for="program_id"
                                        class="block text-sm font-medium text-gray-700"
                                    >
                                        Program
                                        <span class="text-red-500">*</span>
                                    </label>
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
                                            {{ program.name }}
                                            ({{ program.code }})
                                        </option>
                                    </select>
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
                                        placeholder="e.g., BS Computer Science Curriculum 2024"
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
                                        type="button"
                                        @click="addSubject"
                                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm"
                                    >
                                        + Add Subject
                                    </button>
                                </div>

                                <div
                                    v-if="form.subjects.length === 0"
                                    class="text-center py-6 bg-gray-50 rounded"
                                >
                                    <p class="text-gray-500">
                                        No subjects added yet. Click "Add
                                        Subject" to get started.
                                    </p>
                                </div>

                                <div v-else class="space-y-4">
                                    <div
                                        v-for="(
                                            subject, index
                                        ) in form.subjects"
                                        :key="index"
                                        class="bg-gray-50 p-4 rounded-lg space-y-3"
                                    >
                                        <div
                                            class="flex justify-between items-start mb-3"
                                        >
                                            <h4
                                                class="font-semibold text-gray-900"
                                            >
                                                Subject {{ index + 1 }}:
                                                {{
                                                    getSubjectTitle(
                                                        subject.subject_id,
                                                    )
                                                }}
                                            </h4>
                                            <button
                                                type="button"
                                                @click="removeSubject(index)"
                                                class="text-red-600 hover:text-red-900 text-sm"
                                            >
                                                Remove
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
                                                    v-model="subject.subject_id"
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
                                                        subject.year_level
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
                                                    v-model="subject.semester"
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
                                                        subject.course_type
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
                                                        subject.has_laboratory
                                                    "
                                                    :id="`lab_${index}`"
                                                    type="checkbox"
                                                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                                />
                                                <label
                                                    :for="`lab_${index}`"
                                                    class="ml-2 block text-sm text-gray-700"
                                                >
                                                    Has Laboratory
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end gap-4 border-t pt-6">
                                <a
                                    href="/curriculums"
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
                                            ? "Submitting..."
                                            : "Submit"
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
