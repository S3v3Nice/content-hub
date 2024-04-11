<script setup lang='ts'>
import Message from 'primevue/message'
</script>

<template>
    <div class="flex justify-center">
        <Message :closable="false" severity="error">
            Недостаточно прав для просмотра страницы.
        </Message>
    </div>
</template>

<style scoped>

</style>
