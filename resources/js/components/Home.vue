<script setup lang='ts'>
import {getErrorMessageByCode, getFullDate, getRelativeDate, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {reactive, ref} from 'vue'
import {type Post} from '@/types'
import axios, {type AxiosError} from 'axios'
import ProgressSpinner from 'primevue/progressspinner'
import Paginator, {type PageState} from 'primevue/paginator'
import Avatar from 'primevue/avatar'
import PostSingleActionsBar from '@/components/post/PostSingleActionsBar.vue'

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
            posts.value = responseData.records!
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

function wasPostUpdated(post: Post) {
    return post.updated_at !== post.created_at
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
            <div
                v-for="post in posts"
                class="post-card flex flex-col surface-overlay rounded-lg border p-4 gap-3"
            >
                <RouterLink :to="{name: 'post', params: {slug: post.slug}}">
                    <img :src="post.version!.cover_url" alt="" class="post-cover"/>
                </RouterLink>

                <div class="flex justify-between items-center">
                    <div class="flex gap-2 items-center">
                        <Avatar :label="post.version!.author!.username![0]" shape="circle"/>
                        <p class="text-sm">{{ post.version!.author!.username }}</p>
                    </div>
                    <div
                        :title="`${wasPostUpdated(post) ? 'Обновлено' : 'Опубликовано'} ${getFullDate(post.updated_at)}`"
                        class="text-muted text-xs lg:text-sm flex items-center gap-1.5"
                    >
                        <span
                            class="text-[var(--gray-400)]"
                            :class="{'fa-regular fa-calendar': !wasPostUpdated(post),
                                     'fa-solid fa-clock-rotate-left': wasPostUpdated(post)}"
                        />
                        <p>{{ getRelativeDate(post.updated_at) }}</p>
                    </div>
                </div>
                <RouterLink :to="{name: 'post', params: {slug: post.slug}}" class="flex flex-col gap-3">
                    <div class="text-xl font-bold post-card-title transition-colors">
                        {{ post.version!.title }}
                    </div>
                    <p>{{ post.version!.description }}</p>
                </RouterLink>

                <PostSingleActionsBar :post="post" class="mt-auto flex"/>
            </div>
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
</style>
