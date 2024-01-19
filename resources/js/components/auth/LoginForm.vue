<script setup lang="ts">
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import {ref} from "vue";
import axios from "axios";
import router from "@/router";
import {useAuthStore} from "@/stores/auth";

defineEmits(['switch-to-register'])

const loginData = ref({
  login: '',
  password: ''
})
const isProcessing = ref(false)
const errors = ref([])
const authStore = useAuthStore()

function submitLogin() {
  if (isProcessing.value) {
    return
  }

  isProcessing.value = true

  axios.post('/login', loginData.value).then((response) => {
    if (!response.data.success) {
      errors.value = response.data.errors
      return
    }

    authStore.fetchUser().then(() => {
      router.go(0)
    })
  }).finally(() => {
    isProcessing.value = false
  })
}

</script>

<template>
  <form class="space-y-7">
    <div class="space-y-1">
      <label for="login">E-mail / Имя пользователя</label>
      <InputText
          id="login"
          v-model="loginData.login"
          class="w-full"
      />
    </div>

    <div class="space-y-1">
      <label for="password">Пароль</label>
      <InputText
          id="password"
          v-model="loginData.password"
          type="password"
          class="w-full"
      />
    </div>

    <div class="space-y-3">
      <Button label="Войти" class="w-full" @click="submitLogin"></Button>
      <Button label="Еще нет учетной записи?" class="w-full p-button-link" @click="$emit('switch-to-register')"></Button>
    </div>
  </form>

</template>

<style scoped>

</style>