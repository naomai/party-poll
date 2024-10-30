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
    <li class="flex justify-between gap-x-6 py-5">
        <div class="min-w-0 gap-x-4"
            :class="{'opacity-50': !allowEdit}"
        >
            <div class="pl-6 min-w-0 block">
                <p class="text-sm font-semibold leading-6 text-gray-900">
                    {{ question.question }}
                </p>
            </div>
            <div v-if="allowEdit">
                ~editor~
            </div>
            <div v-else>
                <svg 
                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" 
                    fill="currentColor" 
                    class="size-6 inline-block"
                >
                    <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z" clip-rule="evenodd" />
                </svg>
                Published
            </div>
        </div>
    </li>
</template>
