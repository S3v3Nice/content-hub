<script setup>
import Toast from 'primevue/toast'
import GlobalModals from '@/components/GlobalModals.vue'
</script>

<template>
    <Toast/>
    <GlobalModals/>
    <div class="surface-ground">
        <RouterView></RouterView>
    </div>
</template>

<style scoped>

</style>
