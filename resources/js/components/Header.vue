<script setup lang="ts">
import {useAuthStore} from "@/stores/auth"
import {computed, ref} from "vue"
import axios from "axios"
import useThemeManager from "@/theme-manager";
import Button from "primevue/button"
import Dialog from "primevue/dialog"
import LoginForm from "@/components/auth/LoginForm.vue";
import RegisterForm from "@/components/auth/RegisterForm.vue";

const authStore = useAuthStore()
const themeManager = ref(useThemeManager())
const isLightTheme = computed(() => themeManager.value.isLight())
const isLoginDialogVisible = ref(false)
const isRegisterDialogVisible = ref(false)

function logout() {
  axios.post('/logout').then(() => {
    authStore.reset()
  })
}

function setLoginDialogVisible(isVisible: boolean) {
  if (isVisible) {
    isRegisterDialogVisible.value = false
  }
  isLoginDialogVisible.value = isVisible
}

function setRegisterDialogVisible(isVisible: boolean) {
  if (isVisible) {
    isLoginDialogVisible.value = false
  }
  isRegisterDialogVisible.value = isVisible
}

</script>

<template>
  <div class="surface-overlay pb-2 pt-2 border-b" style="height: 60px; max-width: 100%">
    <div class="container-md flex space-x-4 justify-between" style="height: 100%">
      <RouterLink :to="{name: 'home'}" @click="themeManager.toggleTheme()">
        <img v-if="isLightTheme" src="/images/logo.svg" alt="Logo" style="height: 100%">
        <img v-else src="/images/logo-dark.svg" alt="Logo" style="height: 100%">
      </RouterLink>
      <Button label="Войти" @click="setLoginDialogVisible(true)"/>
    </div>
  </div>

  <Dialog
      header="Вход"
      position="top"
      :visible="isLoginDialogVisible"
      :modal="true"
      :draggable="false"
      @update:visible="setLoginDialogVisible"
  >
    <LoginForm @switch-to-register="setRegisterDialogVisible(true)"></LoginForm>
  </Dialog>

  <Dialog
      header="Регистрация"
      position="top"
      :visible="isRegisterDialogVisible"
      :modal="true"
      :draggable="false"
      @update:visible="setRegisterDialogVisible"
  >
    <RegisterForm @switch-to-login="setLoginDialogVisible(true)"></RegisterForm>
  </Dialog>
</template>

<style scoped>

</style>
