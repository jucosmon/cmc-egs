<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    programs: Array,
});

const form = useForm({
    code: "",
    program_id: "",
    admission_year: new Date().getFullYear(),
    status: "active",
});

const submit = () => {
    form.post("/blocks");
};
</script>

<template>
    <Head title="Create Block" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Block
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <Link
                            href="/blocks"
                            class="text-blue-600 hover:text-blue-700 mb-4 inline-block"
                        >
                            ← Back to Blocks
                        </Link>

                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            Create New Block
                        </h1>
                        <p class="text-gray-600 mb-6">
                            Fill in the information below to create a new block.
                        </p>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Block Code *
                                </label>
                                <input
                                    v-model="form.code"
                                    type="text"
                                    required
                                    placeholder="e.g., BS-CS-2023-A"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <p
                                    v-if="form.errors.code"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Program *
                                </label>
                                <select
                                    v-model="form.program_id"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="">Select a program</option>
                                    <option
                                        v-for="program in programs"
                                        :key="program.id"
                                        :value="program.id"
                                    >
                                        {{ program.name }}
                                    </option>
                                </select>
                                <p
                                    v-if="form.errors.program_id"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.program_id }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Admission Year *
                                </label>
                                <input
                                    v-model.number="form.admission_year"
                                    type="number"
                                    required
                                    :min="new Date().getFullYear() - 10"
                                    :max="new Date().getFullYear() + 1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                />
                                <p
                                    v-if="form.errors.admission_year"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.admission_year }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-2"
                                >
                                    Status *
                                </label>
                                <select
                                    v-model="form.status"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="graduated">Graduated</option>
                                </select>
                                <p
                                    v-if="form.errors.status"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.status }}
                                </p>
                            </div>

                            <div class="flex gap-3 pt-4">
                                <Link
                                    href="/blocks"
                                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium text-center"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium disabled:opacity-50"
                                >
                                    {{
                                        form.processing
                                            ? "Creating..."
                                            : "Create Block"
                                    }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
