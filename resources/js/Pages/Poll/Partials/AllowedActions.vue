<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    participation: {
        type: Object,
    },
});
</script>

<template>
    <div 
        class="rounded-full bg-gray-700 inline-flex" 
        style="width: 30px; height: 30px "
        :class="{'opacity-50': !participation.can_answer}"
    >a</div>
    <div 
        class="rounded-full bg-gray-700 inline-flex" 
        style="width: 30px; height: 30px "
        :class="{'opacity-50': !participation.can_modify_poll}"
    >b</div>
    <div 
        class="rounded-full bg-gray-700 inline-flex" 
        style="width: 30px; height: 30px "
        :class="{'opacity-50': !participation.can_control_flow}"
    >c</div>
    <div 
        class="rounded-full bg-gray-700 inline-flex" 
        style="width: 30px; height: 30px "
        :class="{'opacity-50': !participation.can_see_progress}"
    >d</div>
</template>
