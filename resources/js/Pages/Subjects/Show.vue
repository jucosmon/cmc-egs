<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed } from "vue";

const { subject } = defineProps({ subject: Object });

const curriculumItems = computed(
    () => subject.curriculum_subjects || subject.curriculumSubjects || [],
);

const deleteSubject = () => {
    if (confirm("Are you sure you want to delete this subject?")) {
        router.delete(route("subjects.destroy", subject.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Subject Details" />

        <div class="py-12">
            <div class="mx-auto max-w-5xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div
                            class="mb-6 flex flex-wrap items-center justify-between gap-4"
                        >
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">
                                    Subject Details
                                </h2>
                                <p class="text-sm text-gray-600">
                                    Code: {{ subject.code }}
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    :href="route('subjects.edit', subject.id)"
                                    class="rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                                    @click="deleteSubject"
                                >
                                    Delete
                                </button>
                                <Link
                                    :href="route('subjects.index')"
                                    class="rounded-md bg-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-300"
                                >
                                    Back
                                </Link>
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="rounded-lg border border-gray-100 p-4">
                                <p
                                    class="text-xs font-semibold uppercase text-gray-500"
                                >
                                    Title
                                </p>
                                <p class="mt-1 text-base text-gray-900">
                                    {{ subject.title }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-gray-100 p-4">
                                <p
                                    class="text-xs font-semibold uppercase text-gray-500"
                                >
                                    Units
                                </p>
                                <p class="mt-1 text-base text-gray-900">
                                    {{ subject.units }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 rounded-lg border border-gray-100 p-4">
                            <p
                                class="text-xs font-semibold uppercase text-gray-500"
                            >
                                Description
                            </p>
                            <p class="mt-1 text-sm text-gray-700">
                                {{
                                    subject.description ||
                                    "No description provided."
                                }}
                            </p>
                        </div>

                        <div class="mt-6 rounded-lg border border-gray-100 p-4">
                            <div class="mb-3 flex items-center justify-between">
                                <p
                                    class="text-xs font-semibold uppercase text-gray-500"
                                >
                                    Curriculums Using This Subject
                                </p>
                                <span
                                    class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-700"
                                >
                                    {{ curriculumItems.length }}
                                </span>
                            </div>
                            <div
                                v-if="curriculumItems.length"
                                class="space-y-2"
                            >
                                <div
                                    v-for="item in curriculumItems"
                                    :key="item.id"
                                    class="rounded-md bg-gray-50 p-3 text-sm text-gray-700"
                                >
                                    <div class="font-medium text-gray-900">
                                        {{
                                            item.curriculum?.name ||
                                            "Curriculum"
                                        }}
                                    </div>
                                    <div class="text-xs text-gray-600">
                                        Program:
                                        {{
                                            item.curriculum?.program?.name ||
                                            "N/A"
                                        }}
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-500">
                                This subject is not yet used in any curriculum.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
