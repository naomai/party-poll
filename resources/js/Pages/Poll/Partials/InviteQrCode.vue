<script setup>
import VueQrcode from '@chenfengyuan/vue-qrcode';

const props = defineProps({
    poll: [Object],
})
</script>
<template>
    <div class="p-6">
        <header>
            <h2 class="text-lg">
                Invite
            </h2>
        </header>
        <vue-qrcode :value="props.poll.links.invite" :options="{ width: 200 }"></vue-qrcode>
        <input type="text" :value="props.poll.links.invite"/>
    </div>
</template>