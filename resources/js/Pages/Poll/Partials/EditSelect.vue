<script setup>
import { computed, reactive, ref, useTemplateRef } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import IconTextInput from '@/Components/IconTextInput.vue';


const question = defineModel('question');

const deleteOption = (idx)=>{
    question.value.response_params.options.splice(idx, 1);
};

const addOption = () => {
    question.value.response_params.options.push({caption: "", newOption: true});
}

</script>

<template>
    <p>Options:</p>
    <div v-for="(option, index) in question.response_params.options">
        
        <IconTextInput v-model="option.caption" class="option full-width" icon="pizza-slice" :autofocus="option.newOption" />
        <button class="option-delete" @click="deleteOption(index)"><i class="fa icon fa-trash"></i></button>
    </div>

    <div>
        <IconTextInput class="option full-width" icon="pizza-slice" placeholder="Add..." @click="addOption()" />
    </div>
</template>
