<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div v-if="links && links.length > 3" class="flex flex-wrap justify-center gap-1 mt-6">
        <template v-for="(link, index) in links" :key="index">
            <Link
                v-if="link.url"
                :href="link.url"
                v-html="link.label"
                :class="[
                    'px-3 py-2 text-sm rounded border transition-colors',
                    link.active
                        ? 'bg-indigo-600 text-white border-indigo-600'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'
                ]"
            />
            <span
                v-else
                v-html="link.label"
                :class="[
                    'px-3 py-2 text-sm rounded border transition-colors',
                    'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed'
                ]"
            />
        </template>
    </div>
</template>
