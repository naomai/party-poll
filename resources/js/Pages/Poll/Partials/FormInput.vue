<script setup>
import { computed, ref } from 'vue';
import Vue3Slider from "vue3-slider";

const model = defineModel()

const props = defineProps({
    responseParams: {
        type: Object,
    },
});

const textValue = computed(()=>
    model.value!=null ? model.value.input : ""
);
const inputType = ref(props.responseParams.type);
const lengthMax = ref(props.responseParams.max_length);

const wrapAnswer = (x) => {
    const valid = 0 < x.length <= lengthMax.value;
    return {valid: valid, input: x};
};

</script>

<template>
    <input
        :type="inputType" :maxlength="lengthMax"
        :value="textValue" @input="$event => {model = wrapAnswer($event.target.value)}" 
        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        ref="input"
    />
</template>
