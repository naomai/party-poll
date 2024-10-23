<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';


defineProps({
    polls: {
        type: Array,
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
                >
                <ul role="list" class="divide-y divide-gray-100">
                    <li v-for="poll in polls.data" class="flex justify-between gap-x-6 py-5">
                        <div class="flex min-w-0 gap-x-4">
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm font-semibold leading-6 text-gray-900">{{ poll.title }}</p>
                                <p class="mt-1 truncate text-xs leading-5 text-gray-500">{{ poll.participant_count }} ppl</p>
                            </div>
                        </div>
                    </li>
                </ul>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
