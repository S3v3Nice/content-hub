<script setup lang="ts">
import {useAuthStore} from "@/stores/auth"
import {computed, ref} from "vue"
import axios from "axios"
import useThemeManager from "../theme-manager";

const authStore = useAuthStore()
const themeManager = ref(useThemeManager())

function logout() {
  axios.post('/logout').then(() => {
    authStore.reset()
  })
}

const isLightTheme = computed(() => themeManager.value.isLight())

</script>

<template>
  <div class="surface-overlay pb-2 pt-2 border-b" style="height: 60px; max-width: 100%">
    <div class="container-md flex space-x-4" style="height: 100%">
      <RouterLink class="flex align-items-center" :to="{name: 'home'}" @click="themeManager.toggleTheme()">
        <img v-if="isLightTheme" src="/images/logo.svg" alt="Logo" style="height: 100%">
        <img v-else src="/images/logo-dark.svg" alt="Logo" style="height: 100%">
      </RouterLink>
    </div>
  </div>
</template>

<style scoped>

</style>
