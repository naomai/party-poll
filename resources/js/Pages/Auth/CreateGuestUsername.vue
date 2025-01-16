<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PollItem from '../PollIndex/Partials/PollItem.vue';

const form = useForm({
    name: '',
});

const submit = () => {
    form.put(route('guest_upgrade'));
};

const pageProps = usePage().props;

</script>

<template>
    <GuestLayout>
        <Head title="Your name" />
        <div v-if="pageProps.invitation!==null">
            <h2> You've been invited to a poll:</h2>
            <PollItem 
                :poll="pageProps.invitation.poll"
                class=" shadow-md sm:max-w-md sm:rounded-lg py-4 my-3 border  border-purple-400"
             />
        </div>
        <form @submit.prevent="submit" class="block w-full">
            <div>
                <InputLabel for="name" value="Would you like to introduce yourself first?" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />

            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Duh, already registered
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    OK
                </PrimaryButton>
            </div>
            <p v-if="form.name==''" class="text-sm text-purple-500">
                <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="inline-block icon icon-tabler icons-tabler-outline icon-tabler-horse"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10l-.85 8.507a1.357 1.357 0 0 0 1.35 1.493h.146a2 2 0 0 0 1.857 -1.257l.994 -2.486a2 2 0 0 1 1.857 -1.257h1.292a2 2 0 0 1 1.857 1.257l.994 2.486a2 2 0 0 0 1.857 1.257h.146a1.37 1.37 0 0 0 1.364 -1.494l-.864 -9.506h-8c0 -3 -3 -5 -6 -5l-3 6l2 2l3 -2z" /><path d="M22 14v-2a3 3 0 0 0 -3 -3" /></svg>
                We'll give you a fancy "lorem ipsum" name if you're not feeling inspired.
            </p>
        </form>
    </GuestLayout>
</template>
