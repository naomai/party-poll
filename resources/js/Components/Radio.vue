<script setup>
import { computed } from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    value: {
        type: String,
    },
    selectedId: {
        type: String,
    },
    id: {
        type: String,
    }, 
    group: {
        type: String,
    }
});

const proxyChecked = computed({
    get() {
        return props.selectedId == props.id;
    },

    set(val) {
        emit('update:selectedId', val);
    },
});
</script>

<template>
    <input
        type="radio"
        :value="value"
        :name="group"
        :id="id"
        v-model="proxyChecked"
        class="rounded shadow-sm"
    />
</template>
