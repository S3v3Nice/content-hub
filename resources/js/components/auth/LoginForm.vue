<script setup lang='ts'>
import InputText from 'primevue/inputtext'
import Checkbox from 'primevue/checkbox'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {useRouter} from 'vue-router'
import {useToast} from 'primevue/usetoast'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'

defineEmits(['switch-to-forgot-password', 'switch-to-register'])

const toastHelper = new ToastHelper(useToast())
const router = useRouter()
const isProcessing = ref(false)
const errors = ref([])
const loginData = ref({
    username: '',
    password: '',
    remember: true
})

function submitLogin() {
    isProcessing.value = true
    errors.value = []

    axios.post('/api/auth/login', loginData.value).then((response) => {
        if (response.data.success) {
            router.go(0)
        } else {
            if (response.data.errors) {
                errors.value = response.data.errors
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
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
        <div class="space-y-1">
            <label for="username" :class="{ 'p-error': errors['username'] }">Имя пользователя</label>
            <InputText
                id="username"
                v-model="loginData.username"
                class="w-full"
                :class="{ 'p-invalid': errors['username'] }"
                aria-describedby="username-error"
                autocomplete="username"
            />
            <small class="p-error" id="username-error">{{ errors['username']?.[0] || '&nbsp;' }}</small>
        </div>

        <div class="space-y-1">
            <label for="password" :class="{ 'p-error': errors['password'] }">Пароль</label>
            <InputText
                id="password"
                v-model="loginData.password"
                type="password"
                class="w-full"
                :class="{ 'p-invalid': errors['password'] }"
                aria-describedby="password-error"
                autocomplete="current-password"
            />
            <small class="p-error" id="password-error">{{ errors['password']?.[0] || '&nbsp;' }}</small>
        </div>

        <div class="sm:justify-between sm:flex sm:space-y-0 space-y-5">
            <div class="space-x-2">
                <Checkbox input-id="remember" :binary="true" v-model="loginData.remember"/>
                <label for="remember">Запомнить</label>
            </div>
            <Button label="Забыли пароль?" class="p-button-link" @click="$emit('switch-to-forgot-password')"/>
        </div>

        <div class="pt-4 space-y-2">
            <Button type="submit" label="Войти" class="w-full" :loading="isProcessing" @click.prevent="submitLogin"/>
            <Button label="Еще нет учетной записи?" class="w-full p-button-link" @click="$emit('switch-to-register')"/>
        </div>
    </form>
</template>

<style scoped>

</style>
