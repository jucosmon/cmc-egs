<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";

const props = defineProps({
    program: Object,
});

const deleteProgram = () => {
    if (
        !confirm(
            "Are you sure you want to delete this program? This action cannot be undone.",
        )
    )
        return;

    router.delete(
        route("programs.destroy", props.program.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                alert("Program deleted successfully.");
                router.visit(route("programs.index"));
            },
            onError: () => {
                alert("Failed to delete program.");
            },
        },
    );
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Program: ${program.name}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg m-2"
                >
                    <div class="p-5">
                        <div
                            class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6"
                        >
                            <div class="pb-2">
                                <h2 class="text-2xl font-bold">
                                    {{ program.name }}
                                </h2>
                                <p class="text-sm text-gray-500">
                                    Code: {{ program.code }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Status:
                                    <span
                                        class="ml-2"
                                        :class="
                                            program.is_active
                                                ? 'text-green-700'
                                                : 'text-red-700'
                                        "
                                        >{{
                                            program.is_active
                                                ? "Active"
                                                : "Inactive"
                                        }}</span
                                    >
                                </p>
                                <p class="text-sm text-gray-500">
                                    Program Head:
                                    {{
                                        program.program_head
                                            ? program.program_head.first_name +
                                              " " +
                                              program.program_head.last_name
                                            : "N/A"
                                    }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="route('programs.edit', program.id)"
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
                                    >Update</Link
                                >
                                <button
                                    @click="deleteProgram"
                                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 ml-2"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div class="md:col-span-2">
                                <div class="p-4 border rounded">
                                    <h3 class="font-medium text-gray-700">
                                        Subjects
                                    </h3>
                                    <div class="mt-3 space-y-4">
                                        <div
                                            v-for="curriculum in program.curriculums ||
                                            []"
                                            :key="curriculum.id"
                                        >
                                            <div class="text-sm font-medium">
                                                {{ curriculum.name }}
                                                <span
                                                    class="text-xs text-gray-500"
                                                    >({{
                                                        curriculum.year_effective
                                                    }})</span
                                                >
                                            </div>
                                            <ul
                                                class="mt-2 pl-4 list-disc text-sm text-gray-800"
                                            >
                                                <li
                                                    v-for="cs in curriculum.curriculum_subjects ||
                                                    curriculum.curriculumSubjects ||
                                                    []"
                                                    :key="cs.id"
                                                >
                                                    {{
                                                        cs.subject
                                                            ? cs.subject.code +
                                                              " — " +
                                                              cs.subject.title
                                                            : "—"
                                                    }}
                                                </li>
                                                <li
                                                    v-if="
                                                        !(
                                                            (curriculum.curriculum_subjects &&
                                                                curriculum
                                                                    .curriculum_subjects
                                                                    .length) ||
                                                            (curriculum.curriculumSubjects &&
                                                                curriculum
                                                                    .curriculumSubjects
                                                                    .length)
                                                        )
                                                    "
                                                    class="text-sm text-gray-500"
                                                >
                                                    No subjects found for this
                                                    curriculum.
                                                </li>
                                            </ul>
                                        </div>
                                        <div
                                            v-if="
                                                !(
                                                    program.curriculums &&
                                                    program.curriculums.length
                                                )
                                            "
                                            class="text-sm text-gray-500"
                                        >
                                            No curriculums/subjects found for
                                            this program.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <Link
                            :href="route('programs.index')"
                            class="text-gray-600 hover:text-gray-900 pt-4 block"
                        >
                            ← Back
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
