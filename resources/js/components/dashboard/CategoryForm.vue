<script setup lang='ts'>
import {useToast} from 'primevue/usetoast'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {type PostCategory} from '@/types'
import InputText from 'primevue/inputtext'

interface ResponseData {
    success: boolean
    message?: string
    errors?: string[][]
}

const props = defineProps<{
    category?: PostCategory
}>()
const emit = defineEmits(['cancel', 'processed'])

const apiUrl = '/api/post-categories'
const toastHelper = new ToastHelper(useToast())
const isProcessing = ref(false)
const errors = ref<string[][]>([])
const category = ref<PostCategory>(props.category || {})

function update() {
    errors.value = []
    isProcessing.value = true

    axios.put(`${apiUrl}/${category.value.id}`, category.value!).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            toastHelper.success(`Данные категории "${category.value.name}" изменены.`)
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

    axios.post(`${apiUrl}`, category.value).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            toastHelper.success(`Добавлена новая категория "${category.value.name}".`)
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
    if (category.value.id) {
        update()
    } else {
        add()
    }
}
</script>

<template>
    <form class="space-y-3" @submit.prevent="save">
        <div class="space-y-1">
            <label for="slug" :class="{ 'p-error': errors['slug'] }">Ярлык</label>
            <InputText
                id="slug"
                v-model="category.slug"
                class="w-full"
                :class="{ 'p-invalid': errors['slug'] }"
                aria-describedby="slug-error"
                type="slug"
                autocomplete="off"
            />
            <small class="p-error" id="slug-error">{{ errors['slug']?.[0] || '&nbsp;' }}</small>
        </div>
        <div class="space-y-1">
            <label for="name" :class="{ 'p-error': errors['name'] }">Название</label>
            <InputText
                id="name"
                v-model="category.name"
                class="w-full"
                :class="{ 'p-invalid': errors['name'] }"
                aria-describedby="name-error"
                autocomplete="off"
            />
            <small class="p-error" id="name-error">{{ errors['name']?.[0] || '&nbsp;' }}</small>
        </div>
        <div class="flex justify-end gap-2">
            <Button label="Отмена" severity="secondary" @click="$emit('cancel')"></Button>
            <Button label="Сохранить" :loading="isProcessing" type="submit"></Button>
        </div>
    </form>
</template>

<style scoped>

</style>
