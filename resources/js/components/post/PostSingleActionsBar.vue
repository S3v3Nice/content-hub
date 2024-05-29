<script setup lang="ts">
import Button from 'primevue/button'
import {computed, type PropType, reactive} from 'vue'
import type {Post} from '@/types'
import axios, {type AxiosError, type AxiosResponse} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {useAuthStore} from '@/stores/auth'
import {useModalStore} from '@/stores/modal'

const props = defineProps({
    post: {
        type: Object as PropType<Post>,
        required: true
    },
})

const toastHelper = new ToastHelper(useToast())
const authStore = useAuthStore()
const modalStore = useModalStore()
const post = reactive(props.post!)

const icon = computed(() => {
    return `${post.is_liked ? 'fa-solid' : 'fa-regular'} fa-heart`
})

function toggleLike() {
    post.is_liked = !post.is_liked
    post.like_count += post.is_liked ? 1 : -1
}

function onLikeClick() {
    if (!authStore.isAuthenticated) {
        modalStore.auth = true
        return
    }

    toggleLike()

    const apiUrl = `/api/posts/${post.id}/likes`
    const responseCallback = function (response: AxiosResponse) {
        if (!response.data.success) {
            toggleLike()
            toastHelper.error(response.data.message)
        }
    }
    const errorCallback = function (error: AxiosError) {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }

    if (post.is_liked) {
        axios.post(apiUrl).then(responseCallback).catch(errorCallback)
    } else {
        axios.delete(apiUrl).then(responseCallback).catch(errorCallback)
    }
}
</script>

<template>
    <div class="flex gap-3">
        <div class="flex items-center">
            <Button
                text
                rounded
                :title="post.is_liked ? 'Больше не нравится' : 'Нравится'"
                :severity="post.is_liked ? 'danger' : 'secondary'"
                :icon="`fa-heart ${post.is_liked ? 'fa-solid' : 'fa-regular'}`"
                @click="onLikeClick"
            />
            <p class="text-sm" :class="{'p-error': post.is_liked}">{{ post.like_count }}</p>
        </div>

        <div class="flex items-center">
            <RouterLink :to="{name: 'post', params: {slug: post.slug}, hash: '#comments'}">
                <Button
                    text
                    rounded
                    title="Комментарии"
                    severity="secondary"
                    icon="fa-regular fa-comment"
                />
            </RouterLink>

            <p class="text-sm">{{ post.comment_count }}</p>
        </div>

        <div class="flex items-center ml-auto gap-2">
            <span title="Просмотры" class="fa-regular fa-eye text-[var(--text-color-secondary)]"/>
            <p class="text-sm">{{ post.view_count }}</p>
        </div>
    </div>
</template>

<style scoped>

</style>
