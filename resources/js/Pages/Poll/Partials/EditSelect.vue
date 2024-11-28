<script setup>
import { computed, reactive, ref, useTemplateRef, watch } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import IconTextInput from '@/Components/IconTextInput.vue';



const props = defineProps(['question'])
const emit = defineEmits(['update:question'])

const optionsLocal = reactive(props.question.response_params.options);

const deleteOption = (idx)=>{
    optionsLocal.splice(idx, 1);
};

const addOption = () => {
    optionsLocal.push({caption: "", newOption: true});
}

watch(optionsLocal, (o)=>{
    let optionsFiltered = o.filter(
        (e)=>
            e.caption!=null && e.caption!=""
    );
    props.question.response_params.options = optionsFiltered;
    emit("update:question", props.question);
});

</script>

<template>
    <div v-for="(option, index) in optionsLocal">
        
        <IconTextInput v-model="option.caption" class="option full-width" icon="pizza-slice" :autofocus="option.newOption" />
        <button :disabled="optionsLocal.length <= 1" class="option-delete" @click="deleteOption(index)"><i class="fa icon fa-trash"></i></button>
    </div>

    <div>
        <IconTextInput 
            class="option full-width" icon="pizza-slice" 
            placeholder="Add..."
            @click="addOption()" 
        />
    </div>
</template>
