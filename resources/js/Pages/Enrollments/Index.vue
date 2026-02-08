<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Enrollment" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-lg font-semibold leading-tight text-[#1f7fa3] sm:text-xl"
            >
                Enrollment
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-3xl font-bold text-gray-900 mb-6">
                            Enrollment Management
                        </h1>

                        <p class="text-gray-600 mb-6">
                            Manage your enrollment and schedules
                        </p>

                        <!-- Placeholder content -->
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <p class="text-gray-500">
                                Enrollment content goes here
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
