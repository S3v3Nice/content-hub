<script setup lang="ts">
import PostEditor from '@/components/post/editor/PostEditor.vue'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import OverlayPanel from 'primevue/overlaypanel'
import {useRouter} from 'vue-router'
import type {PostVersion} from '@/types'

const toastHelper = new ToastHelper(useToast())
const router = useRouter()
const isSubmitting = ref(false)
const isCreatingDraft = ref(false)
const submitOverlayPanel = ref<OverlayPanel>()
const postVersion = ref<PostVersion>({})

function submit() {
    isSubmitting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value).forEach(key => formData.append(key, postVersion.value[key]))

    axios.post('/api/post-versions/submit', formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал отправлен на модерацию.')
            submitOverlayPanel.value?.hide()
            router.push({name: 'post-version', params: {id: response.data.id}})
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

    const formData = new FormData()
    Object.keys(postVersion.value).forEach(key => formData.append(key, postVersion.value[key]))

    axios.post('/api/post-versions', formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал сохранён как черновик.')
            const postVersionId = response.data.id
            router.push({name: 'post-version', params: {id: postVersionId}})
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
    <PostEditor v-model="postVersion" editor-title="Создание материала">
        <template v-slot:actions>
            <div class="flex flex-col gap-2">
                <Button
                    icon="fa-solid fa-check"
                    label="Отправить на модерацию"
                    outlined
                    @click="submitOverlayPanel?.toggle"
                />
                <Button
                    icon="fa-solid fa-floppy-disk"
                    label="Сохранить как черновик"
                    severity="secondary"
                    :loading="isCreatingDraft"
                    @click="createDraft"
                />
            </div>
        </template>
    </PostEditor>

    <OverlayPanel ref="submitOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <p>Вы точно хотите отправить материал на модерацию?</p>

            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="submitOverlayPanel?.hide()"/>
                <Button size="small" label="Да, отправить" :loading="isSubmitting" @click="submit"/>
            </div>
        </div>
    </OverlayPanel>
</template>

<style scoped>

</style>
