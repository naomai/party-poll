<script setup>
import ListAddButton from '@/Components/ListAddButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onUpdated, reactive } from 'vue';
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

const hasQuestionsToAnswer = computed(() =>
    questions.length > 0 && page.props.state.question_id !== null
);
const hasQuestionsToEdit = computed(() =>
    questionsAll.length > 0
);

onUpdated(()=>{
    questions.value = page.props.questions;
});


const pollStarted = ref(page.props.state.poll_state.started);
const hasMoreQuestions = ref(page.props.state.more_questions);

const isAdmin = computed(() => membership.modify_poll);
const canSeeAllQuestions = computed(() => 
    membership.modify_poll || membership.see_progress
);

const unpublishedCount = computed(()=>
    questionsAll.reduce((a, current)=>
        a + (
            page.props.state.poll_state.published_seq === null ||
            page.props.state.poll_state.published_seq < current.poll_sequence_id 
            ? 1
            : 0
        )
    , 0)
);

const hasUnpublished = computed(()=>unpublishedCount.value > 0);

const clientState = reactive({
    editing: false,
    viewingQr: false,

})

const createQuestion = () => {
    let newQuestion = {uncommitted: true, localUniqueId: Math.random()};
    questionsAll.push(newQuestion);
}

const updateQuestion = (request) => {
    let question = request.data;
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
            request.busyFlag.value = false;
        }).catch(()=>{
            request.busyFlag.value = false;
        });
    } else {
        axios.patch(
            route("api.questions.update", {
                poll: info.id,
                question: q.id,
            }), 
            q
        ).then(()=>{
            request.busyFlag.value = false;
        }).catch(()=>{
            request.busyFlag.value = false;
        });
    }

}

const deleteQuestion = (q) => {

    const deleteFromLocalList = (q) => {
        let questionInArray = questionsAll.find(
            (question)=>
                (typeof q.id!="undefined" && question.id == q.id) 
                || (typeof q.localUniqueId!="undefined" && question.localUniqueId == q.localUniqueId)
        );
        questionsAll.splice(questionsAll.indexOf(questionInArray), 1);
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
        only: ['info', 'state', 'questions', 'questionsAll'], 
        preserveState: true,
        preserveScroll: true,
    });
};

const publishWarning = ref(null); //useTemplateRef('publishWarning');

//onMounted(()=>{
window.Echo.private('Poll.'+info.id)
    .listen('.question.stats', (e) => {
        if(typeof e.question_id == 'number' && typeof e.stats == 'object') {
            let questionId = e.question_id;

            let question = questions.find((q) => q.id==questionId);
            question.stats = e.stats;
            
        }
    })
    .listen('.poll.publish', (e) => {
        questions.push(...e.questions);
        info.question_count = e.question_count;
    })
//});

</script>

<template>
    <Head :title="info.title" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight"
            >
            {{ info.title }}
            </h2>
        </template>
        <div class="py-4">
            <div id="poll-summary" 
                class="app-islands"
                :class="{editing: clientState.editing}"
            >
                <AllowedActions :membership="membership" v-model:client-state="clientState" />
                <div
                    class="poll-questions app-island"
                    :class="{editing: clientState.editing}"
                >
                <ul
                role="list" class="question-list"
                >
                    <Question v-if="!clientState.editing"
                        v-for="question in questions" 
                        :question="question" :poll-state="page.props.state"
                        :client-state="clientState"
                        :rules="page.props.info.rules"
                    />
                    <EditQuestion v-else
                        v-for="question in questionsAll" 
                        :question="question" :poll-state="page.props.state"
                        :client-state="clientState"
                        @update:question="updateQuestion"
                        @delete="deleteQuestion"
                        :key="question.localUniqueId || question.id"
                    />
                </ul>

                <div v-if="hasMoreQuestions && !page.props.state.waiting_me && page.props.state.waiting_others" class="state-message">
                    Waiting for others... ({{ page.props.state.others_responses_left }})
                </div>
                <div v-else-if="!pollStarted && !hasQuestionsToEdit && isAdmin" class="state-message">
                    Hey boss, there are no questions here. Go ahead and add some!
                </div>
                <div v-else-if="!pollStarted && !hasQuestionsToAnswer && !clientState.editing" class="state-message">
                    We don't have any questions yet. Come back soon!
                </div>
                <div v-else-if="!hasQuestionsToAnswer && !hasMoreQuestions && !page.props.state.waiting_me" class="state-message">
                    No more questions for you. Come back soon!
                </div>
                <div v-if="isAdmin && clientState.editing" class="edit-buttons self-center py-6">
                    <ListAddButton class="" @click="createQuestion()">New question</ListAddButton>
                    <SecondaryButton v-if="hasUnpublished" @click="publishWarning.show()"><i class="fa-solid fa-person-chalkboard"></i> Reveal ({{ unpublishedCount }})</SecondaryButton>
                </div>
                <InlineWarning class="publish-warning" ref='publishWarning' @confirm="publishQuestions">Reveal questions? You won't be able to edit them.</InlineWarning>
            </div>
        </div>
    </div>
    <pre v-if="false ">
        {{ page.props }}
    </pre>
    <Modal :show="clientState.viewingQr" @close="clientState.viewingQr = false">
        <InviteQrCode :poll="info"></InviteQrCode>
    </Modal>
    </AuthenticatedLayout>
</template>
