<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();
const flash = computed(() => page.props.flash || {});
const archiveError = computed(() => page.props.errors?.archive || null);

const props = defineProps({
    department: Object,
});

const archiveDepartment = () => {
    if (!confirm("Are you sure you want to archive this department?")) return;

    router.delete(route("departments.destroy", props.department.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route("departments.index"));
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Department: ${department.name}`" />

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
                                    {{ department.name }}
                                </h2>
                                <p class="text-sm text-gray-500">
                                    Code: {{ department.code }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Status:
                                    <span
                                        class="ml-2"
                                        :class="
                                            department.is_active
                                                ? 'text-green-700'
                                                : 'text-red-700'
                                        "
                                        >{{
                                            department.is_active
                                                ? "Active"
                                                : "Archived"
                                        }}</span
                                    >
                                </p>
                                <p class="text-sm text-gray-500">
                                    Dean:
                                    {{
                                        department.dean
                                            ? department.dean.first_name +
                                              " " +
                                              department.dean.last_name
                                            : "N/A"
                                    }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="
                                        route('departments.edit', department.id)
                                    "
                                    class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
                                    >Update</Link
                                >
                                <button
                                    v-if="department.is_active"
                                    @click="archiveDepartment"
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
                                        Programs
                                        <span class="text-xs text-gray-500">{{
                                            department.programs_count ??
                                            (department.programs
                                                ? department.programs.length
                                                : 0)
                                        }}</span>
                                    </h3>
                                    <div class="mt-3">
                                        <ul
                                            class="space-y-2 text-sm text-gray-800"
                                        >
                                            <li
                                                v-for="program in department.programs ||
                                                []"
                                                :key="program.id"
                                                class="flex items-center justify-between"
                                            >
                                                <div>
                                                    <div class="font-medium">
                                                        {{ program.name }}
                                                    </div>
                                                    <div
                                                        class="text-xs text-gray-500"
                                                    >
                                                        {{
                                                            program.code || "—"
                                                        }}
                                                    </div>
                                                </div>
                                            </li>
                                            <li
                                                v-if="
                                                    !(
                                                        department.programs &&
                                                        department.programs
                                                            .length
                                                    )
                                                "
                                                class="text-sm text-gray-500"
                                            >
                                                No programs found for this
                                                department.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <Link
                            :href="route('departments.index')"
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
