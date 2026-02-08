<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />
    <div class="relative min-h-screen overflow-hidden bg-[#0b1b26] text-white">
        <div class="absolute inset-0">
            <img
                src="/images/background1.jpg"
                alt=""
                aria-hidden="true"
                class="h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-[#0b1b26]/70"></div>
            <div
                class="absolute inset-0 bg-gradient-to-b from-[#0b1b26]/50 via-transparent to-[#0b1b26]/80"
            ></div>
        </div>

        <div class="relative z-10 flex min-h-screen flex-col">
            <header class="w-full px-6 py-6">
                <div
                    class="mx-auto flex max-w-6xl items-center justify-between"
                >
                    <div class="flex items-center gap-4">
                        <img
                            src="/images/logo.jpg"
                            alt="Carmen Municipal College logo"
                            class="h-14 w-14 rounded-full ring-2 ring-white/40"
                        />
                        <div>
                            <p
                                class="text-xs font-semibold uppercase tracking-[0.3em] text-white/70"
                            >
                                Carmen Municipal College
                            </p>
                            <p class="text-lg font-semibold text-white">
                                Enrollment and Grade System
                            </p>
                        </div>
                    </div>

                    <nav v-if="canLogin" class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="rounded-full border border-white/40 px-5 py-2 text-sm font-semibold text-white transition hover:border-white hover:bg-white/10 focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                        >
                            Dashboard
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="rounded-full bg-[#2596be] px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-[#0b1b26]/40 transition hover:bg-[#1f7fa3] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                        >
                            Log in
                        </Link>
                    </nav>
                </div>
            </header>

            <main class="flex flex-1 items-center px-6 pb-12">
                <div
                    class="mx-auto flex w-full max-w-6xl flex-col gap-10 md:flex-row md:items-center md:justify-between"
                >
                    <div class="max-w-2xl">
                        <p
                            class="text-sm font-semibold uppercase tracking-[0.3em] text-white/60"
                        >
                            Carmen Municipal College
                        </p>
                        <h1
                            class="mt-4 text-3xl font-semibold leading-tight text-white sm:text-4xl lg:text-5xl"
                        >
                            Enrollment and Grade System
                        </h1>
                        <p
                            class="mt-6 text-base leading-relaxed text-white/80 sm:text-lg"
                        >
                            A renowned educational institution providing quality
                            education highly responsive to the dynamic needs of
                            the community.
                        </p>

                        <div class="mt-8 flex flex-wrap items-center gap-4">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('login')"
                                class="rounded-full bg-[#2596be] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#0b1b26]/40 transition hover:bg-[#1f7fa3] focus:outline-none focus-visible:ring-2 focus-visible:ring-white/60"
                            >
                                Log in to continue
                            </Link>
                            <div
                                class="rounded-full border border-white/30 px-5 py-3 text-xs uppercase tracking-[0.2em] text-white/70"
                            >
                                Serving the community
                            </div>
                        </div>
                    </div>

                    <div
                        class="w-full max-w-sm rounded-3xl border border-white/20 bg-white/10 p-6 shadow-2xl shadow-[#0b1b26]/50 backdrop-blur"
                    >
                        <p
                            class="text-xs font-semibold uppercase tracking-[0.35em] text-white/70"
                        >
                            CMC EGS
                        </p>
                        <h2 class="mt-4 text-xl font-semibold text-white">
                            Streamlined enrollment, clear academic records.
                        </h2>
                        <p class="mt-4 text-sm leading-relaxed text-white/75">
                            Secure access for students, instructors, and staff.
                            Designed to keep learning journeys organized and
                            transparent.
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
