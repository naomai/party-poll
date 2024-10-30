<script setup>
import { computed, reactive, ref } from 'vue';
import FormRange from './FormRange.vue';
import FormInput from './FormInput.vue';
import FormSelect from './FormSelect.vue';
import FormRating from './FormRating.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import StatsChart from './StatsChart.vue';

const props = defineProps({
    question: {
        type: Object,
    },
    pollState: {
        type: Object,
    },
    clientState: {
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
    });
};

</script>

<template>
    <li class="flex justify-between gap-x-6 py-5"  
        v-if="question.revealed"
    >
        <div class="min-w-0 gap-x-4">
            <div class="pl-6 min-w-0 block">
                <p class="text-sm font-semibold leading-6 text-gray-900" 
                    @click="collapsed = !collapsed"
                >
                    {{ question.question }}
                </p>
            </div>
            <TransitionGroup name="collapse">
                
                <div v-if="!collapsed">
                    <form v-if="question.answer==null && question.revealed"
                        @submit.prevent="submit"
                        class="px-6 block" >
                        <FormRange v-if="question.type=='range'" :response-params="question.response_params" v-model="responseLocal"/>
                        <FormInput v-if="question.type=='input'" :response-params="question.response_params" v-model="responseLocal"/>
                        <FormSelect v-if="question.type=='select'" :response-params="question.response_params" v-model="responseLocal"/>
                        <FormRating v-if="question.type=='rating'" :response-params="question.response_params" v-model="responseLocal"/>
                        <div class="px-6 block">
                            <PrimaryButton
                                class="ms-4"
                                :class="{ 'opacity-25': responseLocal == null || !responseLocal.valid || form.processing }"
                                :disabled="responseLocal == null || !responseLocal.valid || form.processing"
                            >
                                OK
                            </PrimaryButton>
                        </div>
                    </form>
                    <div v-else
                        class="response_stats"
                    >
                        <StatsChart v-if="question.stats.type=='options'" :stats="question.stats" />
                        <StatsText v-if="question.stats.type=='list'" :stats="question.stats" />

                    </div>
                </div>
                <div v-else-if="responseLocal !== null" class="text-sm text-gray-400 px-6">
                    <span v-if="question.type=='range'">{{ responseLocal.input }}</span>
                    <span v-if="question.type=='text'">{{ responseLocal.input }}</span>
                    <span v-if="question.type=='rating'">{{ responseLocal.input }} / 5</span>
                    <span v-if="question.type=='select'">{{ responseLocal.selected.reduce(
                        (acc, option) => (acc!="" ? acc+", " : "") + question.response_params.options[option].caption,
                        ""
                    ) }}</span>
                </div>
            </TransitionGroup>
        </div>
    </li>
</template>
