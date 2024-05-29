<script setup lang="ts">
import Button from 'primevue/button'
import PostCommentEditor from '@/components/post/comment/PostCommentEditor.vue'
import Avatar from 'primevue/avatar'
import Menu, {type MenuPassThroughOptions} from 'primevue/menu'
import {type PostComment} from '@/types'
import {computed, onMounted, onUnmounted, type PropType, reactive, ref} from 'vue'
import axios, {type AxiosError, type AxiosResponse} from 'axios'
import {getAppUrl, getErrorMessageByCode, getFullDate, getRelativeDate, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {useAuthStore} from '@/stores/auth'
import type {MenuItem} from 'primevue/menuitem'
import {useRoute, useRouter} from 'vue-router'
import {useModalStore} from '@/stores/modal'

interface EditedComment {
    errors?: { [key: string]: string[] }
    content?: string
}

const emit = defineEmits<{
    (e: 'submitReply', id: bigint, content: string): void,
    (e: 'remove'): void,
}>()

const props = defineProps({
    comment: {
        type: Object as PropType<PostComment>,
        required: true
    }
})

const toastHelper = new ToastHelper(useToast())
const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const modalStore = useModalStore()
const comment = reactive(props.comment!)
const isSubmittingEdit = ref(false)
const isSubmittingReply = ref(false)
const editData = ref<EditedComment | null>(null)
const replyData = ref<EditedComment | null>(null)
const actionsMenu = ref()
const isHighlighted = ref(false)

const isEditable = computed(() => authStore.id === comment.user_id && !isEditTimeExpired.value)
const isRemovable = computed(() => authStore.isModerator || (authStore.id === comment.user_id && !isEditTimeExpired.value))

const isEditTimeExpired = computed(() => {
    const dateDiffInMinutes = (new Date().getTime() - new Date(comment.created_at).getTime()) / (1000 * 60)
    return dateDiffInMinutes >= 30
})

const actionsMenuItems = computed<MenuItem>(() => [
    {
        label: 'Отредактировать',
        icon: 'fa-solid fa-pen',
        visible: isEditable.value,
        command: () => editData.value = {content: comment.content},
    },
    {
        label: 'Удалить',
        icon: 'fa-solid fa-trash',
        visible: isRemovable.value,
        command: remove,
    },
    {
        label: 'Копировать ссылку',
        icon: 'fa-solid fa-link',
        command: copyUrl,
    },
])

const actionsMenuPassThroughOptions: MenuPassThroughOptions = {
    action: {
        class: 'text-sm',
    },
    icon: {
        style: 'width: 15px; margin-right: 0.75rem;'
    }
}

const removeAfterEachRouteHook = router.afterEach((to, from) => {
    if (to.path === from.path) {
        updateHighlighting()
    }
})

onMounted(() => {
    updateHighlighting()
})

onUnmounted(() => {
    removeAfterEachRouteHook()
    document.removeEventListener('mousedown', removeHighlighting)
})

function updateHighlighting() {
    isHighlighted.value = route.hash === getCommentUrlHash(comment.id)
    if (isHighlighted.value) {
        document.addEventListener('mousedown', removeHighlighting, {once: true})
    }
}

function removeHighlighting() {
    isHighlighted.value = false
}

function getCommentUrlHash(commentId: bigint): string {
    return `#comment-${commentId}`
}

function submitEdit() {
    editData.value!.errors = {}
    isSubmittingEdit.value = true

    axios.patch(
        `/api/post-comments/${comment.id}`, {content: editData.value!.content}
    ).then((response) => {
        if (response.data.success) {
            comment.updated_at = new Date().toISOString()
            comment.content = editData.value!.content!
            editData.value = null
            toastHelper.success('Комментарий изменён.')
        } else {
            if (response.data.errors) {
                editData.value!.errors = {} = response.data.errors
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmittingEdit.value = false
    })
}

function remove() {
    axios.delete(`/api/post-comments/${comment.id}`,).then((response) => {
        if (response.data.success) {
            toastHelper.success('Комментарий удалён.')
            emit('remove')
            actionsMenu.value.hide()
        } else {
            if (response.data.errors) {
                replyData.value!.errors = {} = response.data.errors
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    })
}

function submitReply() {
    replyData.value!.errors = {}
    isSubmittingReply.value = true

    axios.post(
        `/api/posts/${comment.post_id}/comments`,
        {parent_comment_id: comment.id, content: replyData.value!.content}
    ).then((response) => {
        if (response.data.success) {
            toastHelper.success('Ответ отправлен.')
            emit('submitReply', response.data.id, replyData.value!.content!)
            replyData.value = null
        } else {
            if (response.data.errors) {
                replyData.value!.errors = {} = response.data.errors
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmittingReply.value = false
    })
}

function copyUrl() {
    const url = new URL(
        router.resolve(
            {name: 'post', params: {slug: comment.post!.slug}, hash: getCommentUrlHash(comment.id)}
        ).fullPath,
        getAppUrl()
    ).href

    navigator.clipboard.writeText(url)
    toastHelper.success('Ссылка на комментарий скопирована.')
}

function toggleLike() {
    comment.is_liked = !comment.is_liked
    comment.like_count += comment.is_liked ? 1 : -1
}

function onLikeClick() {
    if (!authStore.isAuthenticated) {
        modalStore.auth = true
        return
    }

    toggleLike()

    const apiUrl = `/api/post-comments/${comment.id}/likes`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleLike()
            toastHelper.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }

    if (comment.is_liked) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}

function onReplyClick() {
    if (!authStore.isAuthenticated) {
        modalStore.auth = true
        return
    }
    replyData.value = {}
}
</script>

<template>
    <div class="flex gap-2 flex-1">
        <div v-if="editData" class="flex flex-col gap-2 flex-1 min-w-0">
            <p class="text-sm">Редактировать комментарий</p>
            <PostCommentEditor v-model="editData.content" class="border rounded-lg"/>
            <div class="flex flex-col xs:flex-row w-full xs:w-auto self-start gap-1">
                <Button label="Отмена" outlined severity="secondary" size="small" @click="editData = null"/>
                <Button label="Отправить" outlined size="small" :loading="isSubmittingEdit" @click="submitEdit"/>
            </div>
        </div>
        <template v-else>
            <Avatar :label="comment.user!.username![0]" shape="circle" class="min-w-[2rem]"/>
            <div class="flex flex-col gap-2 flex-1 min-w-0">
                <div
                    class="comment-header flex gap-2 px-1 -ml-1 rounded items-center transition-all duration-300 border border-[var(--primary-color)]"
                    :class="{'border-transparent': !isHighlighted}"
                >
                    <p class="text-sm font-semibold">{{ comment.user!.username }}</p>
                    <p :title="getFullDate(comment.created_at)" class="text-xs text-muted">
                        {{ getRelativeDate(comment.created_at) }}
                    </p>
                    <p
                        v-if="comment.created_at !== comment.updated_at"
                        :title="`Изменено ${getRelativeDate(comment.updated_at)} (${getFullDate(comment.updated_at)})`"
                        class="text-xs text-muted"
                    >
                        (ред.)
                    </p>
                    <span class="fa-solid fa-ellipsis p-text-secondary cursor-pointer" @click="actionsMenu!.toggle"/>
                </div>
                <div class="flex flex-col gap-1">
                    <span v-if="comment.parent_comment?.parent_comment_id">
                        <RouterLink
                            :to="{name: 'post', params: {slug: comment.post!.slug}, hash: getCommentUrlHash(comment.parent_comment_id), replace: true}"
                            class="text-[var(--primary-color)]"
                        >
                            @{{ comment.parent_comment!.user!.username }}
                        </RouterLink>
                        <span>,</span>
                    </span>

                    <span v-html="comment.content" class="post-comment-content"></span>
                </div>
                <div class="flex gap-3 items-center -ml-2">
                    <div class="flex items-center">
                        <Button
                            text
                            rounded
                            :title="comment.is_liked ? 'Больше не нравится' : 'Нравится'"
                            :severity="comment.is_liked ? 'danger' : 'secondary'"
                            :icon="`fa-heart ${comment.is_liked ? 'fa-solid' : 'fa-regular'}`"
                            :pt="{root: {style: 'height: 2rem; width: 2rem;'}}"
                            @click="onLikeClick"
                        />
                        <p class="text-sm" :class="{'p-error': comment.is_liked}">{{ comment.like_count }}</p>
                    </div>
                    <Button
                        size="small"
                        text
                        label="Ответить"
                        :pt="{root: {style: 'padding: 0 0.5rem;'}}"
                        @click="onReplyClick"
                    />
                </div>
                <div v-if="replyData" class="flex flex-col gap-2" @keydown.esc="() => replyData = null">
                    <div class="text-sm flex gap-1">
                        <p>Ответить</p>
                        <p class="font-semibold text-[var(--primary-color)]">{{ comment.user!.username }}</p>
                    </div>
                    <PostCommentEditor v-model="replyData.content" class="border rounded-lg"/>
                    <div class="flex flex-col xs:flex-row w-full xs:w-auto self-start gap-1">
                        <Button label="Отмена" outlined severity="secondary" size="small" @click="replyData = null"/>
                        <Button
                            label="Отправить"
                            outlined
                            size="small"
                            :loading="isSubmittingReply"
                            @click="submitReply"
                        />
                    </div>
                </div>
            </div>
        </template>

        <Menu
            ref="actionsMenu"
            :model="actionsMenuItems"
            :popup="true"
            :pt="actionsMenuPassThroughOptions"
        />
    </div>
</template>

<style scoped>

</style>
