<script setup lang='ts'>
import {useToast} from 'primevue/usetoast'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {type User, UserRole} from '@/types/user'
import InputText from 'primevue/inputtext'
import Dropdown from 'primevue/dropdown'

interface ResponseData {
    success: boolean
    message?: string
    errors?: string[][]
}

const props = defineProps<{
    user?: User
}>()
const emit = defineEmits(['cancel', 'processed'])

const apiUrl = '/api/users'
const toastHelper = new ToastHelper(useToast())
const isProcessing = ref(false)
const errors = ref<string[][]>([])
const userRoles = ref([
    {label: 'Пользователь', value: UserRole.USER},
    {label: 'Модератор', value: UserRole.MODERATOR},
    {label: 'Администратор', value: UserRole.ADMIN},
])
const user = ref<User>(props.user || { role: UserRole.USER })

function update() {
    errors.value = []
    isProcessing.value = true

    axios.put(`${apiUrl}/${user.value.id}`, user.value!).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            toastHelper.success(`Данные пользователя ${user.value.username} изменены.`)
            emit('processed')
        } else {
            if (data.errors) {
                errors.value = data.errors
            }
            if (data.message) {
                toastHelper.error(data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}

function add() {
    errors.value = []
    isProcessing.value = true

    axios.post(`${apiUrl}`, user.value).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            toastHelper.success(`Добавлен новый пользователь ${user.value.username}.`)
            emit('processed')
        } else {
            if (data.errors) {
                errors.value = data.errors
            }
            if (data.message) {
                toastHelper.error(data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isProcessing.value = false
    })
}

function save() {
    if (user.value.id) {
        update()
    } else {
        add()
    }
}
</script>

<template>
    <form class="space-y-3" @submit.prevent="save">
        <div class="space-y-1">
            <label for="username" :class="{ 'p-error': errors['username'] }">Имя пользователя</label>
            <InputText
                id="username"
                v-model="user.username"
                class="w-full"
                :class="{ 'p-invalid': errors['username'] }"
                aria-describedby="username-error"
                autocomplete="username"
            />
            <small class="p-error" id="username-error">{{ errors['username']?.[0] || '&nbsp;' }}</small>
        </div>
        <div class="space-y-1">
            <label for="email" :class="{ 'p-error': errors['email'] }">E-mail</label>
            <InputText
                id="email"
                v-model="user.email"
                class="w-full"
                :class="{ 'p-invalid': errors['email'] }"
                aria-describedby="email-error"
                type="email"
                autocomplete="email"
            />
            <small class="p-error" id="email-error">{{ errors['email']?.[0] || '&nbsp;' }}</small>
        </div>
        <div v-if="!user.id" class="space-y-1">
            <label for="password" :class="{ 'p-error': errors['password'] }">Пароль</label>
            <InputText
                id="password"
                v-model="user['password']"
                class="w-full"
                :class="{ 'p-invalid': errors['password'] }"
                aria-describedby="password-error"
                type="password"
                autocomplete="password"
            />
            <small class="p-error" id="password-error">{{ errors['password']?.[0] || '&nbsp;' }}</small>
        </div>
        <div class="space-y-1">
            <label for="role" :class="{ 'p-error': errors['role'] }">Роль</label>
            <Dropdown
                input-id="role"
                :options="userRoles"
                option-label="label"
                option-value="value"
                v-model="user.role"
                class="w-full"
                :class="{ 'p-invalid': errors['role'] }"
                aria-describedby="role-error"
                autocomplete="role"
            />
            <small class="p-error" id="role-error">{{ errors['role']?.[0] || '&nbsp;' }}</small>
        </div>
        <div class="space-y-1">
            <label for="first-name" :class="{ 'p-error': errors['first_name'] }">Имя</label>
            <InputText
                id="first-name"
                v-model="user.first_name"
                class="w-full"
                :class="{ 'p-invalid': errors['first_name'] }"
                aria-describedby="first-name-error"
                autocomplete="given-name"
            />
            <small class="p-error" id="first-name-error">{{ errors['first_name']?.[0] || '&nbsp;' }}</small>
        </div>
        <div class="space-y-1">
            <label for="last-name" :class="{ 'p-error': errors['last_name'] }">Фамилия</label>
            <InputText
                id="last-name"
                v-model="user.last_name"
                class="w-full"
                :class="{ 'p-invalid': errors['last_name'] }"
                aria-describedby="last-name-error"
                autocomplete="family-name"
            />
            <small class="p-error" id="last-name-error">{{ errors['last_name']?.[0] || '&nbsp;' }}</small>
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Отмена" severity="secondary" @click="$emit('cancel')"></Button>
            <Button label="Сохранить" :loading="isProcessing" type="submit"></Button>
        </div>
    </form>
</template>

<style scoped>

</style>
