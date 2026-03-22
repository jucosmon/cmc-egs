<script setup>
import Pagination from "@/Components/Pagination.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const page = usePage();
const flash = computed(() => page.props.flash || {});
const archiveError = computed(() => page.props.errors?.archive || null);

const props = defineProps({
    programs: Object,
    filters: Object,
});

const showArchived = ref(Boolean(props.filters?.show_archived));

const archiveProgram = (id) => {
    if (confirm("Are you sure you want to archive this program?")) {
        router.delete(route("programs.destroy", id));
    }
};

const toggleArchived = () => {
    router.get(
        route("programs.index"),
        { show_archived: showArchived.value ? 1 : 0 },
        { preserveState: true, replace: true },
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Programs" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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

                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold">Programs</h2>
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
                                    :href="route('programs.create')"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
                                >
                                    Create
                                </Link>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Code
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Name
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Program Head
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Students
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Status
                                        </th>
                                        <th
                                            class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody
                                    class="bg-white divide-y divide-gray-200"
                                >
                                    <tr
                                        v-for="program in programs.data"
                                        :key="program.id"
                                    >
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                        >
                                            {{ program.code }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm"
                                        >
                                            {{ program.name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm"
                                        >
                                            {{
                                                program.program_head
                                                    ? program.program_head
                                                          .first_name +
                                                      " " +
                                                      program.program_head
                                                          .last_name
                                                    : "N/A"
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm"
                                        >
                                            {{ program.students_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="
                                                    program.is_active
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-red-100 text-red-800'
                                                "
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            >
                                                {{
                                                    program.is_active
                                                        ? "Active"
                                                        : "Archived"
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'programs.show',
                                                        program.id,
                                                    )
                                                "
                                                class="text-indigo-600 hover:text-indigo-900"
                                                >View</Link
                                            >
                                            <button
                                                v-if="program.is_active"
                                                @click="
                                                    archiveProgram(program.id)
                                                "
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Archive
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination :links="programs.links" />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
