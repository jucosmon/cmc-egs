<script setup>
import { onBeforeUnmount, ref, watch } from "vue";

const props = defineProps({
    modelValue: {
        type: [File, Object, null],
        default: null,
    },
    currentAvatarUrl: {
        type: String,
        default: "",
    },
    error: {
        type: String,
        default: "",
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    label: {
        type: String,
        default: "Upload Avatar",
    },
    sizeClass: {
        type: String,
        default: "h-24 w-24",
    },
});

const emit = defineEmits(["update:modelValue"]);

const previewUrl = ref(props.currentAvatarUrl || "/images/default-avatar.svg");
let objectUrl = null;

const setPreviewFromFile = (file) => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    }

    if (file) {
        objectUrl = URL.createObjectURL(file);
        previewUrl.value = objectUrl;
        return;
    }

    previewUrl.value = props.currentAvatarUrl || "/images/default-avatar.svg";
};

watch(
    () => props.modelValue,
    (value) => {
        setPreviewFromFile(value || null);
    },
    { immediate: true },
);

watch(
    () => props.currentAvatarUrl,
    (value) => {
        if (!props.modelValue) {
            previewUrl.value = value || "/images/default-avatar.svg";
        }
    },
);

const handleFileChange = (event) => {
    const file = event.target.files?.[0] || null;
    emit("update:modelValue", file);
};

const clearSelectedFile = () => {
    emit("update:modelValue", null);
};

onBeforeUnmount(() => {
    if (objectUrl) {
        URL.revokeObjectURL(objectUrl);
    }
});
</script>

<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <img
            :src="previewUrl"
            alt="Avatar preview"
            class="rounded-full object-cover ring-2 ring-indigo-200"
            :class="sizeClass"
        />

        <div class="flex flex-col gap-2">
            <label
                class="inline-flex cursor-pointer items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                :class="{ 'cursor-not-allowed opacity-60': disabled }"
            >
                <span>Choose New Picture</span>
                <input
                    class="hidden"
                    type="file"
                    accept=".jpg,.png,image/jpeg,image/png"
                    :disabled="disabled"
                    @change="handleFileChange"
                />
            </label>

            <p class="text-xs text-gray-500">{{ label }}</p>

            <p v-if="modelValue?.name" class="text-sm text-gray-700">
                Selected: {{ modelValue.name }}
            </p>

            <button
                v-if="modelValue"
                type="button"
                class="w-fit text-xs font-medium text-gray-600 underline hover:text-gray-800"
                :disabled="disabled"
                @click="clearSelectedFile"
            >
                Remove selected file
            </button>

            <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
        </div>
    </div>
</template>
