<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    block: Object,
    activeTerm: Object,
    instructors: Array,
    curriculumSubjects: Array,
});
</script>

<template>
    <Head title="Block Details" />

    <AuthenticatedLayout>
        <!-- HEADER -->
        <template #header>
            <h2
                class="text-lg font-semibold leading-tight text-[#1f7fa3] sm:text-xl"
            >
                Block Details
            </h2>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-6xl sm:px-6 lg:px-8 space-y-6">
                <!-- BACK -->
                <Link
                    href="/blocks"
                    class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700"
                >
                    ← Back to Blocks
                </Link>

                <!-- BLOCK INFO -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">
                        Block Information
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Block Code</span>
                            <p class="font-medium">
                                {{ block.code }}
                            </p>
                        </div>

                        <div>
                            <span class="text-gray-500">Program</span>
                            <p class="font-medium">
                                {{ block.program?.name ?? "—" }}
                            </p>
                        </div>

                        <div>
                            <span class="text-gray-500">Admission Year</span>
                            <p class="font-medium">
                                {{ block.admission_year }}
                            </p>
                        </div>

                        <div>
                            <span class="text-gray-500">Status</span>
                            <span
                                class="inline-flex px-2 py-1 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-green-100 text-green-700':
                                        block.status === 'active',
                                    'bg-gray-200 text-gray-700':
                                        block.status !== 'active',
                                }"
                            >
                                {{ block.status }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- STUDENTS TABLE -->
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Students in this Block
                        </h3>

                        <span class="text-sm text-gray-500">
                            Total: {{ block.students.length }}
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table
                            class="min-w-full border border-gray-200 rounded-lg"
                        >
                            <thead
                                class="bg-gray-100 text-gray-700 text-sm uppercase"
                            >
                                <tr>
                                    <th class="px-4 py-3 text-left border-b">
                                        Student No.
                                    </th>
                                    <th class="px-4 py-3 text-left border-b">
                                        Full Name
                                    </th>
                                    <th class="px-4 py-3 text-left border-b">
                                        Email
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="divide-y">
                                <tr
                                    v-for="student in block.students"
                                    :key="student.id"
                                    class="hover:bg-gray-50 transition"
                                >
                                    <td
                                        class="px-4 py-3 text-sm font-mono text-gray-800"
                                    >
                                        {{ student.student_number ?? "—" }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-800">
                                        {{ student.user.last_name }},
                                        {{ student.user.first_name }}
                                    </td>

                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        {{ student.user.email }}
                                    </td>
                                </tr>

                                <tr v-if="block.students.length === 0">
                                    <td
                                        colspan="3"
                                        class="px-4 py-6 text-center text-gray-500 text-sm"
                                    >
                                        No students assigned to this block
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
