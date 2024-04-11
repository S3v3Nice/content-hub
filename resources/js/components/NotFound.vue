<script setup lang="ts">
import Button from 'primevue/button'
import {RouterLink} from 'vue-router'
</script>

<template>
    <div class="error-container flex flex-col justify-center items-center ml-2 mr-2">
        <h1 class="text-4xl font-bold text-center mb-4">404: Не найдено</h1>
        <p class="text-muted text-center mb-8">Извините, запрашиваемая страница не была найдена.</p>
        <RouterLink :to="{ name: 'home' }">
            <Button label="Вернуться на главную" icon="fa-solid fa-house"/>
        </RouterLink>
    </div>
</template>

<style scoped>
.error-container {
    height: calc(75vh);
}
</style>
