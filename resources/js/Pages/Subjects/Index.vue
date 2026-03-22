<script setup>
import Pagination from "@/Components/Pagination.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const flash = computed(() => page.props.flash || {});
const archiveError = computed(() => page.props.errors?.archive || null);

const props = defineProps({
    subjects: Object,
    filters: Object,
});

const search = ref(props.filters?.search ?? "");
const showArchived = ref(Boolean(props.filters?.show_archived));

const submitSearch = () => {
    const params = {
        search: search.value || undefined,
        show_archived: showArchived.value ? 1 : undefined,
    };

    // Remove undefined values
    Object.keys(params).forEach((key) => {
        if (params[key] === undefined) delete params[key];
    });

    router.get(route("subjects.index"), params, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
};

const clearSearch = () => {
    search.value = "";
    submitSearch();
};

const archiveSubject = (id) => {
    if (confirm("Are you sure you want to archive this subject?")) {
        router.delete(route("subjects.destroy", id));
    }
};

const toggleArchived = () => {
    const params = {
        search: search.value || undefined,
        show_archived: showArchived.value ? 1 : undefined,
    };

    // Remove undefined values
    Object.keys(params).forEach((key) => {
        if (params[key] === undefined) delete params[key];
    });

    router.get(route("subjects.index"), params, {
        preserveState: true,
        replace: true,
    });
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
                            v-if="flash.success"
                            class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800"
                        >
                            {{ flash.success }}
                        </div>
                        <div
                            v-if="flash.error"
                            class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800"
                        >
                            {{ flash.error }}
                        </div>
                        <div
                            v-if="archiveError"
                            class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800"
                        >
                            {{ archiveError }}
                        </div>

                        <div
                            class="mb-6 flex flex-wrap items-center justify-between gap-4"
                        >
                            <div>
                                <h2 class="text-2xl font-bold">Subjects</h2>
                                <p class="text-sm text-gray-600">
                                    Manage the master list of subjects.
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                <label
                                    class="inline-flex items-center gap-2 text-sm"
                                >
                                    <input
                                        v-model="showArchived"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        @change="toggleArchived"
                                    />
                                    Show archived
                                </label>
                                <Link
                                    :href="route('subjects.create')"
                                    class="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                                >
                                    Create
                                </Link>
                            </div>
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
                                            class="px-6 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            Status
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
                                            class="whitespace-nowrap px-6 py-4 text-sm"
                                        >
                                            <span
                                                v-if="!subject.is_active"
                                                class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-800"
                                            >
                                                Archived
                                            </span>
                                            <span
                                                v-else
                                                class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800"
                                            >
                                                Active
                                            </span>
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
                                                    v-if="subject.is_active"
                                                    type="button"
                                                    class="text-red-600 hover:text-red-900"
                                                    @click="
                                                        archiveSubject(
                                                            subject.id,
                                                        )
                                                    "
                                                >
                                                    Archive
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
