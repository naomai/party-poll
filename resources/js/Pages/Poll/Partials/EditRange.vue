<script setup>
import { computed, reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import IconTextInput from '@/Components/IconTextInput.vue';
import TextInput from '@/Components/TextInput.vue';


const question = defineModel('question');

</script>

<template>
    From <TextInput v-model="question.response_params.min" type="number" required :max="question.response_params.max-1" size="4" />
    to <TextInput v-model="question.response_params.max" type="number" required :min="question.response_params.min+1" size="4" />
</template>
