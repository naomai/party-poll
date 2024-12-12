<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PollItem from '../PollIndex/Partials/PollItem.vue';

const pageProps = usePage().props;

</script>

<template>
    <GuestLayout>
        <Head title="Invalid invitation" />
        <h2>Wait a sec, buddy!</h2>
        <div v-if="pageProps.status==1">
            <p> It seems that your invitation link is broken. Can you ask your friend to copy it again? </p>
        </div>
        <div v-else-if="pageProps.status==2">
            <p> It seems that your invitation link has expired, and you're late to the party. Sorry! </p>
            <PollItem 
                :poll="pageProps.poll"
                class=" shadow-md sm:max-w-md sm:rounded-lg py-4 my-3 border  border-purple-400"
             />
        </div>
    </GuestLayout>
</template>
