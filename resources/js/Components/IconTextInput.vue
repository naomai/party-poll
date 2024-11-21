<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const props = defineProps({
    icon: {
        type: String,
    },
    name: {
        type: String,
    }
})

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="input-iconized">
        <i class="fa icon" :class="'fa-'+icon"></i>
        <input
            type="text"
            v-model="model"
            ref="input"
            class=""
        />
    </div>
</template>
