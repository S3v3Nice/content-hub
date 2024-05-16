<script setup lang="ts">
import Editor from '@/components/post/editor/Editor.vue'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import OverlayPanel from 'primevue/overlaypanel'

const toastHelper = new ToastHelper(useToast())
const editor = ref<InstanceType<typeof Editor>>()
const isSubmitting = ref(false)
const isCreatingDraft = ref(false)
const submitOverlayPanel = ref<OverlayPanel>()

function submit() {
    isSubmitting.value = true
    const postVersion = editor.value!.getPostVersion()

    const formData = new FormData()
    Object.keys(postVersion).forEach(key => formData.append(key, postVersion[key]))

    axios.post('/api/post-versions/submit', formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал отправлен на модерацию.')
            submitOverlayPanel.value?.hide()
            // TODO очищать редактор, либо переадресовывать на страницу со всеми материалами пользователя
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

function createDraft() {
    isCreatingDraft.value = true
    const postVersion = editor.value!.getPostVersion()

    const formData = new FormData()
    Object.keys(postVersion).forEach(key => formData.append(key, postVersion[key]))

    axios.post('/api/post-versions', formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал сохранён как черновик.')
            // TODO очищать редактор, либо переадресовывать на страницу со всеми материалами пользователя
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
        isCreatingDraft.value = false
    })
}
</script>

<template>
    <Editor ref="editor" editor-title="Создание материала">
        <template v-slot:actions>
            <Button
                class="w-full"
                label="Отправить на модерацию"
                :loading="isSubmitting"
                @click="submitOverlayPanel?.toggle"
            />
            <Button
                class="w-full mt-3"
                label="Сохранить как черновик"
                :loading="isCreatingDraft"
                severity="secondary"
                @click="createDraft"
            />
        </template>
    </Editor>

    <OverlayPanel ref="submitOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <p>Вы точно хотите отправить материал на модерацию?</p>

            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="submitOverlayPanel?.hide()"/>
                <Button size="small" label="Подтвердить" :loading="isSubmitting" @click="submit"/>
            </div>
        </div>
    </OverlayPanel>
</template>

<style scoped>

</style>
