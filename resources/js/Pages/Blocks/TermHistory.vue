<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Term History" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Term History
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
                            Term History
                        </h1>

                        <p class="text-gray-600 mb-6">
                            Historical enrollment data for this block
                        </p>

                        <!-- Placeholder for term history -->
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <p class="text-gray-500">
                                No history data available
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
