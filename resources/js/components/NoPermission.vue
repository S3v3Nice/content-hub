<script setup lang='ts'>
import BaseLayout from '@/components/layout/BaseLayout.vue'
import Message from 'primevue/message'
</script>

<template>
    <BaseLayout>
        <div class="flex justify-center">
            <Message :closable="false" severity="error">
                Недостаточно прав для просмотра страницы.
            </Message>
        </div>
    </BaseLayout>
</template>

<style scoped>

</style>
