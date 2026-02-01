<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";

const { term } = defineProps({ term: Object });

const toDateInput = (iso) => {
    if (!iso) return "";
    try {
        const d = new Date(iso);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, "0");
        const dd = String(d.getDate()).padStart(2, "0");
        return `${yyyy}-${mm}-${dd}`;
    } catch (e) {
        return iso;
    }
};

const form = useForm({
    academic_year: term.academic_year || "",
    semester: term.semester || "first",
    start_date: toDateInput(term.start_date) || "",
    end_date: toDateInput(term.end_date) || "",
    start_enrollment: toDateInput(term.start_enrollment) || "",
    end_enrollment: toDateInput(term.end_enrollment) || "",
    start_mid_grade: toDateInput(term.start_mid_grade) || "",
    end_mid_grade: toDateInput(term.end_mid_grade) || "",
    start_final_grade: toDateInput(term.start_final_grade) || "",
    end_final_grade: toDateInput(term.end_final_grade) || "",
    is_active: term.is_active || false,
});

const submit = () => {
    if (!confirm("Do you confirm the changes?")) return;
    form.put(route("terms.update", term.id));
};

const activate = () => {
    if (
        confirm("Do you confirm to activate this term to be the current term?")
    ) {
        router.post(route("terms.activate", term.id));
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Academic Term" />

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-gray-800">
                                Update Academic Term
                            </h2>
                            <Link
                                :href="route('terms.show', term.id)"
                                class="text-gray-600 hover:text-gray-900"
                                >← Back</Link
                            >
                        </div>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Academic Year *</label
                                >
                                <input
                                    v-model="form.academic_year"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                />
                                <div
                                    v-if="form.errors.academic_year"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.academic_year }}
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Semester *</label
                                >
                                <select
                                    v-model="form.semester"
                                    class="mt-1 block w-full rounded-md border-gray-300"
                                >
                                    <option value="first">First</option>
                                    <option value="second">Second</option>
                                    <option value="summer">Summer</option>
                                </select>
                                <div
                                    v-if="form.errors.semester"
                                    class="text-red-600 text-sm mt-1"
                                >
                                    {{ form.errors.semester }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Start Date *</label
                                    >
                                    <input
                                        v-model="form.start_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >End Date *</label
                                    >
                                    <input
                                        v-model="form.end_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Enrollment Start *</label
                                    >
                                    <input
                                        v-model="form.start_enrollment"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Enrollment End *</label
                                    >
                                    <input
                                        v-model="form.end_enrollment"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Midterm Start *</label
                                    >
                                    <input
                                        v-model="form.start_mid_grade"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Midterm End *</label
                                    >
                                    <input
                                        v-model="form.end_mid_grade"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Final Start *</label
                                    >
                                    <input
                                        v-model="form.start_final_grade"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700"
                                        >Final End *</label
                                    >
                                    <input
                                        v-model="form.end_final_grade"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300"
                                    />
                                </div>
                            </div>

                            <div class="flex items-center">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-indigo-600"
                                />
                                <label
                                    for="is_active"
                                    class="ml-2 block text-sm text-gray-700"
                                    >Active</label
                                >
                            </div>

                            <div
                                class="flex items-center justify-between space-x-3 pt-4 border-t"
                            >
                                <div class="space-x-3">
                                    <button
                                        type="button"
                                        @click="activate"
                                        v-if="!form.is_active"
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                                    >
                                        Activate
                                    </button>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <Link
                                        :href="route('terms.show', term.id)"
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
                                                ? "Saving..."
                                                : "Save Changes"
                                        }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
