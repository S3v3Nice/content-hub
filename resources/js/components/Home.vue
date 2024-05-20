<script setup lang='ts'>
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {reactive, ref} from 'vue'
import {type Post} from '@/types'
import axios, {type AxiosError} from 'axios'
import ProgressSpinner from 'primevue/progressspinner'
import Paginator, {type PageState} from 'primevue/paginator'

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
const totalRecords = ref(0)
const loadRequestData = reactive({
    page: 1,
    per_page: 12,
})

loadPosts()

function loadPosts() {
    isLoading.value = true

    axios.get('/api/posts', {params: loadRequestData}).then((response) => {
        const responseData: PostLoadResponseData = response.data
        if (responseData.success) {
            totalRecords.value = responseData.pagination!.total_records
            posts.value = responseData.records
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function onPageChange(event: PageState) {
    const selectedPage = event.page + 1
    if (selectedPage !== loadRequestData.page) {
        loadRequestData.page = selectedPage
        loadPosts()
    }
}
</script>

<template>
    <div class="surface-overlay rounded-lg border p-4 mb-3">
        <p class="text-xl">Последние материалы</p>
    </div>

    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <div v-if="posts.length === 0" class="px-4">
            <p class="text-muted">Материалы не найдены.</p>
        </div>
        <div v-else class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <RouterLink
                v-for="post in posts"
                class="post-card flex flex-col surface-overlay rounded-lg border p-4 gap-3"
                :to="{name: 'post', params: {slug: post.slug}}"
            >
                <img :src="post.version!.cover_url" alt="" class="post-cover"/>
                <p class="text-xl font-bold post-card-title transition-colors">{{ post.version!.title }}</p>
                <p>{{ post.version!.description }}</p>
            </RouterLink>
        </div>
    </template>
    <Paginator
        :rows="loadRequestData.per_page"
        :totalRecords="totalRecords"
        @page="onPageChange"
        class="mt-3"
        :class="{'hidden': isLoading || posts.length === 0}"
    />
</template>

<style scoped>
.post-card:hover .post-card-title {
    color: var(--highlight-text-color)
}
</style>
