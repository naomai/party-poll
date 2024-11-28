<script setup>
import ListAddButton from '@/Components/ListAddButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import PollPropertiesForm from '../PollManagement/PollPropertiesForm.vue';
import Modal from '@/Components/Modal.vue';
import { ref } from 'vue';
import EditQuestion from './Partials/EditQuestion.vue';
import Question from './Partials/Question.vue';
import AllowedActions from './Partials/AllowedActions.vue';
import InviteQrCode from './Partials/InviteQrCode.vue';
import axios from 'axios';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InlineWarning from '@/Components/InlineWarning.vue';


const page = usePage();
const info = page.props.info;
const questions = reactive(page.props.questions);
const questionsAll = reactive(page.props.questions_privileged);
const membership = page.props.membership;

const hasQuestions = computed(() =>
    questions.length > 0
);

const hasMoreQuestions = ref(page.props.state.more_questions);

const isAdmin = computed(() => membership.modify_poll);
const canSeeAllQuestions = computed(() => 
    membership.modify_poll || membership.see_progress
);

const hasUnpublished = computed(()=>
    questions.reduce((a, current)=>
        a ||
        page.props.state.poll_state.published_seq === null ||
        page.props.state.poll_state.published_seq < current.poll_sequence_id
    , false)
);

const clientState = reactive({
    editing: false,
    viewingQr: false,

})

const createQuestion = () => {
    let newQuestion = {uncommitted: Math.random()};
    questionsAll.push(newQuestion);
}

const updateQuestion = (question) => {
    let q = {
        id: question.id,
        uncommitted: question.uncommitted,
        poll_sequence_id: question.poll_sequence_id,
        question: question.question,
        type: question.type,
        response_params: question.response_params,
    };


    if(typeof q.uncommitted != 'undefined' ) {
        axios.post(
            route("api.questions.store", {
                poll: info.id,
            }), 
            q
        ).then((response)=>{
            question.id = response.data.id;
            question.poll_sequence_id = response.data.poll_sequence_id;
            question.justStored = true;
            delete question.uncommitted;
        }).catch(()=>{

        });
    } else {
        axios.patch(
            route("api.questions.update", {
                poll: info.id,
                question: q.id,
            }), 
            q
        ).then(()=>{});
    }

}

const deleteQuestion = (q) => {

    const deleteFromLocalList = (q) => {
        let questionInArray = questions.find((question)=>question.id == q.id && question.uncommitted == q.uncommitted);
        questions.splice(questions.indexOf(questionInArray), 1);
    }

    if(typeof q.uncommitted != 'undefined' ) {
        deleteFromLocalList(q);
    } else {
        axios.delete(
            route("api.questions.destroy", {
                poll: info.id,
                question: q.id,
            }), 
            q
        ).then(()=>{
            deleteFromLocalList(q);
        });
    }
};

const publishQuestions = () => {
    router.post(route("polls.publish", {poll: info.id}), {}, {
        only: ['info', 'state'], 
        preserveState: true,
        preserveScroll: true,
    });
};

const publishWarning = ref(null); //useTemplateRef('publishWarning');

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
            <div id="poll-summary" 
                class="app-islands"
                :class="{editing: clientState.editing}"
            >
                {{ hasUnpublished }}
                <AllowedActions :membership="membership" v-model:client-state="clientState" />
                <div
                    class="poll-questions app-island"
                    :class="{editing: clientState.editing}"
                >
                    <div v-if="!hasQuestions" class="text-gray-400 text-center px-6 py-6 w-full">
                        {{ isAdmin ? "There are no questions here. Go ahead and add some!" : "We don't have any questions yet. Come back soon!" }}
                    </div>
                    <ul 
                        v-if="hasQuestions" 
                        role="list" class="question-list"
                    >
                        <Question v-if="!clientState.editing"
                            v-for="question in questions" 
                            :question="question" :poll-state="page.props.state"
                            :client-state="clientState"
                        />
                        <EditQuestion v-else
                            v-for="question in questionsAll" 
                            :question="question" :poll-state="page.props.state"
                            :client-state="clientState"
                            @update:question="updateQuestion"
                            @delete="deleteQuestion"
                            :key="question.id || question.uncommitted"
                        />
                    </ul>
                        <div v-if="hasMoreQuestions && !page.props.state.waiting_me && page.props.state.waiting_others && !clientState.editing" class="text-gray-400 text-center px-6 py-6 w-full">
                            Waiting for others... ({{ page.props.state.others_responses_left }})
                        </div>
                    <div v-if="hasQuestions && !hasMoreQuestions && !page.props.state.waiting_me && !clientState.editing" class="text-gray-400 text-center px-6 py-6 w-full">
                        No more questions for you. Come back soon!
                    </div>
                    <div v-if="isAdmin && clientState.editing" class="edit-buttons self-center py-6">
                        <ListAddButton class="" @click="createQuestion()">New question</ListAddButton>
                        <SecondaryButton v-if="hasUnpublished" @click="publishWarning.show()"><i class="fa-solid fa-person-chalkboard"></i> Reveal</SecondaryButton>
                    </div>
                    <InlineWarning class="publish-warning" ref='publishWarning' @confirm="publishQuestions">Reveal questions? You won't be able to edit them.</InlineWarning>
                </div>
            </div>
        </div>
    <pre>
        {{ page.props }}
    </pre>
    <Modal :show="clientState.viewingQr" @close="clientState.viewingQr = false">
        <InviteQrCode :poll="info"></InviteQrCode>
    </Modal>
    </AuthenticatedLayout>
</template>
