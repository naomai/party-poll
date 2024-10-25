<script setup>
import ListAddButton from '@/Components/ListAddButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import PollPropertiesForm from '../PollManagement/PollPropertiesForm.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';


const props = defineProps({
    polls: {
        type: Array,
    },
});

const inPollInfoEditor = ref(false);

const pollToEdit = ref({
    title: "",
    id: null,
    wait_for_everybody: false,
});

const hasPolls = computed(()=>
    props.polls.data.length > 0
)
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
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg flex justify-center gap-6 flex-wrap"
                >
                    <div v-if="!hasPolls" class="text-gray-400 text-center px-6 py-6 w-full">You don't have any polls yet. Try creating one!</div>
                    <ul v-if="hasPolls" role="list" class="divide-y  divide-gray-100 text-lg">
                        
                        <li v-for="poll in polls.data" class="flex justify-between gap-x-6 py-5">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="min-w-0 flex-auto">
                                    <p class="text-sm font-semibold leading-6 text-gray-900">{{ poll.title }}</p>
                                    <p class="mt-1 truncate text-xs leading-5 text-gray-500">{{ poll.participant_count }} ppl</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div class="self-center py-6">
                        <ListAddButton class="" @click="inPollInfoEditor=true">Create poll</ListAddButton>
                    </div>
                </div>
            </div>
        </div>
        <Modal :show="inPollInfoEditor">
            <PollPropertiesForm :poll="pollToEdit"></PollPropertiesForm>
        </Modal>
    </AuthenticatedLayout>
</template>
