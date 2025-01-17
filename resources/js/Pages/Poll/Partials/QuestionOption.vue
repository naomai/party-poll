<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import Radio from '@/Components/Radio.vue';


const props = defineProps({
    index: {
        type: String,
    },
    option: {
        type: Object,
    },
    form_id: {
        type: Number,
    },
    multiSelect: {
        type: Boolean,
    },
    votes: {
        type: Number,
    },
    maxVotes: {
        type: Number,
    },
    selected: {
        type: Boolean,
    },
    locked: {
        type: Boolean,
    },
});

const emit = defineEmits([
    'changed'
]);

const selectMe = () => {
    document.getElementById('rdb-'+props.form_id+'-'+props.index).click();
};


</script>

<template>
    <div class="option" :class="{'selected': selected}" @click.capture="(e)=>{selectMe();}">
        <div class="controls">
            <Radio v-if="!multiSelect"
                :value="index" 
                :id="'rdb-'+form_id+'-'+index"
                :group="form_id"
                :checked="selected"
                :disabled="locked"
                class="rounded-lg shadow-sm"
                @update:selected-id="($val)=>{emit('changed', $val, true)}" />
            <Checkbox v-if="multiSelect" 
                :value="index"
                :id="'rdb-'+form_id+'-'+index"
                :checked="selected"
                :disabled="locked"
                @update:checked="($val)=>{emit('changed', index, $val)}"
                />
            <label :for="'rdb-'+form_id+'-'+index">{{ option.caption }}</label>
            <div v-if="maxVotes" class="percentage"></div>
        </div>
        <div v-if="maxVotes!==0" class="chart-bar" :style="{width: (votes/maxVotes*100) + '%'}"></div>
        <div v-if="maxVotes!==0" class="chart-percentage">{{ Math.round(votes/maxVotes*100) }}%</div>
    </div>

</template>