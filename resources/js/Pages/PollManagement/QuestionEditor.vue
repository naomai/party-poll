<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import debounce from 'lodash.debounce';
import { ref } from 'vue';
import { reactive } from 'vue';
import { watch } from 'vue';

    const props = defineProps({
        question: {
            type: Object,
        },
    });

    const questionDraft = reactive(props.question);

    const emit = defineEmits([
        'update:question',
    ]);

    const changed = debounce(() => {
        emit("update:question", questionDraft);
    }, 1000);

    watch(questionDraft, changed);
</script>
<template>
    <div class="question-editor-header">
        {{ questionDraft }}
        <TextInput 
            type="text"
            name="question-text"
            v-model="questionDraft.question" 
            required 
            placeholder="Pepperoni or margherita?"
            
        />
        <DangerButton>
            Delete
        </DangerButton>
    </div>

</template>