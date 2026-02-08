<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Registrar Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-lg font-semibold leading-tight text-[#1f7fa3] sm:text-xl"
            >
                Registrar Dashboard
            </h2>
        </template>

        <div class="py-8">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:px-6 lg:px-8"
            >
                <section
                    class="relative overflow-hidden rounded-2xl bg-slate-900 text-white shadow-lg"
                >
                    <img
                        src="/images/background1.jpg"
                        alt="CMC campus"
                        class="absolute inset-0 h-full w-full object-cover opacity-40"
                    />
                    <div class="relative z-10 p-8 sm:p-12">
                        <div
                            class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                        >
                            <div class="flex items-center gap-4">
                                <img
                                    src="/images/logo.jpg"
                                    alt="CMC logo"
                                    class="h-16 w-16 rounded-full bg-white/90 p-1"
                                />
                                <div>
                                    <p
                                        class="text-sm uppercase tracking-[0.2em] text-white/80"
                                    >
                                        Welcome, Registrar
                                    </p>
                                    <h3 class="mt-2 text-3xl font-semibold">
                                        Carmen Municipal College
                                    </h3>
                                    <p
                                        class="mt-1 max-w-xl text-sm text-white/80"
                                    >
                                        Maintaining accurate records and
                                        responsive enrollment services for every
                                        student.
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex flex-col gap-2 text-sm text-white/85"
                            >
                                <a
                                    class="transition hover:text-white"
                                    href="https://cmcbohol.edu.ph"
                                    target="_blank"
                                    rel="noreferrer"
                                    >cmcbohol.edu.ph</a
                                >
                                <a
                                    class="transition hover:text-white"
                                    href="https://www.facebook.com/cmcbhl"
                                    target="_blank"
                                    rel="noreferrer"
                                    >facebook.com/cmcbhl</a
                                >
                                <span>0917 777 0814 | 038-500-0181</span>
                            </div>
                        </div>
                        <div class="mt-8 grid gap-4 md:grid-cols-3">
                            <div
                                class="rounded-xl bg-white/10 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs uppercase tracking-wide text-white/70"
                                >
                                    Enrollment Queue
                                </p>
                                <p class="mt-2 text-sm text-white/90">
                                    Monitor pending requests and resolve
                                    schedule conflicts.
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-white/10 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs uppercase tracking-wide text-white/70"
                                >
                                    Records Verification
                                </p>
                                <p class="mt-2 text-sm text-white/90">
                                    Ensure transcripts, grades, and credentials
                                    are accurate.
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-white/10 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs uppercase tracking-wide text-white/70"
                                >
                                    Term Setup
                                </p>
                                <p class="mt-2 text-sm text-white/90">
                                    Coordinate academic calendars and enrollment
                                    windows.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <h4 class="text-lg font-semibold text-slate-900">
                            Registrar Hub
                        </h4>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Student Accounts
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    View, add, and modify student accounts.
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Academic Terms & Periods
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    Manage terms and update enrollment and
                                    grading periods.
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Enrollment & Subjects
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    Create enrollments, add or drop subjects,
                                    and manage subject offerings.
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Grades & Reports
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    Update grades, generate statistics, and
                                    issue TOR.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <h4 class="text-lg font-semibold text-slate-900">
                            CMC at a Glance
                        </h4>
                        <p class="mt-2 text-sm text-slate-600">
                            A dynamic institution committed to accessible and
                            quality higher education for the youth of Carmen and
                            surrounding areas.
                        </p>
                        <div class="mt-4 grid gap-3">
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Vision & Mission
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    Empowering students through academic
                                    excellence, research, and community
                                    collaboration.
                                </p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Goals & Objectives
                                </p>
                                <ul
                                    class="mt-2 list-disc pl-5 text-sm text-slate-600"
                                >
                                    <li>
                                        Cultivate excellence in instruction,
                                        research, and extension.
                                    </li>
                                    <li>
                                        Align programs with industry and
                                        community needs.
                                    </li>
                                    <li>
                                        Build meaningful collaborations and
                                        linkages.
                                    </li>
                                </ul>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    About the President
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    Frederico B. Ticong, DPA
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl bg-white shadow-sm">
                    <div class="grid gap-0 md:grid-cols-[1.2fr_1fr]">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-slate-900">
                                Records & Community Support
                            </h4>
                            <p class="mt-2 text-sm text-slate-600">
                                Maintain a trusted academic history while
                                supporting students and faculty in every term.
                            </p>
                            <div
                                class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-700"
                            >
                                Coordinate with program heads, instructors, and
                                the dean for timely academic records.
                            </div>
                        </div>
                        <div class="relative min-h-[220px]">
                            <img
                                src="/images/staffs.jpg"
                                alt="CMC staff"
                                class="h-full w-full rounded-b-2xl object-cover md:rounded-r-2xl md:rounded-bl-none"
                            />
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
