<script setup>
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-gradient-to-b from-[#e9f3f7] via-white to-[#f6fbfd] px-4 py-8"
    >
        <div class="flex flex-col items-center text-center">
            <Link href="/" class="inline-flex items-center gap-3">
                <ApplicationLogo
                    class="h-16 w-16 rounded-full ring-2 ring-[#2596be]/30"
                />
                <div class="text-left">
                    <p
                        class="text-sm font-semibold uppercase tracking-[0.2em] text-[#2596be]"
                    >
                        Carmen Municipal College
                    </p>
                    <p class="text-lg font-semibold text-slate-800">
                        Enrollment and Grade System
                    </p>
                </div>
            </Link>
        </div>

        <div
            class="mt-8 w-full max-w-md overflow-hidden rounded-2xl bg-white/90 px-6 py-6 shadow-xl shadow-slate-200/70 backdrop-blur"
        >
            <slot />
        </div>
    </div>
</template>
