<script setup>
import useThemeManager from '@/theme-manager'
import {onMounted} from "vue";
import Toast from 'primevue/toast'

onMounted(() => {
    const themeManager = useThemeManager()
    themeManager.loadTheme()
})
</script>

<template>
    <link id="theme-link" rel="stylesheet" href="/themes/light/theme.css">
    <Toast/>

    <div class="surface-ground">
        <RouterView></RouterView>
    </div>
</template>

<style scoped>

</style>
