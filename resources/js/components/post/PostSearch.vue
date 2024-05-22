<script setup lang='ts'>
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {computed, reactive, ref, watch} from 'vue'
import {type Post} from '@/types'
import axios, {type AxiosError} from 'axios'
import ProgressSpinner from 'primevue/progressspinner'
import Paginator, {type PageState} from 'primevue/paginator'
import Avatar from 'primevue/avatar'
import PostSingleActionsBar from '@/components/post/PostSingleActionsBar.vue'
import {useRoute} from 'vue-router'

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
const route = useRoute()
const isLoading = ref(false)
const posts = ref<Post[]>([])
const totalRecords = ref(0)
const loadRequestData = reactive({
    page: 1,
    per_page: 12,
})

loadPosts()

watch(route, () => {
    loadPosts()
})

const searchTerm = computed(() => route.query.term)

function loadPosts() {
    isLoading.value = true

    axios.get('/api/posts', {params: {...loadRequestData, ...route.query}}).then((response) => {
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
</script>

<template>
    <div class="surface-overlay rounded-lg border p-4 mb-3">
        <p class="text-xl">Результаты поиска</p>
        <p class="text-sm text-muted">
            Всего {{ totalRecords }} по запросу «{{ (searchTerm as string | undefined)?.trim() ?? '' }}».
        </p>
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
                    <div class="text-muted text-xs lg:text-sm flex items-center gap-1.5">
                        <span
                            class="text-[var(--gray-400)]"
                            :class="{'fa-regular fa-calendar': post.updated_at === post.created_at,
                                     'fa-solid fa-clock-rotate-left': post.updated_at !== post.created_at}"
                        />
                        <p>{{ new Date(post.updated_at).toLocaleDateString() }}</p>
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
