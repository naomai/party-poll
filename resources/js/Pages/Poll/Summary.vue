<script setup>
import ListAddButton from '@/Components/ListAddButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import PollPropertiesForm from '../PollManagement/PollPropertiesForm.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';
import EditQuestion from './Partials/EditQuestion.vue';
import Question from './Partials/Question.vue';
import AllowedActions from './Partials/AllowedActions.vue';


const page = usePage();
const info = page.props.info;
const questions = computed(()=>page.props.questions);
const participation = page.props.participation;

const hasQuestions = computed(() =>
    questions.value.length > 0
);

const hasMoreQuestions = ref(page.props.state.more_questions);

const isAdmin = computed(() => participation.modify_poll);
const canSeeAllQuestions = computed(() => 
    participation.modify_poll || participation.see_progress
);

const clientState = reactive({
    editing: false,
})

</script>

<template>
    <Head :title="info.title" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
            {{ info.title }}
            </h2>
        </template>
        <div class="py-12">
            <div id="poll-summary" class="mx-auto max-w-7xl sm:px-6 lg:px-8">

                <AllowedActions :participation="participation" v-model:client-state="clientState" />
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg flex justify-center gap-6 flex-wrap"
                    :class="{editing: clientState.editing}"
                >
                    <div v-if="!hasQuestions" class="text-gray-400 text-center px-6 py-6 w-full">
                        {{ isAdmin ? "There are no questions here. Go ahead and add some!" : "We don't have any questions yet. Come back soon!" }}
                    </div>
                    <ul 
                        v-if="hasQuestions" 
                        role="list" class="w-full divide-y  divide-gray-100 text-lg"
                    >
                        <Question v-if="!clientState.editing"
                            v-for="question in questions" 
                            :question="question" :poll-state="page.props.state"
                            :client-state="clientState"
                        />
                        <EditQuestion v-else
                            v-for="question in questions" 
                            :question="question" :poll-state="page.props.state"
                            :client-state="clientState"
                        />
                        <div v-if="page.props.state.waiting_others" class="text-gray-400 text-center px-6 py-6 w-full">
                            Waiting for others... ({{ page.props.state.others_responses_left }})
                        </div>
                    </ul>
                    <div v-if="!hasMoreQuestions" class="text-gray-400 text-center px-6 py-6 w-full">
                        No more questions for you. Come back soon!
                    </div>
                    <div v-if="isAdmin && clientState.editing" class="self-center py-6">
                        <ListAddButton class="" @click="inQuestion=true">New question</ListAddButton>
                    </div>
                </div>
            </div>
        </div>
    <pre>
{{ page.props }}
    </pre>
    </AuthenticatedLayout>
</template>
