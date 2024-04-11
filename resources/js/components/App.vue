<script setup>
import Toast from 'primevue/toast'
import GlobalModals from '@/components/GlobalModals.vue'
import Header from '@/components/Header.vue'
</script>

<template>
    <Toast/>
    <GlobalModals/>
    <div class="surface-ground">
        <Header></Header>
        <div class='page-container mt-3'>
            <RouterView></RouterView>
        </div>
    </div>
</template>

<style scoped>

</style>
