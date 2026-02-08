<script setup>
import Pagination from "@/Components/Pagination.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps({
    subjects: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? "");

const submitSearch = () => {
    router.get(
        route("subjects.index"),
        { search: search.value || undefined },
        { preserveState: true, replace: true, preserveScroll: true },
    );
};

const clearSearch = () => {
    search.value = "";
    submitSearch();
};

const deleteSubject = (id) => {
    if (confirm("Are you sure you want to delete this subject?")) {
        router.delete(route("subjects.destroy", id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Subjects" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div
                            class="mb-6 flex flex-wrap items-center justify-between gap-4"
                        >
                            <div>
                                <h2 class="text-2xl font-bold">Subjects</h2>
                                <p class="text-sm text-gray-600">
                                    Manage the master list of subjects.
                                </p>
                            </div>
                            <Link
                                :href="route('subjects.create')"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                            >
                                Create Subject
                            </Link>
                        </div>

                        <div
                            class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center"
                        >
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search code or title"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:max-w-sm"
                                @keyup.enter="submitSearch"
                            />
                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    class="rounded-md bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-800"
                                    @click="submitSearch"
                                >
                                    Search
                                </button>
                                <button
                                    v-if="search"
                                    type="button"
                                    class="rounded-md bg-gray-100 px-4 py-2 text-sm text-gray-700 hover:bg-gray-200"
                                    @click="clearSearch"
                                >
                                    Clear
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Code
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Title
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Units
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Curriculums
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium uppercase text-gray-500"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="divide-y divide-gray-200 bg-white"
                                >
                                    <tr
                                        v-for="subject in subjects.data"
                                        :key="subject.id"
                                    >
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm font-medium"
                                        >
                                            {{ subject.code }}
                                        </td>
                                        <td class="px-6 py-4 text-sm">
                                            {{ subject.title }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm"
                                        >
                                            {{ subject.units }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-sm"
                                        >
                                            {{
                                                subject.curriculum_subjects_count ??
                                                subject.curriculumSubjectsCount ??
                                                0
                                            }}
                                        </td>
                                        <td
                                            class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium"
                                        >
                                            <div class="flex justify-end gap-3">
                                                <Link
                                                    :href="
                                                        route(
                                                            'subjects.show',
                                                            subject.id,
                                                        )
                                                    "
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="
                                                        route(
                                                            'subjects.edit',
                                                            subject.id,
                                                        )
                                                    "
                                                    class="text-blue-600 hover:text-blue-900"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    @click="
                                                        deleteSubject(
                                                            subject.id,
                                                        )
                                                    "
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!subjects.data.length">
                                        <td
                                            colspan="5"
                                            class="px-6 py-8 text-center text-sm text-gray-500"
                                        >
                                            No subjects found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="subjects.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
