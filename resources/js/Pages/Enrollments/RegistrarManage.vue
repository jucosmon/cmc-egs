<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    student: Object,
    enrollment: Object,
    blockSchedules: Array,
    enrolledSubjects: Array,
    searchedId: String,
});

const searchForm = useForm({
    student_id: props.searchedId || "",
});

const enrollSubjectForm = useForm({
    scheduled_subject_id: null,
});

const showConfirmEnroll = ref(false);
const showConfirmDrop = ref(false);
const showAddSubject = ref(false);
const selectedSubject = ref(null);
const selectedSchedule = ref(null);

const searchSubjectForm = useForm({
    subject_code: "",
});

const availableSchedules = ref([]);

// Search student
const searchStudent = () => {
    router.get(
        route("enrollments.registrar-manage"),
        {
            student_id: searchForm.student_id,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

// Create enrollment
const createEnrollment = () => {
    router.post(
        route("enrollments.registrar-create", props.student.id),
        {},
        {
            onSuccess: () => {
                router.reload();
            },
        },
    );
};

// Enroll in subject
const confirmEnrollSubject = (schedule) => {
    selectedSchedule.value = schedule;
    showConfirmEnroll.value = true;
};

const enrollInSubject = () => {
    enrollSubjectForm.scheduled_subject_id = selectedSchedule.value.id;
    enrollSubjectForm.post(
        route("enrollments.enroll-subject", props.enrollment.id),
        {
            onSuccess: () => {
                showConfirmEnroll.value = false;
                selectedSchedule.value = null;
                router.reload();
            },
        },
    );
};

// Drop subject
const confirmDropSubject = (enrolledSubject) => {
    selectedSubject.value = enrolledSubject;
    showConfirmDrop.value = true;
};

const dropSubject = () => {
    router.delete(route("enrollments.drop-subject", selectedSubject.value.id), {
        onSuccess: () => {
            showConfirmDrop.value = false;
            selectedSubject.value = null;
            router.reload();
        },
    });
};

// Add subject (search)
const searchSubject = () => {
    router.get(
        route("enrollments.search-subject"),
        {
            enrollment_id: props.enrollment.id,
            subject_code: searchSubjectForm.subject_code,
        },
        {
            preserveState: true,
            onSuccess: (page) => {
                availableSchedules.value = page.props.availableSchedules || [];
            },
        },
    );
};

const totalUnits = computed(() => {
    if (!props.enrolledSubjects) return 0;
    return props.enrolledSubjects.reduce((sum, es) => {
        return (
            sum +
            (es.scheduled_subject?.curriculum_subject?.subject?.units || 0)
        );
    }, 0);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manage Enrollment" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Search Student -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">
                            Search Student
                        </h2>
                        <form
                            @submit.prevent="searchStudent"
                            class="flex gap-3"
                        >
                            <input
                                v-model="searchForm.student_id"
                                type="text"
                                placeholder="Enter Student ID..."
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                required
                            />
                            <button
                                type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Search
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Student Profile (if found) -->
                <div
                    v-if="student"
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                >
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">
                                    {{ student.user.first_name }}
                                    {{ student.user.last_name }}
                                </h3>
                                <p class="text-gray-600">
                                    {{ student.user.official_id }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-600">
                                    Program: {{ student.program.name }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Year Level: {{ student.year_level }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Block: {{ student.block?.code || "N/A" }}
                                </p>
                            </div>
                        </div>

                        <!-- Create Enrollment Button -->
                        <div v-if="!enrollment" class="text-center py-8">
                            <p class="text-gray-600 mb-4">
                                No enrollment record found for this student.
                            </p>
                            <button
                                @click="createEnrollment"
                                class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700"
                            >
                                Create Enrollment Record
                            </button>
                        </div>

                        <!-- View Enrollment Button -->
                        <div v-else class="text-center">
                            <p class="text-green-600 font-semibold mb-2">
                                ✓ Enrollment Record Exists
                            </p>
                            <p class="text-sm text-gray-600">
                                Status: {{ enrollment.status }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Details (if exists) -->
                <div v-if="enrollment">
                    <!-- Block Schedules -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-800 mb-4">
                                Block Schedule ({{ student.block?.code }})
                            </h3>
                            <div
                                v-if="
                                    blockSchedules && blockSchedules.length > 0
                                "
                                class="space-y-3"
                            >
                                <div
                                    v-for="schedule in blockSchedules"
                                    :key="schedule.id"
                                    class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50"
                                >
                                    <div>
                                        <p class="font-semibold">
                                            {{
                                                schedule.curriculum_subject
                                                    .subject.code
                                            }}
                                            -
                                            {{
                                                schedule.curriculum_subject
                                                    .subject.title
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ schedule.day }} |
                                            {{ schedule.time_start }} -
                                            {{ schedule.time_end }} | Room
                                            {{ schedule.room }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Instructor:
                                            {{
                                                schedule.instructor?.user
                                                    ?.first_name
                                            }}
                                            {{
                                                schedule.instructor?.user
                                                    ?.last_name
                                            }}
                                        </p>
                                    </div>
                                    <button
                                        v-if="!schedule.is_enrolled"
                                        @click="confirmEnrollSubject(schedule)"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                    >
                                        Enroll
                                    </button>
                                    <span
                                        v-else
                                        class="px-4 py-2 bg-green-100 text-green-800 rounded-md"
                                    >
                                        Enrolled
                                    </span>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 text-center py-8">
                                No block schedules available
                            </p>
                        </div>
                    </div>

                    <!-- Enrolled Subjects -->
                    <div
                        class="bg-white overflow-hidden shadow-sm sm:rounded-lg"
                    >
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-bold text-gray-800">
                                    Enrolled Subjects ({{
                                        enrolledSubjects?.length || 0
                                    }})
                                </h3>
                                <div class="text-sm text-gray-600">
                                    Total Units:
                                    <span class="font-bold">{{
                                        totalUnits
                                    }}</span>
                                </div>
                            </div>

                            <div
                                v-if="
                                    enrolledSubjects &&
                                    enrolledSubjects.length > 0
                                "
                                class="space-y-3"
                            >
                                <div
                                    v-for="es in enrolledSubjects"
                                    :key="es.id"
                                    class="flex items-center justify-between p-4 border rounded-lg"
                                >
                                    <div>
                                        <p class="font-semibold">
                                            {{
                                                es.scheduled_subject
                                                    .curriculum_subject.subject
                                                    .code
                                            }}
                                            -
                                            {{
                                                es.scheduled_subject
                                                    .curriculum_subject.subject
                                                    .title
                                            }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ es.scheduled_subject.day }} |
                                            {{
                                                es.scheduled_subject.time_start
                                            }}
                                            -
                                            {{ es.scheduled_subject.time_end }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Units:
                                            {{
                                                es.scheduled_subject
                                                    .curriculum_subject.subject
                                                    .units
                                            }}
                                        </p>
                                    </div>
                                    <button
                                        @click="confirmDropSubject(es)"
                                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                    >
                                        Drop
                                    </button>
                                </div>
                            </div>
                            <p v-else class="text-gray-500 text-center py-8">
                                No enrolled subjects yet
                            </p>

                            <!-- Add Subject Button -->
                            <button
                                @click="showAddSubject = true"
                                class="mt-4 w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-md text-gray-600 hover:border-indigo-500 hover:text-indigo-600"
                            >
                                + Add Subject
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Confirm Enroll Modal -->
                <div
                    v-if="showConfirmEnroll"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
                >
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-bold mb-4">
                            Confirm Enrollment
                        </h3>
                        <p class="mb-6">
                            Do you confirm to enroll the student in this class?
                            <br /><br />
                            <strong
                                >{{
                                    selectedSchedule?.curriculum_subject
                                        ?.subject?.code
                                }}
                                -
                                {{
                                    selectedSchedule?.curriculum_subject
                                        ?.subject?.title
                                }}</strong
                            >
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="showConfirmEnroll = false"
                                class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300"
                            >
                                Cancel
                            </button>
                            <button
                                @click="enrollInSubject"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Confirm Drop Modal -->
                <div
                    v-if="showConfirmDrop"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
                >
                    <div class="bg-white rounded-lg p-6 max-w-md w-full">
                        <h3 class="text-lg font-bold mb-4">Confirm Drop</h3>
                        <p class="mb-6">
                            Do you want to drop the student from this class?
                            <br /><br />
                            <strong
                                >{{
                                    selectedSubject?.scheduled_subject
                                        ?.curriculum_subject?.subject?.code
                                }}
                                -
                                {{
                                    selectedSubject?.scheduled_subject
                                        ?.curriculum_subject?.subject?.title
                                }}</strong
                            >
                        </p>
                        <div class="flex gap-3 justify-end">
                            <button
                                @click="showConfirmDrop = false"
                                class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300"
                            >
                                Cancel
                            </button>
                            <button
                                @click="dropSubject"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                            >
                                Confirm Drop
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Add Subject Modal -->
                <div
                    v-if="showAddSubject"
                    class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
                >
                    <div
                        class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto"
                    >
                        <h3 class="text-lg font-bold mb-4">Add Subject</h3>

                        <form
                            @submit.prevent="searchSubject"
                            class="flex gap-3 mb-6"
                        >
                            <input
                                v-model="searchSubjectForm.subject_code"
                                type="text"
                                placeholder="Enter subject code..."
                                class="flex-1 rounded-md border-gray-300"
                                required
                            />
                            <button
                                type="submit"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                            >
                                Search
                            </button>
                        </form>

                        <div
                            v-if="availableSchedules.length > 0"
                            class="space-y-3"
                        >
                            <div
                                v-for="schedule in availableSchedules"
                                :key="schedule.id"
                                class="flex items-center justify-between p-4 border rounded-lg"
                            >
                                <div>
                                    <p class="font-semibold">
                                        {{ schedule.subject_title }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        {{ schedule.day }} |
                                        {{ schedule.time_start }} -
                                        {{ schedule.time_end }} | Room
                                        {{ schedule.room }}
                                    </p>
                                </div>
                                <button
                                    @click="confirmEnrollSubject(schedule)"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                >
                                    Enroll
                                </button>
                            </div>
                        </div>

                        <button
                            @click="showAddSubject = false"
                            class="mt-4 w-full px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
