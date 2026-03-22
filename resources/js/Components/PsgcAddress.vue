<script setup>
import { onMounted, ref, watch } from "vue";

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({
            province_code: "",
            province_name: "",
            city_code: "",
            city_name: "",
            barangay_code: "",
            barangay_name: "",
        }),
    },
    label: {
        type: String,
        default: "Address",
    },
    required: {
        type: Boolean,
        default: false,
    },
    error: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["update:modelValue", "change"]);

const provinces = ref([]);
const cities = ref([]);
const barangays = ref([]);

const selectedProvince = ref(props.modelValue.province_code || "");
const selectedCity = ref(props.modelValue.city_code || "");
const selectedBarangay = ref(props.modelValue.barangay_code || "");

const loadingProvinces = ref(false);
const loadingCities = ref(false);
const loadingBarangays = ref(false);

const sortByName = (items) =>
    [...items].sort((a, b) =>
        String(a?.name || "").localeCompare(String(b?.name || ""), undefined, {
            sensitivity: "base",
        }),
    );

// Fetch provinces on mount
onMounted(async () => {
    await fetchProvinces();
});

// Fetch provinces from PSGC API
const fetchProvinces = async () => {
    loadingProvinces.value = true;
    try {
        const response = await fetch("https://psgc.gitlab.io/api/provinces/");
        const data = await response.json();
        provinces.value = sortByName(data);

        // If initial value is provided, fetch cities and barangays
        if (props.modelValue.province_code) {
            await fetchCities(props.modelValue.province_code, true);
            if (props.modelValue.city_code) {
                await fetchBarangays(props.modelValue.city_code, true);
            }
        }
    } catch (error) {
        console.error("Error fetching provinces:", error);
    } finally {
        loadingProvinces.value = false;
    }
};

// Fetch cities for selected province
const fetchCities = async (provinceCode, preserveSelection = false) => {
    if (!provinceCode) {
        cities.value = [];
        barangays.value = [];
        selectedCity.value = "";
        selectedBarangay.value = "";
        return;
    }

    loadingCities.value = true;
    try {
        const response = await fetch(
            `https://psgc.gitlab.io/api/provinces/${provinceCode}/cities-municipalities/`,
        );
        const data = await response.json();
        cities.value = sortByName(data);
        if (!preserveSelection) {
            barangays.value = [];
            selectedCity.value = "";
            selectedBarangay.value = "";
        }
    } catch (error) {
        console.error("Error fetching cities:", error);
    } finally {
        loadingCities.value = false;
    }
};

// Fetch barangays for selected city
const fetchBarangays = async (cityCode, preserveSelection = false) => {
    if (!cityCode) {
        barangays.value = [];
        selectedBarangay.value = "";
        return;
    }

    loadingBarangays.value = true;
    try {
        const response = await fetch(
            `https://psgc.gitlab.io/api/cities-municipalities/${cityCode}/barangays/`,
        );
        const data = await response.json();
        barangays.value = sortByName(data);
        if (!preserveSelection) {
            selectedBarangay.value = "";
        }
    } catch (error) {
        console.error("Error fetching barangays:", error);
    } finally {
        loadingBarangays.value = false;
    }
};

// Watch province change
watch(selectedProvince, async (newValue) => {
    const selectedProvinceObj = provinces.value.find(
        (p) => p.code === newValue,
    );
    selectedCity.value = "";
    selectedBarangay.value = "";

    if (newValue) {
        await fetchCities(newValue);
    } else {
        cities.value = [];
        barangays.value = [];
    }

    emitUpdate();
});

// Watch city change
watch(selectedCity, async (newValue) => {
    const selectedCityObj = cities.value.find((c) => c.code === newValue);
    selectedBarangay.value = "";

    if (newValue) {
        await fetchBarangays(newValue);
    } else {
        barangays.value = [];
    }

    emitUpdate();
});

// Watch barangay change
watch(selectedBarangay, () => {
    emitUpdate();
});

