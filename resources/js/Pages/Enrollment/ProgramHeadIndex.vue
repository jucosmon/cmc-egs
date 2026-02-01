<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Manage Enrollment" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Manage Enrollment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            Manage Enrollment
                        </h1>
                        <p class="mt-2 text-gray-600 text-lg">
                            Create and manage blocks, schedules, and instructor
                            assignments for your program
                        </p>

                        <!-- Quick Links -->
                        <div class="mt-8 flex gap-3 flex-wrap">
                            <Link
                                href="/blocks"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium"
                            >
                                📚 View Blocks
                            </Link>
                            <Link
                                href="/blocks/create"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium"
                            >
                                ➕ Create Block
                            </Link>
                        </div>

                        <!-- Placeholder for program head interface -->
                        <div class="mt-8 bg-gray-50 rounded-lg p-6 text-center">
                            <p class="text-gray-500">
                                Program head enrollment management interface
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
