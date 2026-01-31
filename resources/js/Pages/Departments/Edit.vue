<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    department: Object,
    deans: Array,
});

const form = useForm({
    name: props.department.name,
    code: props.department.code,
    dean_id: props.department.dean_id,
    is_active: props.department.is_active,
});

const submit = () => {
    form.put(route("departments.update", props.department.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Department" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Edit Department
                            </h2>
                            <Link
                                :href="route('departments.show', department.id)"
                                class="text-gray-600 hover:text-gray-900"
                            >
                                ← Back
                            </Link>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label
                                    for="name"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Department Name *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div
                                    v-if="form.errors.name"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Code -->
                            <div>
                                <label
                                    for="code"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Department Code *
                                </label>
                                <input
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div
                                    v-if="form.errors.code"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.code }}
                                </div>
                            </div>

                            <!-- Dean -->
                            <div>
                                <label
                                    for="dean_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Dean (Optional)
                                </label>
                                <select
                                    id="dean_id"
                                    v-model="form.dean_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="null">
                                        No dean assigned
                                    </option>
                                    <option
                                        v-for="dean in deans"
                                        :key="dean.id"
                                        :value="dean.id"
                                    >
                                        {{ dean.name }} ({{ dean.official_id }})
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.dean_id"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.dean_id }}
                                </div>
                            </div>

                            <!-- Is Active -->
                            <div class="flex items-center">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label
                                    for="is_active"
                                    class="ml-2 block text-sm text-gray-700"
                                >
                                    Active
                                </label>
                            </div>

                            <!-- Buttons -->
                            <div
                                class="flex items-center justify-end space-x-3 pt-4 border-t"
                            >
                                <Link
                                    :href="
                                        route('departments.show', department.id)
                                    "
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{
                                        form.processing
                                            ? "Updating..."
                                            : "Update Department"
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
