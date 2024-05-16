<script setup lang='ts'>
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {ref} from 'vue'
import type {Post} from '@/types'
import axios, {type AxiosError} from 'axios'
import ProgressSpinner from 'primevue/progressspinner'

interface PostLoadResponseData {
    success: boolean
    message?: string
    errors?: string[][]
    records?: Post[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const posts = ref<Post[]>([])

loadPosts()

function loadPosts() {
    isLoading.value = true

    axios.get('/api/posts').then((response) => {
        const responseData: PostLoadResponseData = response.data
        if (responseData.success) {
            posts.value = response.data.records
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}
</script>

<template>
    <p class="text-xl mb-4">Последние материалы</p>
    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <div v-if="posts.length === 0" class="">
            <p class="text-muted">Материалы не найдены.</p>
        </div>
        <div v-else class="flex flex-wrap gap-4">
            <RouterLink
                v-for="post in posts"
                class="post-card flex flex-col surface-overlay rounded-lg border p-4 gap-3 w-full lg:w-[28rem]"
                :to="{name: 'post', params: {slug: post.slug}}"
            >
                <img :src="post.version!.cover_url" alt="" class="post-cover"/>
                <p class="text-xl font-bold post-card-title transition-colors">{{ post.version!.title }}</p>
                <p class="">{{ post.version!.description }}</p>
            </RouterLink>
        </div>
    </template>
</template>

<style scoped>
.post-card:hover .post-card-title {
    color: var(--highlight-text-color)
}
</style>
