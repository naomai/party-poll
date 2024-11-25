<script setup>
import { getCurrentInstance } from 'vue';

const props = defineProps({
    options: {
        type: Array,
    },
})

const model = defineModel();

const groupId = 'selgrp'+getCurrentInstance().uid;
</script>
<template>
    <div class="select-group">
        <div v-for="(option, idx) in props.options"
            class="option"
        >
            <i v-if="option.icon" class="fa icon" :class="'fa-'+option.icon"></i>
            <input 
                type="radio" 
                :name="groupId" 
                :id="groupId + '-opt' + idx" 
                v-model="model"
                :value="option.value"
             />
            <label :for="groupId + '-opt' + idx">{{option.caption}}</label>
        </div>
    </div>
</template>