<script setup lang="ts">
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {type Post, type PostVersion} from '@/types'
import ProgressSpinner from 'primevue/progressspinner'
import Message from 'primevue/message'
import Avatar from 'primevue/avatar'
import PostSingleActionsBar from '@/components/post/PostSingleActionsBar.vue'

const props = defineProps({
    slug: {
        type: String,
        required: true
    }
})

const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const post = ref<Post>()

loadPost()

function loadPost() {
    isLoading.value = true

    axios.get(`/api/posts/${props.slug}`).then((response) => {
        const version: PostVersion = response.data

        if (Object.keys(version).length !== 0) {
            post.value = response.data
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}
</script>

<template>
    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <div v-if="post" class="grid lg:grid-cols-[1fr,19rem] gap-4">
            <div class="min-w-0">
                <div class="surface-overlay rounded-xl border">
                    <div class="space-y-4 p-4">
                        <div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3">
                            <div class="flex gap-2 items-center">
                                <Avatar :label="post.version!.author!.username![0]" shape="circle"/>
                                <p class="text-sm">{{ post.version!.author!.username }}</p>
                            </div>
                            <div class="flex gap-4">
                                <div class="text-muted text-xs lg:text-sm flex items-center gap-1.5">
                                    <span class="text-[var(--gray-400)] fa-regular fa-calendar"/>
                                    <p>{{ new Date(post.created_at).toLocaleDateString() }}</p>
                                </div>
                                <div
                                    v-if="post.updated_at !== post.created_at"
                                    class="text-muted text-xs lg:text-sm flex items-center gap-1.5"
                                >
                                    <span class="text-[var(--gray-400)] fa-solid fa-clock-rotate-left"/>
                                    <p>{{ new Date(post.updated_at).toLocaleDateString() }}</p>
                                </div>
                            </div>
                        </div>
                        <h1 class="post-title">{{ post.version!.title }}</h1>
                        <img :src="post.version!.cover_url" alt="" class="post-cover">
                    </div>

                    <div v-html="post.version!.content" class="post-content p-4"/>
                    <PostSingleActionsBar
                        :post="post"
                        class="flex sticky bottom-0 rounded-b-xl border-t overflow-x-auto
                               whitespace-nowrap surface-overlay px-2"
                    />
                </div>
            </div>

            <div
                class="hidden lg:block lg:sticky lg:right-0 lg:top-[--header-with-margin-height]
                       lg:max-h-[calc(100vh-var(--header-with-margin-height))]"
            >
            </div>
        </div>
        <div v-else class="flex justify-center">
            <Message :closable="false" severity="error">Материал не найден.</Message>
        </div>
    </template>
</template>

<style scoped>

</style>
