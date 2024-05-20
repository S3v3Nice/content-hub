<script setup lang="ts">

import {type Post} from '@/types'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import Paginator, {type PageState} from 'primevue/paginator'
import {reactive, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {RouterLink} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'

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
            console.log(responseData.errors)
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
            <div v-for="post in posts"
                 class="h-[7rem] grid grid-cols-[5rem,1fr] gap-2 [&:not(:first-child)]:border-t p-3">
                <img :src="post.version!.cover_url" alt="" class="h-full object-cover object-center rounded-md">
                <div class="flex flex-col flex-grow overflow-hidden h-full">
                    <div class="text-muted text-xs lg:text-sm">
                        {{ new Date(post.updated_at).toLocaleDateString() }}
                    </div>
                    <RouterLink
                        :to="{ name: 'post', params: {slug: post.slug} }"
                        class="leading-5 lg:text-lg font-semibold hover:text-[var(--highlight-text-color)]
                               transition-colors line-clamp-3 lg:line-clamp-2"
                    >
                        {{ post.version!.title }}
                    </RouterLink>
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