// Emit update
const emitUpdate = () => {
    const selectedProvinceObj = provinces.value.find(
        (p) => p.code === selectedProvince.value,
    );
    const selectedCityObj = cities.value.find(
        (c) => c.code === selectedCity.value,
    );
    const selectedBarangayObj = barangays.value.find(
        (b) => b.code === selectedBarangay.value,
    );

    const updatedValue = {
        province_code: selectedProvince.value,
        province_name: selectedProvinceObj?.name || "",
        city_code: selectedCity.value,
        city_name: selectedCityObj?.name || "",
        barangay_code: selectedBarangay.value,
        barangay_name: selectedBarangayObj?.name || "",
    };

    emit("update:modelValue", updatedValue);
    emit("change", updatedValue);
};

// Get full address string
const getFullAddress = () => {
    const parts = [
        props.modelValue.barangay_name,
        props.modelValue.city_name,
        props.modelValue.province_name,
    ].filter(Boolean);
    return parts.join(", ");
};
</script>

<template>
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">
                {{ label }}
                <span v-if="required" class="text-red-500">*</span>
            </label>
        </div>

        <!-- Province -->
        <div>
            <label
                for="province"
                class="block text-xs font-medium text-gray-600 uppercase"
            >
                Province
                <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select
                    id="province"
                    v-model="selectedProvince"
                    :disabled="loadingProvinces"
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                    <option value="">
                        {{
                            loadingProvinces
                                ? "Loading provinces..."
                                : "Select Province"
                        }}
                    </option>
                    <option
                        v-for="province in provinces"
                        :key="province.code"
                        :value="province.code"
                    >
                        {{ province.name }}
                    </option>
                </select>
                <div
                    v-if="loadingProvinces"
                    class="absolute right-3 top-3 mt-1"
                >
                    <div
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-indigo-600"
                    ></div>
                </div>
            </div>
        </div>

        <!-- City/Municipality -->
        <div>
            <label
                for="city"
                class="block text-xs font-medium text-gray-600 uppercase"
            >
                City / Municipality
                <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select
                    id="city"
                    v-model="selectedCity"
                    :disabled="
                        !selectedProvince ||
                        loadingCities ||
                        cities.length === 0
                    "
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                    <option value="">
                        {{
                            !selectedProvince
                                ? "Select Province first"
                                : loadingCities
                                  ? "Loading cities..."
                                  : cities.length === 0
                                    ? "No cities found"
                                    : "Select City/Municipality"
                        }}
                    </option>
                    <option
                        v-for="city in cities"
                        :key="city.code"
                        :value="city.code"
                    >
                        {{ city.name }}
                    </option>
                </select>
                <div v-if="loadingCities" class="absolute right-3 top-3 mt-1">
                    <div
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-indigo-600"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Barangay -->
        <div>
            <label
                for="barangay"
                class="block text-xs font-medium text-gray-600 uppercase"
            >
                Barangay
                <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <select
                    id="barangay"
                    v-model="selectedBarangay"
                    :disabled="
                        !selectedCity ||
                        loadingBarangays ||
                        barangays.length === 0
                    "
                    class="mt-1 block w-full rounded-md border border-gray-300 px-3 py-2 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                >
                    <option value="">
                        {{
                            !selectedCity
                                ? "Select City first"
                                : loadingBarangays
                                  ? "Loading barangays..."
                                  : barangays.length === 0
                                    ? "No barangays found"
                                    : "Select Barangay"
                        }}
                    </option>
                    <option
                        v-for="barangay in barangays"
                        :key="barangay.code"
                        :value="barangay.code"
                    >
                        {{ barangay.name }}
                    </option>
                </select>
                <div
                    v-if="loadingBarangays"
                    class="absolute right-3 top-3 mt-1"
                >
                    <div
                        class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-indigo-600"
                    ></div>
                </div>
            </div>
        </div>

        <!-- Error Message -->
        <div
            v-if="error"
            class="mt-2 rounded-md bg-red-50 p-3 text-sm text-red-700"
        >
            {{ error }}
        </div>

        <!-- Full Address Display -->
        <div v-if="getFullAddress()" class="mt-3 rounded-md bg-blue-50 p-3">
            <p class="text-xs font-medium text-gray-600">Selected Address:</p>
            <p class="text-sm text-blue-900">{{ getFullAddress() }}</p>
        </div>
    </div>
</template>
