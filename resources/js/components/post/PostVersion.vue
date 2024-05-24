<script setup lang="ts">
import PostEditor from '@/components/post/editor/PostEditor.vue'
import Button from 'primevue/button'
import OverlayPanel from 'primevue/overlaypanel'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import {computed, onUnmounted, reactive, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, getPostVersionStatusInfo, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {
    type PostVersion,
    type PostVersionActionReject,
    type PostVersionActionRequestChanges,
    PostVersionStatus
} from '@/types'
import ProgressSpinner from 'primevue/progressspinner'
import Message from 'primevue/message'
import slugify from '@sindresorhus/slugify'
import Tag from 'primevue/tag'
import {useAuthStore} from '@/stores/auth'

const props = defineProps({
    id: {
        type: Number,
        required: true
    }
})

const authStore = useAuthStore()
const toastHelper = new ToastHelper(useToast())
const postVersion = ref<PostVersion>()

const isLoading = ref(false)
const isSubmitting = ref(false)
const isUpdatingDraft = ref(false)
const isAccepting = ref(false)
const isRejecting = ref(false)
const isRequestingChanges = ref(false)

const submitOverlayPanel = ref<OverlayPanel>()
const acceptOverlayPanel = ref<OverlayPanel>()
const rejectOverlayPanel = ref<OverlayPanel>()
const requestChangesOverlayPanel = ref<OverlayPanel>()

const rejectDetails = reactive<PostVersionActionReject>({reason: ''})
const requestChangesDetails = reactive<PostVersionActionRequestChanges>({message: ''})
const customSlug = ref<string>()

const isReviewing = computed(() => authStore.isModerator && postVersion.value!.status === PostVersionStatus.PENDING)
const isOwnDraft = computed(() => postVersion.value!.author_id === authStore.id && postVersion.value!.status === PostVersionStatus.DRAFT)
const postVersionStatusInfo = computed(() => getPostVersionStatusInfo(postVersion.value?.status!))
const slug = computed({
    get() {
        return customSlug.value ?? slugify(postVersion.value?.title ?? '')
    },
    set(newValue) {
        customSlug.value = newValue
    }
})
const postUrl = computed(() => `${import.meta.env.VITE_APP_URL}/post/${slug.value}`)

loadPostVersion()
document.addEventListener('scroll', onScroll)

onUnmounted(() => {
    document.removeEventListener('scroll', onScroll)
})

function onScroll() {
    const overlayPanels = [submitOverlayPanel, acceptOverlayPanel, rejectOverlayPanel, requestChangesOverlayPanel]
    for (const overlayPanel of overlayPanels) {
        if (overlayPanel.value?.['visible']) {
            overlayPanel.value?.alignOverlay()
        }
    }
}

function loadPostVersion() {
    isLoading.value = true

    axios.get(`/api/post-versions/${props.id}`).then((response) => {
        const version: PostVersion = response.data

        if (Object.keys(version).length !== 0) {
            postVersion.value = response.data
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function submit() {
    isSubmitting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))

    axios.patch(`/api/post-versions/${props.id}/submit`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал отправлен на модерацию.')
            submitOverlayPanel.value?.hide()
            loadPostVersion()
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

function updateDraft() {
    isUpdatingDraft.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))

    axios.patch(`/api/post-versions/${props.id}`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Изменения сохранены.')
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
        isUpdatingDraft.value = false
    })
}

function accept() {
    isAccepting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    if (!postVersion.value?.post_id) {
        formData.append('slug', slug.value)
    }

    axios.patch(`/api/post-versions/${props.id}/accept`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал опубликован.')
            acceptOverlayPanel.value?.hide()
            loadPostVersion()
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
        isAccepting.value = false
    })
}

function reject() {
    isRejecting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    Object.keys(rejectDetails).forEach(key => formData.append(`details[${key}]`, rejectDetails[key]))

    axios.patch(`/api/post-versions/${props.id}/reject`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал отклонён.')
            rejectOverlayPanel.value?.hide()
            loadPostVersion()
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
        isRejecting.value = false
    })
}

