<script setup lang='ts'>
import BaseLayout from '@/components/layout/BaseLayout.vue'
import {ref} from 'vue'
import axios from 'axios'
import {useRoute} from 'vue-router'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import {useToast} from 'primevue/usetoast'

const toast = useToast()
const route = useRoute()
const resetPasswordApiUrl = route.path
const isProcessing = ref(false)
const errors = ref([])
const data = ref({
  password: '',
  password_confirmation: '',
  email: route.query.email,
  token: route.query.token,
})

function submitResetPassword() {
  isProcessing.value = true
  errors.value = []

  axios.post(resetPasswordApiUrl, data.value).then((response) => {
    if (!response.data.success) {
      if (response.data.errors) {
        errors.value = response.data.errors
      }
      if (response.data.message) {
        toast.add({severity: 'error', summary: 'Ошибка', detail: response.data.message, life: 5000})
      }
      return
    }

    toast.add({
      severity: 'success',
      summary: 'Успех',
      detail: response.data.message ?? `Пароль успешно сброшен.`
    })
  }).finally(() => {
    isProcessing.value = false
  })
}
</script>

<template>
  <BaseLayout>
    <div class="flex items-center justify-center">
      <div class="p-4 surface-overlay rounded-xl border" style="width: 30rem;">
        <p class="mb-6 text-2xl font-semibold">Сброс пароля</p>

        <form class="space-y-3">
          <div class="space-y-1">
            <label for="password" :class="{ 'p-error': errors['password'] }">Новый пароль</label>
            <InputText
                id="password"
                v-model="data.password"
                type="password"
                class="w-full"
                :class="{ 'p-invalid': errors['password'] }"
                aria-describedby="password-error"
                autocomplete="new-password"
            />
            <small class="p-error" id="password-error">{{ errors['password']?.[0] || '&nbsp;' }}</small>
          </div>

          <div class="space-y-1">
            <label for="password-confirmation" :class="{ 'p-error': errors['password_confirmation'] }">Новый пароль ещё
              раз</label>
            <InputText
                id="password-confirmation"
                v-model="data.password_confirmation"
                type="password"
                class="w-full"
                :class="{ 'p-invalid': errors['password_confirmation'] }"
                aria-describedby="password-confirmation-error"
                autocomplete="new-password"
            />
            <small class="p-error" id="password-confirmation-error">
              {{ errors['password_confirmation']?.[0] || '&nbsp;' }}
            </small>
          </div>

          <div class="pt-4 space-y-2">
            <Button
                type="submit"
                label="Сбросить пароль"
                class="w-full"
                :loading="isProcessing"
                @click.prevent="submitResetPassword"
            />
          </div>
        </form>
      </div>
    </div>
  </BaseLayout>
</template>

<style scoped>

</style>