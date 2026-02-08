<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    classes: Array,
    academicTerms: Array,
    activeTerm: Object,
    filters: Object,
});

const selectedTerm = ref(
    props.filters?.academic_term_id || props.activeTerm?.id || "",
);

const updateFilters = () => {
    router.get(
        route("grades.index"),
        { academic_term_id: selectedTerm.value || null },
        { preserveState: true, preserveScroll: true },
    );
};

const groupedClasses = computed(() => {
    const groups = {};
    (props.classes || []).forEach((item) => {
        const subject = item.curriculum_subject?.subject;
        const key = subject?.id || item.id;
        if (!groups[key]) {
            groups[key] = {
                subject,
                items: [],
            };
        }
        groups[key].items.push(item);
    });
    return Object.values(groups);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Manage Grades" />

        <template #header>
            <h2
                class="text-lg font-semibold leading-tight text-[#1f7fa3] sm:text-xl"
            >
                Manage Grades (Instructor)
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div
                            class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
                        >
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Assigned Classes
                                </h3>
                                <p class="text-sm text-gray-600">
                                    Select a class schedule to input grades for
                                    the open period.
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <label class="text-sm text-gray-600">
                                    Term
                                </label>
                                <select
                                    v-model="selectedTerm"
                                    @change="updateFilters"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">All Terms</option>
                                    <option
                                        v-for="term in academicTerms"
                                        :key="term.id"
                                        :value="term.id"
                                    >
                                        {{ term.academic_year }} -
                                        {{ term.semester }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div
                            v-if="!classes || classes.length === 0"
                            class="mt-6 rounded-lg border border-dashed p-8 text-center text-gray-500"
                        >
                            No assigned classes found for the current term.
                        </div>

                        <div v-else class="mt-6 space-y-6">
                            <div
                                v-for="group in groupedClasses"
                                :key="group.subject?.id || group.items[0].id"
                                class="border rounded-lg"
                            >
                                <div class="px-4 py-3 bg-gray-50 border-b">
                                    <h4 class="font-semibold text-gray-800">
                                        {{ group.subject?.code }} -
                                        {{ group.subject?.title }}
                                    </h4>
                                </div>
                                <div class="divide-y">
                                    <div
                                        v-for="schedule in group.items"
                                        :key="schedule.id"
                                        class="flex flex-col gap-3 p-4 md:flex-row md:items-center md:justify-between"
                                    >
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Block:
                                                <span class="font-medium">
                                                    {{
                                                        schedule.block?.code ||
                                                        "N/A"
                                                    }}
                                                </span>
                                            </p>
                                            <p class="text-sm text-gray-700">
                                                Schedule:
                                                <span class="font-medium">
                                                    {{ schedule.day }}
                                                    {{ schedule.time_start }} -
                                                    {{ schedule.time_end }}
                                                </span>
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Midterm:
                                                <span
                                                    class="font-medium"
                                                    :class="
                                                        schedule.midterm_open
                                                            ? 'text-green-600'
                                                            : 'text-gray-500'
                                                    "
                                                >
                                                    {{
                                                        schedule.midterm_submitted_at
                                                            ? "Submitted"
                                                            : schedule.midterm_open
                                                              ? "Open"
                                                              : "Closed"
                                                    }}
                                                </span>
                                                • Final:
                                                <span
                                                    class="font-medium"
                                                    :class="
                                                        schedule.final_open
                                                            ? 'text-green-600'
                                                            : 'text-gray-500'
                                                    "
                                                >
                                                    {{
                                                        schedule.final_submitted_at
                                                            ? "Submitted"
                                                            : schedule.final_open
                                                              ? "Open"
                                                              : "Closed"
                                                    }}
                                                </span>
                                            </p>
                                        </div>

                                        <div>
                                            <Link
                                                :href="
                                                    route(
                                                        'grades.edit',
                                                        schedule.id,
                                                    )
                                                "
                                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                                            >
                                                View
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
