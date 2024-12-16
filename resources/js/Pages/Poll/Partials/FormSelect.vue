<script setup>
import { computed, getCurrentInstance, reactive, ref } from 'vue';
import QuestionOption from './QuestionOption.vue';

const model = defineModel()

const props = defineProps({
    responseParams: {
        type: Object,
    },
    stats: {
        type: Object,
    },
    locked: {
        type: Boolean,
    },
});

const options = ref(props.responseParams.options);
const selectionQueue = ref([]);

const selectedMax = ref(props.responseParams.max_selected);

const multiSelect = computed(() => props.responseParams.max_selected != 1);

const id = getCurrentInstance().uid;

const wrapAnswer = (x) => {
    const valid = x.length > 0 && (selectedMax.value==0 || (selectedMax.value>0 && x.length <= selectedMax.value));
    return {valid: valid, selected: x};
};

const optionChanged = (id, value) => {
    if(!multiSelect) {
        model.value = wrapAnswer([id]);
        return;
    }

    const sel = selectionQueue.value;
    if(value) {
        sel.push(id);
        
    } else {
        sel.splice(sel.indexOf(id), 1);
    }

    if(sel.length > selectedMax.value) {
        const truncateCount = sel.length - selectedMax.value;
        sel.splice(0, truncateCount);
    }
    model.value = wrapAnswer(sel);
    selectionQueue.value = sel;

    options.value = options.value.map((element, idx) => {
        element.checked = sel.indexOf(idx) != -1;
        return element;
    });
}



</script>

<template>

    <QuestionOption v-for="(option, index) in options" 
        :option="option" :index="index" :form_id="id"
        :multi-select="multiSelect"
        :votes="stats.options[index][1]"
        :max-votes="stats.votes"
        :selected="model!==null && model.selected.indexOf(index)!==-1"
        :locked="locked"
        @changed="($idx,$value)=>optionChanged($idx,$value)"
    />
    <div v-if="multiSelect && selectedMax != 0"
        class="text-sm text-gray-500"
    >
        Max: {{ selectedMax }}
    </div>
</template>
