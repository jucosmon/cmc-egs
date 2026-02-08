<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm } from "@inertiajs/vue3";

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    account: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    email: props.account.email || "",
    first_name: props.account.first_name || "",
    middle_name: props.account.middle_name || "",
    last_name: props.account.last_name || "",
    phone: props.account.phone || "",
    address: props.account.address || "",
    date_of_birth: props.account.date_of_birth
        ? String(props.account.date_of_birth).slice(0, 10)
        : "",
    sex: (() => {
        const value = props.account.sex
            ? String(props.account.sex).toLowerCase()
            : "";
        if (value === "male") return "Male";
        if (value === "female") return "Female";
        return "";
    })(),
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Update Profile</h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account details and contact information.
            </p>
        </header>

        <form
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <InputLabel for="first_name" value="First Name" />

                    <TextInput
                        id="first_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.first_name"
                        required
                        autofocus
                        autocomplete="given-name"
                    />

                    <InputError
                        class="mt-2"
                        :message="form.errors.first_name"
                    />
                </div>

                <div>
                    <InputLabel for="middle_name" value="Middle Name" />

                    <TextInput
                        id="middle_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.middle_name"
                        autocomplete="additional-name"
                    />

                    <InputError
                        class="mt-2"
                        :message="form.errors.middle_name"
                    />
                </div>

                <div>
                    <InputLabel for="last_name" value="Last Name" />

                    <TextInput
                        id="last_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.last_name"
                        required
                        autocomplete="family-name"
                    />

                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>

                <div>
                    <InputLabel for="phone" value="Phone" />

                    <TextInput
                        id="phone"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.phone"
                        autocomplete="tel"
                    />

                    <InputError class="mt-2" :message="form.errors.phone" />
                </div>

                <div>
                    <InputLabel for="date_of_birth" value="Date of Birth" />

                    <TextInput
                        id="date_of_birth"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.date_of_birth"
                        autocomplete="bday"
                    />

                    <InputError
                        class="mt-2"
                        :message="form.errors.date_of_birth"
                    />
                </div>

                <div>
                    <InputLabel for="sex" value="Sex" />

                    <select
                        id="sex"
                        v-model="form.sex"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="">Select sex...</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>

                    <InputError class="mt-2" :message="form.errors.sex" />
                </div>
            </div>

            <div>
                <InputLabel for="address" value="Address" />

                <TextInput
                    id="address"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.address"
                    autocomplete="street-address"
                />

                <InputError class="mt-2" :message="form.errors.address" />
            </div>

            <div v-if="mustVerifyEmail && account.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600"
                    >
                        You have successfully updated your account!
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
