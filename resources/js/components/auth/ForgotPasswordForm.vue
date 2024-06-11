<script setup lang='ts'>
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {useToast} from 'primevue/usetoast'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'

const toastHelper = new ToastHelper(useToast())
const isProcessing = ref(false)
const errors = ref([])
const forgotPasswordData = ref({
    email: '',
})

function submitForgotPassword() {
    isProcessing.value = true
    errors.value = []

    axios.post('/api/auth/forgot-password', forgotPasswordData.value).then((response) => {
        if (response.data.success) {
            toastHelper.success(`Ссылка для сброса пароля отправлена на ${forgotPasswordData.value.email}.`)
        } else {
            if (response.data.errors) {
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
            return
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}
</script>

<template>
    <form class="space-y-3">
        <p class="pb-3">Укажите ваш E-mail адрес, и мы отправим ссылку для сброса пароля.</p>
        <div class="space-y-1">
            <label for="email" :class="{ 'p-error': errors['email'] }">E-mail</label>
            <InputText
                id="email"
                v-model="forgotPasswordData.email"
                class="w-full"
                :class="{ 'p-invalid': errors['email'] }"
                aria-describedby="email-error"
                autocomplete="email"
            />
            <small class="p-error" id="email-error">{{ errors['email']?.[0] || '&nbsp;' }}</small>
        </div>

        <div class="pt-4 space-y-2">
            <Button
                type="submit"
                label="Отправить ссылку"
                class="w-full"
                :loading="isProcessing"
                @click.prevent="submitForgotPassword"
            />
        </div>
    </form>
</template>

<style scoped>

</style>
