<script setup>
import { computed, ref } from 'vue';
import Vue3Slider from "vue3-slider";

const model = defineModel();

const props = defineProps({
    responseParams: {
        type: Object,
    },
    answer: {
        type: Object,
    },
});

const sliderValue = ref(-0.1);

const rangeMax = ref(props.responseParams.max);
const rangeMin = ref(props.responseParams.min);

const notSet = computed(() => sliderValue.value == -0.1);

const wrapAnswer = (x) => {
    const valid = rangeMin.value <= x <= rangeMax.value;
    return {valid: valid, input: x};
};

</script>

<template>
    <Vue3Slider 
        :modelValue="notSet ? 0 : sliderValue" @update:modelValue="$val => {sliderValue = $val; model = wrapAnswer($val)}" 
        :min="rangeMin" :max="rangeMax" 
        :circle-gap="80" :circle-offset="220" orientation="circular"
        :alwaysShowHandle="true"
        style="width: 150px; height: 150px;"
        :color="notSet ? '#877A' : '#D54'"
        handle-color="#D54"
        handle-scale="2"
        track-color="#D542"
        class="ml-6"/>
    <div style="position: relative;  top: -25px;  left: 55px;  width: 40px;  text-align: center;">
        {{ notSet ? "..." : sliderValue }}
    </div>
</template>
