<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import debounce from 'lodash.debounce';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import EditSelect from './EditSelect.vue';
import IconTextInput from '@/Components/IconTextInput.vue';
import SelectGroup from '@/Components/SelectGroup.vue';
import DangerButton from '@/Components/DangerButton.vue';

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

const allowEdit = computed(() => 
    typeof props.question.id === 'undefined' ||
        props.pollState.poll_state.published_seq === null ||
        props.pollState.poll_state.published_seq < props.question.sequence_id
);

const questionDraft = reactive(props.question);

const responseTypes = ref([
    {caption:'Selection',  icon: "list", value: 'select'},
    {caption:'Text', icon: "keyboard", value: 'input'},
    {caption:'Range', icon: "gauge-high", value: 'range'},
    {caption:'Rating', icon: "star", value: 'rating'},
]);

const paramsForTypes = ref({
    select: 
        props.question.type=='select' ? props.question.response_params : {
            options: [{'caption':''},{'caption':''},{'caption':''}],
            max_selected: 1,
        
    },
    input: 
        props.question.type=='input' ? props.question.response_params : {
            type: 'text',
            max_length: '100',
        },
    range: 
        props.question.type=='range' ? props.question.response_params : {
            min: 0,
            max: 10,
        },
    rating: 
        props.question.type=='rating' ? props.question.response_params : {
            
        },
});

const responseType = ref(props.question.type);

const emit = defineEmits([
    'update:question',
]);

const changed = debounce(() => {
    emit("update:question", questionDraft);
}, 1000);


onMounted(()=>{
    if(!responseType.value) {
        responseType.value = "select";
    } else {
        paramsForTypes[props.question.type] = props.question.response_params;
    }
});


watch(questionDraft, changed);
watch(responseType, ()=>{
    let params = paramsForTypes.value[responseType.value];
    questionDraft.response_params = params;
    questionDraft.type = responseType.value;
});

const questionDelete = ()=>{
    emit("delete", questionDraft);
};


</script>

<template>
    <li>
        <div
            class="question" 
            :class="{'edit-lock': !allowEdit}"
        >
            <form @submit.prevent="submitQuestionProperties">
                <div v-if="allowEdit" class="editor">
                    <!-- EDITABLE -->
                    <IconTextInput 
                        v-if="allowEdit" 
                        v-model="questionDraft.question" 
                        name="question-text" 
                        icon="circle-question"
                        required 
                        placeholder="Pepperoni or margherita?"
                    />
                    <SelectGroup 
                        :options="responseTypes" 
                        v-model="responseType"
                    />
                    <EditSelect v-if="questionDraft.type=='select'" :question="questionDraft" @update:question="changed" />
                    <DangerButton @click="questionDelete">Delete question</DangerButton>
                </div>
                <div v-else class="editor">
                    <!-- PUBLISH-LOCKED -->
                    <div class="text-container">
                        <p>{{ question.question }}</p>
                    </div>
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" 
                        fill="currentColor" 
                    >
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                    </svg>
                    Published
                </div>
            </form>
        </div>
    </li>
</template>
