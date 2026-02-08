<script setup>
import InputError from "@/Components/InputError.vue";
import Modal from "@/Components/Modal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    student: Object,
    enrollments: Array,
    searchedId: String,
    searchMessage: String,
});

const searchForm = useForm({
    student_id: props.searchedId || "",
});

const showUpdateModal = ref(false);
const selectedSubject = ref(null);

const updateForm = useForm({
    _method: "put",
    grade_period: "midterm",
    new_grade: "",
    reason: "",
    attachment: null,
});

const searchStudent = () => {
    router.get(
        route("grades.index"),
        { student_id: searchForm.student_id },
        { preserveState: true, preserveScroll: true },
    );
};

const openUpdateModal = (enrolledSubject, period) => {
    selectedSubject.value = { ...enrolledSubject, period };
    updateForm.grade_period = period;
    updateForm.new_grade =
        period === "midterm"
            ? (enrolledSubject.midterm_grade ?? "")
            : (enrolledSubject.final_grade ?? "");
    updateForm.reason = "";
    updateForm.attachment = null;
    updateForm.clearErrors();
    showUpdateModal.value = true;
};

const closeUpdateModal = () => {
    showUpdateModal.value = false;
    selectedSubject.value = null;
    updateForm.reset();
    updateForm.clearErrors();
};

const oldGrade = computed(() => {
    if (!selectedSubject.value) return "-";
    return selectedSubject.value.period === "midterm"
        ? (selectedSubject.value.midterm_grade ?? "-")
        : (selectedSubject.value.final_grade ?? "-");
});

const submitUpdate = () => {
    if (!selectedSubject.value) return;
    updateForm.post(route("grades.update-single", selectedSubject.value.id), {
        forceFormData: true,
        onSuccess: () => {
            closeUpdateModal();
            router.reload({ only: ["student", "enrollments"] });
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manage Grades" />

        <template #header>
            <h2
                class="text-lg font-semibold leading-tight text-[#1f7fa3] sm:text-xl"
            >
                Manage Grades (Registrar)
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Search Student
                        </h3>
                        <form
                            @submit.prevent="searchStudent"
                            class="mt-4 flex flex-col gap-3 md:flex-row"
                        >
                            <input
                                v-model="searchForm.student_id"
                                type="text"
                                placeholder="Enter Student ID"
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <button
                                type="submit"
                                class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                Search
                            </button>
                        </form>
                        <p
                            v-if="searchMessage"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ searchMessage }}
                        </p>
                    </div>
                </div>

                <div v-if="student" class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div
                            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                        >
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ student.user.first_name }}
                                    {{ student.user.last_name }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ student.user.official_id }}
                                </p>
                            </div>
                            <div class="text-sm text-gray-600">
                                <p>Program: {{ student.program?.name }}</p>
                                <p>Year Level: {{ student.year_level }}</p>
                                <p>Block: {{ student.block?.code || "N/A" }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="student" class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-semibold text-gray-900">
                            Grade Sheet
                        </h4>

                        <div
                            v-if="!enrollments || !enrollments.length"
                            class="mt-6 rounded-lg border border-dashed p-8 text-center text-gray-500"
                        >
                            No subjects enrolled yet.
                        </div>

                        <div v-else class="mt-6 space-y-6">
                            <div
                                v-for="enrollment in enrollments"
                                :key="enrollment.id"
                                class="border rounded-lg"
                            >
                                <div class="px-4 py-3 bg-gray-50 border-b">
                                    <h5 class="font-semibold text-gray-800">
                                        {{
                                            enrollment.academic_term
                                                ?.academic_year
                                        }}
                                        -
                                        {{ enrollment.academic_term?.semester }}
                                    </h5>
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
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <span>
                                                            {{
                                                                subject.midterm_grade ??
                                                                "-"
                                                            }}
                                                        </span>
                                                        <button
                                                            class="text-xs text-indigo-600 hover:text-indigo-800"
                                                            @click="
                                                                openUpdateModal(
                                                                    subject,
                                                                    'midterm',
                                                                )
                                                            "
                                                        >
                                                            Update
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm">
                                                    <div
                                                        class="flex items-center gap-2"
                                                    >
                                                        <span>
                                                            {{
                                                                subject.final_grade ??
                                                                "-"
                                                            }}
                                                        </span>
                                                        <button
                                                            class="text-xs text-indigo-600 hover:text-indigo-800"
                                                            @click="
                                                                openUpdateModal(
                                                                    subject,
                                                                    'final',
                                                                )
                                                            "
                                                        >
                                                            Update
                                                        </button>
                                                    </div>
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
        </div>

        <Modal :show="showUpdateModal" @close="closeUpdateModal">
            <div class="p-6 space-y-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Update Grade
                    </h3>
                    <p class="text-sm text-gray-600">
                        {{
                            selectedSubject?.scheduled_subject
                                ?.curriculum_subject?.subject?.code
                        }}
                        -
                        {{
                            selectedSubject?.scheduled_subject
                                ?.curriculum_subject?.subject?.title
                        }}
                        ({{
                            selectedSubject?.period === "midterm"
                                ? "Midterm"
                                : "Final"
                        }})
                    </p>
                </div>

                <div class="space-y-2 text-sm">
                    <p>
                        <span class="text-gray-600">Old Grade:</span>
                        <span class="font-medium text-gray-900">{{
                            oldGrade
                        }}</span>
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        New Grade
                    </label>
                    <input
                        v-model="updateForm.new_grade"
                        type="number"
                        min="0"
                        max="100"
                        step="0.01"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <InputError :message="updateForm.errors.new_grade" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Reason for Modification
                    </label>
                    <textarea
                        v-model="updateForm.reason"
                        rows="3"
                        class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    ></textarea>
                    <InputError :message="updateForm.errors.reason" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Attachment (optional)
                    </label>
                    <input
                        type="file"
                        @change="
                            (event) =>
                                (updateForm.attachment = event.target.files[0])
                        "
                        class="mt-1 block w-full text-sm text-gray-600"
                    />
                    <InputError :message="updateForm.errors.attachment" />
                </div>

                <div class="flex justify-end gap-2">
                    <SecondaryButton @click="closeUpdateModal">
                        Cancel
                    </SecondaryButton>
                    <PrimaryButton @click="submitUpdate"> Save </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
