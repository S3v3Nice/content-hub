<script setup lang="ts">

import {type Post, type PostVersion, PostVersionStatus} from '@/types'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import Paginator, {type PageState} from 'primevue/paginator'
import {reactive, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import PostVersionCard from '@/components/post/PostVersionCard.vue'

interface PostVersionLoadResponseData {
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

const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const postVersions = ref<PostVersion[]>([])
const totalRecords = ref(0)
const loadRequestData = reactive({
    status: PostVersionStatus.PENDING,
    page: 1,
    per_page: 10,
})

loadPostVersions()

function loadPostVersions() {
    isLoading.value = true

    axios.get('/api/post-versions', {params: loadRequestData}).then((response) => {
        const responseData: PostVersionLoadResponseData = response.data
        if (responseData.success) {
            postVersions.value = responseData.records!
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
        loadPostVersions()
    }
}
</script>

<template>
    <template v-if="!isLoading">
        <div v-if="postVersions.length === 0">
            <p class="text-muted">Материалы не найдены.</p>
        </div>
        <div v-else class="rounded-md border">
            <PostVersionCard
                v-for="postVersion in postVersions"
                :post-version="postVersion"
                class="[&:not(:first-child)]:border-t"
            />
        </div>
    </template>
    <Paginator
        :rows="loadRequestData.per_page"
        :totalRecords="totalRecords"
        @page="onPageChange"
        :class="{'hidden': isLoading || postVersions.length === 0}"
    />
</template>

<style scoped>

</style>
