<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import Radio from '@/Components/Radio.vue';
import { computed, getCurrentInstance, reactive, ref } from 'vue';

const model = defineModel()

const props = defineProps({
    responseParams: {
        type: Object,
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
    <div v-for="(option, index) in options">
        <Radio v-if="!multiSelect"
            :value="index" 
            :id="'rdb-'+id+'-'+index"
            :group="id"
            class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            @update:selected-id="($val)=>{selection=[$val]; model=wrapAnswer(selection);}" />
        <Checkbox v-if="multiSelect" 
            :value="index"
            :id="'rdb-'+id+'-'+index"
            :checked="option.checked"
            @update:checked="($val)=>{optionChanged(index, $val)}"
            />
        <label :for="'rdb-'+id+'-'+index" class='pl-3 text-sm text-gray-900'>{{ option.caption }}</label>
    </div>
</template>
