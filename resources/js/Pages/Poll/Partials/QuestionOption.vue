<script setup>

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
    }

});

const emit = defineEmits([
    'changed'
]);


</script>

<template>
    <div class="option">
        <Radio v-if="!multiSelect"
            :value="index" 
            :id="'rdb-'+form_id+'-'+index"
            :group="id"
            class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            @update:selected-id="($val)=>{selection=[$val]; model=wrapAnswer(selection);}" />
        <Checkbox v-if="multiSelect" 
            :value="index"
            :id="'rdb-'+form_id+'-'+index"
            :checked="option.checked"
            @update:checked="($val)=>{optionChanged(index, $val)}"
            />
        <label :for="'rdb-'+form_id+'-'+index" class='pl-3 text-sm text-gray-900'>{{ option.caption }}</label>
    </div>

</template>