<script setup>
import { computed, reactive, ref, watch } from 'vue';
import FormRange from './FormRange.vue';
import FormInput from './FormInput.vue';
import FormSelect from './FormSelect.vue';
import FormRating from './FormRating.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import StatsChart from './StatsChart.vue';
import VueCollapse from "vue3-collapse";

const props = defineProps({
    question: {
        type: Object,
    },
    pollState: {
        type: Object,
    },
    clientState: {
        type: Object,
    },
    rules: {
        type: Object,
    }
});

const responseLocal = ref(props.question.answer!=null ? props.question.answer : null);

const collapsed = ref( 
    (props.question.answer !== null 
    || !props.question.revealed
    ) && props.pollState.blocking_id != props.question.id 
);

const form = useForm({
    answer: {},
});

const submit = () => {
    form.answer = responseLocal.value;
    form.put(
        route('question.answer.store', props.question.id), {
        onFinish: () => {},
        only: ['info', 'state', 'questions'], 
        preserveState: true,
        preserveScroll: true,
    });
};

const instantSubmit = ref(!props.rules.wait_for_everybody);

watch(responseLocal, () => {
    if(instantSubmit.value) {
        submit();
    }
});

</script>

<template>
    <li 
        v-if="question.revealed"
        :class="{collapsed: collapsed, answered: props.question.answer !== null }"
        @click="collapsed = !collapsed"
    >
        <div class="question">
            <div class="text-container">
                <p>
                    <div class="drag-handle">{{ question.poll_sequence_id }}. </div>
                    {{ question.question }}
                </p>
                <svg xmlns="http://www.w3.org/2000/svg" 
                    viewBox="0 0 24 24" fill="currentColor" 
                    class="collapse-chevron"
                >
                    <path fill-rule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clip-rule="evenodd" />
                </svg>

            </div>
            <VueCollapse duration="250" :model-value="collapsed" easing="var(--timing-bouncy)">
                <div v-if="responseLocal !== null" 
                    class="response-preview"
                >
                    <span v-if="question.type=='range'">{{ responseLocal.input }}</span>
                    <span v-if="question.type=='text'">{{ responseLocal.input }}</span>
                    <span v-if="question.type=='rating'">{{ responseLocal.input }} / 5</span>
                    <span v-if="question.type=='select'">{{ responseLocal.selected.reduce(
                        (acc, option) => (acc!="" ? acc+", " : "") + question.response_params.options[option].caption,
                        ""
                    ) }}</span>
                </div>
            </VueCollapse>
            <VueCollapse duration="350" :model-value="!collapsed" easing="var(--timing-bouncy)">
                <div
                    class="response-editor"
                    @click.stop=""
                >
                    <form v-if="question.revealed"
                        @submit.prevent="submit"
                    >
                        <FormRange v-if="question.type=='range'" :response-params="question.response_params" v-model="responseLocal"/>
                        <FormInput v-if="question.type=='input'" :response-params="question.response_params" v-model="responseLocal"/>
                        <FormSelect v-if="question.type=='select'" 
                            :response-params="question.response_params" 
                            v-model="responseLocal"
                            :stats="question.stats"
                            :locked="!rules.enable_revise_response && props.question.answer !== null && !instantSubmit"
                        />
                        <FormRating v-if="question.type=='rating'" :response-params="question.response_params" v-model="responseLocal"/>
                        <div v-if="!instantSubmit && props.question.answer === null">
                            <PrimaryButton
                                class="confirm"
                                :class="{ 'opacity-25': responseLocal == null || !responseLocal.valid || form.processing }"
                                :disabled="responseLocal == null || !responseLocal.valid || form.processing"
                            >
                                <i class="fa-solid fa-paper-plane"></i>
                                OK
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </VueCollapse>
        </div>
    </li>
</template>
