<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import Checkbox from '@/Components/Checkbox.vue';
import { computed } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    poll: [Object],
});

defineEmits([
    'create',
    'save',
])

const isCreatingPoll = computed(()=>{
    return props.poll.id===null;
});

//const poll = usePage().props.poll;

const form = useForm(props.poll);

const submitPollProperties = () => {
    if(isCreatingPoll) {
        form.post(route('polls.store'), {
            onSuccess: () => emit('create')
        });
    } else {
        form.patch(route('polls.update'), {
            onFinish: () => form.reset('title'),
        });
    }
}

</script>

<template>
    <div class="p-6">
        <header>
            <h2 v-if="isCreatingPoll" class="text-lg">
                Create poll
            </h2>
            <h2 v-if="!isCreatingPoll" class="text-lg">
                Edit poll
            </h2>
        </header>

        <form @submit.prevent="submitPollProperties">
            <div  class="my-6">
                <InputLabel for="title" value="Title" />

                <TextInput
                    id="title"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.title"
                    required
                    autofocus
                />

                <InputError class="mt-2" :message="form.errors.title" />
            </div>

            <div class="my-6">
                <label>
                    <Checkbox name="wait_for_everybody" v-model:checked="form.wait_for_everybody" />
                    <span class="ms-2 text-sm text-gray-600">
                        Wait for everybody
                    </span>
                    <div class="text-gray-400 text-sm">
                        Go to next question only after everybody answered
                    </div>
                </label>
                
            </div>

            <PrimaryButton
                class="ms-4"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Save
            </PrimaryButton>
        </form>
    </div>
</template>