function requestChanges() {
    isRequestingChanges.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    Object.keys(requestChangesDetails).forEach(key => formData.append(`details[${key}]`, requestChangesDetails[key]))

    axios.patch(`/api/post-versions/${props.id}/request-changes`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал возвращён на доработку.')
            requestChangesOverlayPanel.value?.hide()
            loadPostVersion()
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
        isRequestingChanges.value = false
    })
}
</script>

<template>
    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <PostEditor
            v-if="postVersion"
            v-model="postVersion"
            editor-title="Заявка на публикацию"
            :editable="isOwnDraft || isReviewing"
        >
            <template v-slot:actions>
                <div class="mb-3 space-y-1">
                    <p class="text-xs text-muted">Автор</p>
                    <p>{{ postVersion.author_id === authStore.id ? 'Вы' : postVersion.author!.username }}</p>
                </div>
                <hr class="mb-3"/>

                <p class="text-xs text-muted">Статус</p>
                <Tag :value="postVersionStatusInfo.name" :severity="postVersionStatusInfo.severity"/>

                <div v-if="isOwnDraft || isReviewing" class="flex flex-col gap-2 mt-3">
                    <hr class="mb-3"/>

                    <template v-if="isOwnDraft">
                        <Button
                            icon="fa-solid fa-check"
                            label="Отправить на модерацию"
                            outlined
                            @click="submitOverlayPanel?.toggle"
                        />
                        <Button
                            icon="fa-solid fa-floppy-disk"
                            label="Сохранить изменения"
                            severity="secondary"
                            :loading="isUpdatingDraft"
                            @click="updateDraft"
                        />
                    </template>
                    <template v-else>
                        <Button
                            icon="fa-solid fa-check"
                            label="Принять и опубликовать"
                            outlined
                            @click="acceptOverlayPanel?.toggle"
                        />
                        <Button
                            icon="fa-solid fa-ban"
                            label="Отклонить"
                            severity="danger"
                            outlined
                            @click="rejectOverlayPanel?.toggle"
                        />
                        <Button
                            icon="fa-solid fa-rotate-left"
                            label="Вернуть на доработку"
                            severity="secondary"
                            @click="requestChangesOverlayPanel?.toggle"
                        />
                    </template>
                </div>
            </template>
        </PostEditor>
        <div v-else class="flex justify-center">
            <Message :closable="false" severity="error">
                Запрашиваемая заявка на публикацию не существует, либо у вас нет к ней доступа.
            </Message>
        </div>
    </template>

    <OverlayPanel ref="submitOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <p>Вы точно хотите отправить материал на модерацию?</p>
            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="submitOverlayPanel?.hide()"/>
                <Button size="small" label="Да, отправить" :loading="isSubmitting" @click="submit"/>
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel ref="acceptOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <template v-if="postVersion?.post_id">
                <p>Вы точно хотите принять эту версию материала?</p>
            </template>
            <template v-else>
                <label for="accept-slug" class="text-sm">URL идентификатор</label>
                <InputText id="accept-slug" v-model="slug"/>
                <p class="text-xs text-muted">{{ postUrl }}</p>
            </template>
            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="acceptOverlayPanel?.hide()"/>
                <Button size="small" label="Опубликовать" :loading="isAccepting" @click="accept"/>
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel ref="rejectOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <label for="reject-reason" class="text-sm">Причина отклонения</label>
            <Textarea id="reject-reason" v-model="rejectDetails.reason" rows="3"/>
            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="rejectOverlayPanel?.hide()"/>
                <Button size="small" label="Отклонить" :loading="isRejecting" severity="danger" @click="reject"/>
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel ref="requestChangesOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <label for="request-changes-message" class="text-sm">Что не так?</label>
            <Textarea id="request-changes-message" v-model="requestChangesDetails.message" rows="5"/>
            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="requestChangesOverlayPanel?.hide()"/>
                <Button size="small" label="Отправить" :loading="isRequestingChanges" @click="requestChanges"/>
            </div>
        </div>
    </OverlayPanel>
</template>

<style scoped>

</style>
