<script setup lang="ts">
import InputText from "primevue/inputtext";
import Button from "primevue/button";
import {ref} from "vue";
import axios from "axios";
import router from "@/router";
import {useAuthStore} from "@/stores/auth";

defineEmits(['switch-to-login'])

const registerData = ref({
  username: '',
  email: '',
  password: '',
  password_confirmation: ''
})
const isProcessing = ref(false)
const errors = ref([])
const authStore = useAuthStore()

function submitRegister() {
  if (isProcessing.value) {
    return
  }

  isProcessing.value = true

  axios.post('/register', registerData.value).then((response) => {
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
      <label for="username">Имя пользователя</label>
      <InputText
          id="username"
          v-model="registerData.username"
          class="w-full"
      />
    </div>

    <div class="space-y-1">
      <label for="email">E-mail</label>
      <InputText
          id="email"
          v-model="registerData.email"
          class="w-full"
      />
    </div>

    <div class="space-y-1">
      <label for="password">Пароль</label>
      <InputText
          id="password"
          v-model="registerData.password"
          type="password"
          class="w-full"
      />
    </div>

    <div class="space-y-1">
      <label for="password-confirmation">Пароль ещё раз</label>
      <InputText
          id="password-confirmation"
          v-model="registerData.password_confirmation"
          type="password"
          class="w-full"
      />
    </div>

    <div class="space-y-3">
      <Button label="Зарегистрироваться" :loading="isProcessing" class="w-full" @click="submitRegister"></Button>
      <Button label="Уже есть учетная запись?" class="w-full p-button-link" @click="$emit('switch-to-login')"></Button>
    </div>
  </form>

</template>

<style scoped>

</style>