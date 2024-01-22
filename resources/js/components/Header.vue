<script setup lang="ts">
import {useAuthStore} from "@/stores/auth"
import {computed, ref} from "vue"
import axios from "axios"
import useThemeManager from "@/theme-manager";
import Button from "primevue/button"
import Dialog from "primevue/dialog"
import LoginForm from "@/components/auth/LoginForm.vue";
import RegisterForm from "@/components/auth/RegisterForm.vue";
import {useRouter} from "vue-router";
import ForgotPasswordForm from "@/components/auth/ForgotPasswordForm.vue";

const router = useRouter()
const authStore = useAuthStore()
const themeManager = ref(useThemeManager())
const isLightTheme = computed(() => themeManager.value.isLight())
const isLoginDialogVisible = ref(false)
const isForgotPasswordDialogVisible = ref(false)
const isRegisterDialogVisible = ref(false)

function logout() {
  axios.post('/logout').then(() => {
    router.go(0)
  })
}

function setLoginDialogVisible(isVisible: boolean) {
  if (isVisible) {
    isRegisterDialogVisible.value = false
    isForgotPasswordDialogVisible.value = false
  }
  isLoginDialogVisible.value = isVisible
}

function setForgotPasswordDialogVisible(isVisible: boolean) {
  if (isVisible) {
    isLoginDialogVisible.value = false
  }
  isForgotPasswordDialogVisible.value = isVisible
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
      <Button v-if="!authStore.isAuthenticated" label="Войти" @click="setLoginDialogVisible(true)"/>
      <Button v-else label="Выйти" @click="logout"/>
    </div>
  </div>

  <Dialog
      header="Вход"
      position="top"
      :visible="isLoginDialogVisible"
      :modal="true"
      :draggable="false"
      @update:visible="setLoginDialogVisible"
      style="width: 30rem;"
  >
    <LoginForm
        @switch-to-forgot-password="setForgotPasswordDialogVisible(true)"
        @switch-to-register="setRegisterDialogVisible(true)"
    />
  </Dialog>

  <Dialog
      header="Сброс пароля"
      position="top"
      :visible="isForgotPasswordDialogVisible"
      :modal="true"
      :draggable="false"
      @update:visible="setForgotPasswordDialogVisible"
      style="width: 30rem;"
  >
    <template #header>
      <div class="inline-flex items-center">
        <Button icon="fa-solid fa-arrow-left" aria-label="Назад" class="p-dialog-header-icon"
                @click="setLoginDialogVisible(true)"/>
        <span class="p-dialog-title">Сброс пароля</span>
      </div>
    </template>
    <ForgotPasswordForm/>
  </Dialog>

  <Dialog
      header="Регистрация"
      position="top"
      :visible="isRegisterDialogVisible"
      :modal="true"
      :draggable="false"
      @update:visible="setRegisterDialogVisible"
      style="width: 30rem;"
  >
    <RegisterForm @switch-to-login="setLoginDialogVisible(true)"/>
  </Dialog>
</template>

<style scoped>

</style>
