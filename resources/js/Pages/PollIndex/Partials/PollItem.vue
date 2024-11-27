<script setup>
    const props = defineProps({
        poll: {type: Object},
    });

</script>

<template>
    <div class="poll-summary">
        <div class="poll-summary-container">
            <p class="title">{{ poll.title }}</p>
            <p class="details">{{ poll.member_count }} ppl</p>
        </div>
    </div>
</template>