<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    departments: Array,
    programHeads: Array,
});

const form = useForm({
    name: "",
    code: "",
    degree_type: "bachelors",
    total_units: 120,
    duration_years: 4,
    description: "",
    department_id:
        props.departments && props.departments.length > 0
            ? Number(props.departments[0].id)
            : null,
    program_head_id: null,
    is_active: true,
});

const submit = () => {
    form.post(route("programs.store"));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Program" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Create Program
                            </h2>
                            <Link
                                :href="route('programs.index')"
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
                                    Program Name *
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g., Bachelor of Science in Computer Science"
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
                                    Program Code *
                                </label>
                                <input
                                    id="code"
                                    v-model="form.code"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g., BSCS"
                                />
                                <div
                                    v-if="form.errors.code"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.code }}
                                </div>
                            </div>

                            <!-- Department -->
                            <div>
                                <label
                                    for="department_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Department *
                                </label>
                                <select
                                    id="department_id"
                                    v-model.number="form.department_id"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="null" disabled>
                                        Select a department...
                                    </option>
                                    <option
                                        v-for="dept in departments"
                                        :key="dept.id"
                                        :value="Number(dept.id)"
                                    >
                                        {{ dept.name }} ({{ dept.code }})
                                    </option>
                                </select>
                                <div
                                    v-if="form.errors.department_id"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.department_id }}
                                </div>
                            </div>

                            <!-- Degree Type -->
                            <div>
                                <label
                                    for="degree_type"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Degree Type *
                                </label>
                                <select
                                    id="degree_type"
                                    v-model="form.degree_type"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="bachelors">
                                        Bachelor's
                                    </option>
                                    <option value="masters">Master's</option>
                                    <option value="doctors">Doctorate</option>
                                    <option value="associate">Associate</option>
                                </select>
                                <div
                                    v-if="form.errors.degree_type"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.degree_type }}
                                </div>
                            </div>

                            <!-- Total Units -->
                            <div>
                                <label
                                    for="total_units"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Total Units *
                                </label>
                                <input
                                    id="total_units"
                                    v-model.number="form.total_units"
                                    type="number"
                                    min="1"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g., 120"
                                />
                                <div
                                    v-if="form.errors.total_units"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.total_units }}
                                </div>
                            </div>

                            <!-- Duration Years -->
                            <div>
                                <label
                                    for="duration_years"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Duration (Years) *
                                </label>
                                <input
                                    id="duration_years"
                                    v-model.number="form.duration_years"
                                    type="number"
                                    min="1"
                                    max="10"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="e.g., 4"
                                />
                                <div
                                    v-if="form.errors.duration_years"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.duration_years }}
                                </div>
                            </div>

                            <!-- Description -->
                            <div>
                                <label
                                    for="description"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Program description..."
                                ></textarea>
                                <div
                                    v-if="form.errors.description"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.description }}
                                </div>
                            </div>

                            <!-- Program Head -->
                            <div>
                                <label
                                    for="program_head_id"
                                    class="block text-sm font-medium text-gray-700"
                                >
                                    Program Head (Optional)
                                </label>
                                <select
                                    id="program_head_id"
                                    v-model="form.program_head_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option :value="null">
                                        Select a program head...
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
                                >
                                    Active
                                </label>
                            </div>

                            <!-- Buttons -->
                            <div
                                class="flex items-center justify-end space-x-3 pt-4 border-t"
                            >
                                <Link
                                    :href="route('programs.index')"
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
                                            ? "Creating..."
                                            : "Create Program"
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
