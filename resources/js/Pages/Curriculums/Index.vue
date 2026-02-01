<script setup>
import Pagination from "@/Components/Pagination.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const { curriculums, programs, filters } = defineProps({
    curriculums: Object,
    programs: Array,
    filters: Object,
});

const showDeleteConfirm = ref(false);
const selectedCurriculumId = ref(null);

const activateCurriculum = (id) => {
    if (
        confirm(
            "Are you sure you want to activate this curriculum? The previously active curriculum will be deactivated.",
        )
    ) {
        router.put(
            route("curriculums.activate", id),
            {},
            {
                onSuccess: () => {
                    router.reload();
                },
            },
        );
    }
};

const deleteCurriculum = (id) => {
    if (confirm("Are you sure you want to delete this curriculum?")) {
        router.delete(route("curriculums.destroy", id), {
            onSuccess: () => {
                router.reload();
            },
        });
    }
};

const hasCurriculums = computed(() => {
    return curriculums && curriculums.data && curriculums.data.length > 0;
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Curriculums" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h2 class="text-2xl font-bold">Curriculums</h2>
                                <div
                                    v-if="programs && programs.length === 1"
                                    class="text-sm text-gray-600"
                                >
                                    Program: {{ programs[0].name }} ({{
                                        programs[0].code
                                    }})
                                </div>
                            </div>
                            <Link
                                :href="route('curriculums.create')"
                                class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
                            >
                                Create
                            </Link>
                        </div>

                        <!-- No curriculums message -->
                        <div v-if="!hasCurriculums" class="text-center py-12">
                            <p class="text-gray-500 text-lg">
                                No curriculums created
                            </p>
                        </div>

                        <!-- Curriculums table -->
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Name
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Program
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Year Effective
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
                                        >
                                            Subjects
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
                                        v-for="curriculum in curriculums.data"
                                        :key="curriculum.id"
                                    >
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                                        >
                                            {{ curriculum.name }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm"
                                        >
                                            {{
                                                curriculum.program
                                                    ? curriculum.program.name
                                                    : "N/A"
                                            }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm"
                                        >
                                            {{ curriculum.year_effective }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm"
                                        >
                                            {{
                                                curriculum.curriculum_subjects_count
                                            }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                :class="
                                                    curriculum.is_active
                                                        ? 'bg-green-100 text-green-800'
                                                        : 'bg-red-100 text-red-800'
                                                "
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            >
                                                {{
                                                    curriculum.is_active
                                                        ? "Active"
                                                        : "Inactive"
                                                }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3"
                                        >
                                            <Link
                                                :href="
                                                    route(
                                                        'curriculums.show',
                                                        curriculum.id,
                                                    )
                                                "
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                View
                                            </Link>
                                            <Link
                                                :href="
                                                    route(
                                                        'curriculums.edit',
                                                        curriculum.id,
                                                    )
                                                "
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                v-if="!curriculum.is_active"
                                                @click="
                                                    activateCurriculum(
                                                        curriculum.id,
                                                    )
                                                "
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Activate
                                            </button>
                                            <button
                                                @click="
                                                    deleteCurriculum(
                                                        curriculum.id,
                                                    )
                                                "
                                                class="text-red-600 hover:text-red-900"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <Pagination
                            v-if="hasCurriculums"
                            :links="curriculums.links"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
