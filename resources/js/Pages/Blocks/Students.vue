<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Block Students" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Block Students
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <Link
                            href="/enrollments"
                            class="text-blue-600 hover:text-blue-700 mb-4 inline-block"
                        >
                            ← Back to Enrollment
                        </Link>

                        <h1 class="text-3xl font-bold text-gray-900 mb-6">
                            Block Students
                        </h1>

                        <p class="text-gray-600 mb-6">
                            Students enrolled in this block
                        </p>

                        <!-- Placeholder for student list -->
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <p class="text-gray-500">
                                No students data available
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
