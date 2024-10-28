<script setup>
import { computed, ref } from 'vue';
import FormRange from './FormRange.vue';
import FormInput from './FormInput.vue';
import FormSelect from './FormSelect.vue';
import FormRating from './FormRating.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    question: {
        type: Object,
    },
    answer: {
        type: Object,
    },
});


const responseLocal = ref(props.question.answer!=null ? props.question.answer.response.answer : null);

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
    <li class="flex justify-between gap-x-6 py-5">
        <div class="min-w-0 gap-x-4" :class="{'opacity-50': !question.revealed}">
            <div class="pl-6 min-w-0 block">
                <p class="text-sm font-semibold leading-6 text-gray-900">{{ question.question }}</p>
            </div>
            <form @submit.prevent="submit"
                class="px-6 block" :class="{'hidden': props.answer!=null || !question.revealed}">
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
        </div>
    </li>
</template>
