<script setup>

import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';

const emit = defineEmits([
    'confirm',
    'abort',
]);
const visible = ref(false);

const show = () => {
    visible.value = true;
}

const sayYes = ()=>{
    emit('confirm');
    visible.value = false;
};

const sayNo = ()=>{
    emit('abort');
    visible.value = false;
};

defineExpose({
    show, visible,
})

</script>
<template>
    <div class="inline-warning" v-show="visible">
        <p class="caption">
            <slot/>
        </p>
        <PrimaryButton @click="sayYes"><i class="fa-solid fa-thumbs-up"></i>Yes</PrimaryButton>
        <SecondaryButton @click="sayNo"><i class="fa-solid fa-thumbs-down"></i>No</SecondaryButton>
    </div>
</template>