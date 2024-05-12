<script setup lang="ts">
import Editor from '@/components/post/editor/Editor.vue'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'

const toastHelper = new ToastHelper(useToast())
const editor = ref<Editor>()
const isSubmitting = ref(false)

function submitPostVersion() {
    isSubmitting.value = true
    const postVersion = editor.value!.getPostVersion()

    const formData = new FormData()
    Object.keys(postVersion).forEach(key => formData.append(key, postVersion[key]))

    axios.post('/api/post-versions', formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал отправлен на модерацию.')
        } else {
            if (response.data.errors) {
                toastHelper.error('Не все поля заполнены корректно.')
                // TODO отображать ошибки в редакторе
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmitting.value = false
    })
}
</script>

<template>
    <Editor ref="editor">
        <template v-slot:actions>
            <Button class="w-full" label="Отправить на модерацию" :loading="isSubmitting" @click="submitPostVersion"/>
        </template>
    </Editor>
</template>

<style scoped>

</style>
