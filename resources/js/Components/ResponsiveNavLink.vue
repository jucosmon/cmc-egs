<script setup>
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? "block w-full ps-3 pe-4 py-2 border-l-4 border-white text-start text-base font-semibold text-white bg-white/10 focus:outline-none focus:text-white focus:bg-white/15 focus:border-white transition duration-150 ease-in-out"
        : "block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-white/80 hover:text-white hover:bg-white/10 hover:border-white/60 focus:outline-none focus:text-white focus:bg-white/10 focus:border-white/70 transition duration-150 ease-in-out",
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
