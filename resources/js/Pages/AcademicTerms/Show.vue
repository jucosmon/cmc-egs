<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

const { term, stats } = defineProps({
    term: Object,
    stats: Object,
});

const formatDate = (iso) => {
    if (!iso) return "";
    try {
        const d = new Date(iso);
        return new Intl.DateTimeFormat("en", {
            year: "numeric",
            month: "short",
            day: "numeric",
        }).format(d);
    } catch (e) {
        return iso;
    }
};

const activate = () => {
    if (
        confirm("Do you confirm to activate this term to be the current term?")
    ) {
        router.post(route("terms.activate", term.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Term ${term.academic_year} - ${term.semester}`" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">
                                Academic Term Details
                            </h2>
                            <Link
                                :href="route('terms.index')"
                                class="text-gray-600 hover:text-gray-900"
                                >← Back</Link
                            >
                        </div>

                        <div class="space-y-4">
                            <div>
                                <h3 class="font-semibold">Academic Year</h3>
                                <p>{{ term.academic_year }}</p>
                            </div>
                            <div>
                                <h3 class="font-semibold">Semester</h3>
                                <p>{{ term.semester }}</p>
                            </div>
                            <div>
                                <h3 class="font-semibold">Dates</h3>
                                <p>
                                    {{ formatDate(term.start_date) }} —
                                    {{ formatDate(term.end_date) }}
                                </p>
                            </div>
                            <div>
                                <h3 class="font-semibold">Enrollment Period</h3>
                                <p>
                                    {{ formatDate(term.start_enrollment) }} —
                                    {{ formatDate(term.end_enrollment) }}
                                </p>
                            </div>
                            <div>
                                <h3 class="font-semibold">Midterm Grading</h3>
                                <p>
                                    {{ formatDate(term.start_mid_grade) }} —
                                    {{ formatDate(term.end_mid_grade) }}
                                </p>
                            </div>
                            <div>
                                <h3 class="font-semibold">Final Grading</h3>
                                <p>
                                    {{ formatDate(term.start_final_grade) }} —
                                    {{ formatDate(term.end_final_grade) }}
                                </p>
                            </div>

                            <div
                                class="pt-4 border-t flex items-center justify-between"
                            >
                                <div class="space-x-3">
                                    <Link
                                        :href="route('terms.edit', term.id)"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                                        >Update</Link
                                    >
                                    <button
                                        v-if="!term.is_active"
                                        @click.prevent="activate"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                    >
                                        Activate
                                    </button>
                                </div>

                                <div class="text-sm text-gray-600">
                                    <div>
                                        Total Enrollments:
                                        {{ stats.total_enrollments }}
                                    </div>
                                    <div>
                                        Total Scheduled Subjects:
                                        {{ stats.total_scheduled_subjects }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
