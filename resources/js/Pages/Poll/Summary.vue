<script setup>
import ListAddButton from '@/Components/ListAddButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import PollPropertiesForm from '../PollManagement/PollPropertiesForm.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';


const page = usePage();
const info = page.props.info;
const questions = page.props.questions;
const participation = page.props.participation;

const hasQuestions = computed(() =>
    questions.length > 0
);

const isAdmin = computed(() => participation.can_modify_poll);
const canSeeAllQuestions = computed(() => 
    participation.can_modify_poll || participation.can_see_progress
);

</script>

<template>
    <Head title="{{ info.title }}" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
            {{ info.title }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg flex justify-center gap-6 flex-wrap"
                >
                    <div v-if="!hasQuestions" class="text-gray-400 text-center px-6 py-6 w-full">
                        {{ isAdmin ? "There are no questions here. Go ahead and add some!" : "We don't have any questions yet. Come back soon!" }}
                    </div>
                    <ul v-if="hasQuestions" role="list" class="w-full divide-y  divide-gray-100 text-lg">
                        
                        <li v-for="question in questions" class="flex justify-between gap-x-6 py-5">
                            <div class="flex min-w-0 gap-x-4">
                                <div class="pl-6 min-w-0 flex-auto" :class="{'opacity-75': question.revealed}">
                                    <p class="text-sm font-semibold leading-6 text-gray-900">{{ question.question }}</p>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <div v-if="isAdmin" class="self-center py-6">
                        <ListAddButton class="" @click="inQuestion=true">New question</ListAddButton>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
