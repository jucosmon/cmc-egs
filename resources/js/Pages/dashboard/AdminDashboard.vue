<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Admin Dashboard
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
                                        Welcome, Admin
                                    </p>
                                    <h3 class="mt-2 text-3xl font-semibold">
                                        Carmen Municipal College
                                    </h3>
                                    <p
                                        class="mt-1 max-w-xl text-sm text-white/80"
                                    >
                                        Keep the system reliable, secure, and
                                        ready for academic operations.
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
                                    System Health
                                </p>
                                <p class="mt-2 text-sm text-white/90">
                                    Monitor uptime, background jobs, and
                                    performance.
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-white/10 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs uppercase tracking-wide text-white/70"
                                >
                                    Access Control
                                </p>
                                <p class="mt-2 text-sm text-white/90">
                                    Manage roles, permissions, and account
                                    security.
                                </p>
                            </div>
                            <div
                                class="rounded-xl bg-white/10 p-4 backdrop-blur"
                            >
                                <p
                                    class="text-xs uppercase tracking-wide text-white/70"
                                >
                                    Data Protection
                                </p>
                                <p class="mt-2 text-sm text-white/90">
                                    Keep backups, logs, and data policies up to
                                    date.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-white p-6 shadow-sm">
                        <h4 class="text-lg font-semibold text-slate-900">
                            Admin Hub
                        </h4>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Account Information
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    View and update your profile details.
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Staff Accounts
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    View, add, and modify dean, program head,
                                    registrar, and instructor accounts.
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Student Accounts
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    View student accounts and reset passwords.
                                </p>
                            </div>
                            <div class="rounded-xl border border-slate-100 p-4">
                                <p class="text-sm font-semibold text-slate-800">
                                    Departments
                                </p>
                                <p class="mt-1 text-sm text-slate-600">
                                    View, add, modify, and archive departments.
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
                                Operations & Community Service
                            </h4>
                            <p class="mt-2 text-sm text-slate-600">
                                Support stakeholders by keeping the platform
                                stable and data consistent.
                            </p>
                            <div
                                class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-700"
                            >
                                Align system updates with institutional goals
                                and academic calendar needs.
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
