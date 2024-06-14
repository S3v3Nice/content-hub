<script setup lang="ts">

import {type Post} from '@/types'
import {getErrorMessageByCode, getFullDate, getRelativeDate, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import Paginator, {type PageState} from 'primevue/paginator'
import {reactive, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {RouterLink} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import PostSingleActionsBar from '@/components/post/PostSingleActionsBar.vue'

interface PostLoadResponseData {
    success: boolean
    message?: string
    errors?: object
    records?: Post[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const authStore = useAuthStore()
const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const posts = ref<Post[]>([])
const totalRecords = ref(0)
const loadRequestData = reactive({
    page: 1,
    per_page: 6,
})

loadPosts()

function loadPosts() {
    isLoading.value = true

    axios.get(`/api/users/${authStore.id}/posts`, {params: loadRequestData}).then((response) => {
        const responseData: PostLoadResponseData = response.data
        if (responseData.success) {
            posts.value = responseData.records!
            totalRecords.value = responseData.pagination!.total_records
        } else {
            toastHelper.error()
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
    <RouterLink :to="{name: 'create-post'}">
        <Button icon="fa-solid fa-plus" label="Создать" outlined class="mb-3"/>
    </RouterLink>
    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <div v-if="posts.length === 0">
            <p class="text-muted">Материалы не найдены.</p>
        </div>
        <div v-else class="rounded-md border">
            <div v-for="post in posts" class="flex flex-col gap-2 p-3 [&:not(:first-child)]:border-t">
                <div class="flex xs:grid grid-cols-[6rem,1fr] gap-2">
                    <RouterLink :to="{ name: 'post', params: {slug: post.slug} }" class="hidden xs:block">
                        <img
                            :src="post.version!.cover_url"
                            alt=""
                            class="h-[4.5rem] object-cover object-center rounded-md"
                        >
                    </RouterLink>
                    <div class="flex flex-col gap-1.5 w-full">
                        <div class="inline-block text-muted text-xs lg:text-sm">
                            <span
                                :title="`${post.version!.updated_at !== post.version!.created_at ? 'Обновлено' : 'Создано'} ${getFullDate(post.version!.updated_at!)}`"
                            >
                                <span>{{ getRelativeDate(post.version!.updated_at!) }}</span>
                            </span>
                        </div>
                        <div class="flex gap-4 items-center mb-2">
                            <RouterLink
                                :to="{ name: 'post', params: {slug: post.slug} }"
                                class="leading-5 lg:text-lg font-semibold hover:text-[var(--highlight-text-color)]
                               transition-colors line-clamp-2"
                            >
                                {{ post.version!.title }}
                            </RouterLink>
                        </div>

                        <PostSingleActionsBar :post="post"/>
                    </div>
                </div>
            </div>
        </div>
    </template>
    <Paginator
        :rows="loadRequestData.per_page"
        :totalRecords="totalRecords"
        @page="onPageChange"
        :class="{'hidden': isLoading || posts.length === 0}"
    />
</template>

<style scoped>

</style>
