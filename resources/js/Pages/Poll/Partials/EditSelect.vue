<script setup>
import { computed, reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import IconTextInput from '@/Components/IconTextInput.vue';


const question = defineModel('question');

</script>

<template>
    <p>Options:</p>
    <div v-for="(option, index) in question.response_params.options">
        
        <IconTextInput v-model="option.caption" class="option" icon="pizza-slice" />
    </div>
</template>
