<script setup>
import useThemeManager from '@/theme-manager'
import {onMounted} from "vue";

onMounted(() => {
  const themeManager = useThemeManager()
  themeManager.loadTheme()
})
</script>

<template>
  <link id="theme-link" rel="stylesheet" href="/themes/light/theme.css">
  <div class="surface-ground">
    <RouterView></RouterView>
  </div>
</template>

<style scoped>

</style>
