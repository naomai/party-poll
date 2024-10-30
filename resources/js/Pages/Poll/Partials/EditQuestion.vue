<script setup>
import { computed, reactive, ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    question: {
        type: Object,
    },
    pollState: {
        type: Object,
    },
    clientState: {
        type: Object,
    }
});

const allowEdit = computed(() => 
    props.pollState.poll_state.published_seq < props.question.poll_sequence_id
);


</script>

<template>
    <li>
        <div
            class="question" 
            :class="{'edit-lock': !allowEdit}"
        >
            <div class="text-container">
                <p>
                    {{ question.question }}
                </p>
            </div>
            <div v-if="allowEdit" class="editor">
                ~editor~
            </div>
            <div v-else class="editor">
                <svg 
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" 
                    fill="currentColor" 
                >
                    <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                </svg>
                Published
            </div>
        </div>
    </li>
</template>
