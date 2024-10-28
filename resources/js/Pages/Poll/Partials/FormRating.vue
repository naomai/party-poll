<script setup>
import { ref } from 'vue';
import StarRating from 'vue-star-rating'

const model = defineModel();

const props = defineProps({
    responseParams: {
        type: Object,
    },
    answer: {
        type: Object,
    },
});

const rating = ref(-0.1);

const wrapAnswer = (x) => {
    const valid = 0.5 <= x <= 5;
    return {valid: valid, input: x};
};

</script>

<template>
    <star-rating 
        :rating="rating"
        @update:rating="$val => {rating = $val; model = wrapAnswer($val)}" 
        increment="0.5"
        ></star-rating>

</template>
