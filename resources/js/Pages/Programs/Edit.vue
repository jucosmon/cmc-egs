<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    program: Object,
    programHeads: Array,
});

const form = useForm({
    name: props.program.name,
    code: props.program.code,
    program_head_id: props.program.program_head_id,
    is_active: props.program.is_active,
});

const submit = () => {
    form.put(route("programs.update", props.program.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Program" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Edit Program
                            </h2>
                            <Link
                                :href="route('programs.show', program.id)"
                                class="text-gray-600 hover:text-gray-900"
                                >← Back</Link
                            >
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div>
                                <label
                                    for="name"
                                    class="block text-sm font-medium text-gray-700"
                                    >Program Name *</label
                                >
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
                                    >Program Code *</label
                                >
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

                            <!-- Program Head -->
                            <div>
                                <label
                                    for="program_head_id"
                                    class="block text-sm font-medium text-gray-700"
                                    >Program Head (Optional)</label
                                >
                                <select
                                    id="program_head_id"
                                    v-model="form.program_head_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="null">
                                        No program head
                                    </option>
                                    <option
                                        v-for="program_head in programHeads"
                                        :key="program_head.id"
                                        :value="program_head.id"
                                    >
                                        {{ program_head.name }} ({{
                                            program_head.official_id
                                        }})
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.program_head_id"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.program_head_id }}
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
                                    >Active</label
                                >
                            </div>

                            <!-- Buttons -->
                            <div
                                class="flex items-center justify-end space-x-3 pt-4 border-t"
                            >
                                <Link
                                    :href="route('programs.show', program.id)"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300"
                                    >Cancel</Link
                                >
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{
                                        form.processing
                                            ? "Updating..."
                                            : "Update Program"
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
