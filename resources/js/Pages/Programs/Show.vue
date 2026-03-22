<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const flash = computed(() => page.props.flash || {});
const archiveError = computed(() => page.props.errors?.archive || null);

const props = defineProps({
    program: Object,
});

const archiveProgram = () => {
    if (!confirm("Are you sure you want to archive this program?")) return;

    router.delete(route("programs.destroy", props.program.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route("programs.index"));
        },
    });
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
                                                : "Archived"
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
                                    v-if="program.is_active"
                                    @click="archiveProgram"
                                    class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 ml-2"
                                >
                                    Archive
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
