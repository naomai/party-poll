<script setup>
    const props = defineProps({
        poll: {type: Object},
    });

</script>

<template>
    <div class="flex min-w-0 gap-x-4">
        <div class="pl-6 min-w-0 flex-auto">
            <p class="text-sm font-semibold leading-6 text-gray-900">{{ poll.title }}</p>
            <p class="mt-1 truncate text-xs leading-5 text-gray-500">{{ poll.member_count }} ppl</p>
        </div>
    </div>
</template>