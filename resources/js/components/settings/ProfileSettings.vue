<script setup lang='ts'>
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import {ref} from 'vue'
import {useAuthStore} from '@/stores/auth'
import axios, {type AxiosError} from 'axios'
import {useToast} from 'primevue/usetoast'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'

const toastHelper = new ToastHelper(useToast())
const authStore = useAuthStore()

const data = ref({
    first_name: authStore.firstName ?? '',
    last_name: authStore.lastName ?? '',
})
const errors = ref([])
const isProcessing = ref(false)

function submit() {
    isProcessing.value = true
    errors.value = []

    axios.put('/api/settings/profile', data.value).then((response) => {
        if (response.data.success) {
            toastHelper.success('Настройки профиля изменены.')
            authStore.fetchUser()
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
    <form class="space-y-2">
        <div class="space-y-1">
            <label for="first-name" :class="{ 'p-error': errors['first_name'] }">Имя</label>
            <InputText
                id="first-name"
                v-model="data.first_name"
                class="w-full"
                :class="{ 'p-invalid': errors['first_name'] }"
                aria-describedby="new-first-name-error"
                autocomplete="given-name"
            />
            <small class="p-error" id="new-first-name-error">{{ errors['first_name']?.[0] || '&nbsp;' }}</small>
        </div>

        <div class="space-y-1">
            <label for="last-name" :class="{ 'p-error': errors['last_name'] }">Фамилия</label>
            <InputText
                id="last-name"
                v-model="data.last_name"
                class="w-full"
                :class="{ 'p-invalid': errors['last_name'] }"
                aria-describedby="new-last-name-error"
                autocomplete="family-name"
            />
            <small class="p-error" id="new-last-name-error">{{ errors['last_name']?.[0] || '&nbsp;' }}</small>
        </div>

        <div class="pt-2 flex gap-2 xs:flex-row flex-col">
            <Button
                type="submit"
                label="Сохранить"
                icon="fa-solid fa-check"
                :disabled="data.first_name === (authStore.firstName ?? '') && data.last_name === (authStore.lastName ?? '')"
                :loading="isProcessing"
                @click.prevent="submit"
            />
        </div>
    </form>
</template>

<style scoped>

</style>
