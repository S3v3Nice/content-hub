<script setup lang="ts">

import {type Post, type PostVersion, PostVersionStatus} from '@/types'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import Paginator, {type PageState} from 'primevue/paginator'
import {reactive, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import PostVersionCard from '@/components/post/PostVersionCard.vue'
import type {MenuItem} from 'primevue/menuitem'
import TabMenu, {type TabMenuChangeEvent} from 'primevue/tabmenu'
import Skeleton from 'primevue/skeleton'

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

const tabs = ref<MenuItem[]>([
    {label: 'Ожидающие', icon: 'fa-solid fa-hourglass-half', status: PostVersionStatus.PENDING},
    {label: 'Принятые', icon: 'fa-solid fa-check-circle', status: PostVersionStatus.ACCEPTED},
    {label: 'Отклонённые', icon: 'fa-solid fa-times-circle', status: PostVersionStatus.REJECTED},
])

loadPostVersions()

function loadPostVersions() {
    isLoading.value = true
    postVersions.value = []

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

function onTabChange(event: TabMenuChangeEvent) {
    const selectedStatus = tabs.value[event.index].status
    if (loadRequestData.status !== selectedStatus) {
        loadRequestData.status = selectedStatus
        loadRequestData.page = 1
        totalRecords.value = 0
        loadPostVersions()
    }
}
</script>

<template>
    <TabMenu class="mb-4" :model="tabs" @tab-change="onTabChange"/>

    <div v-if="isLoading" class="flex flex-col rounded-md border">
        <div v-for="i in 3" class="flex xs:grid grid-cols-[6rem,1fr] gap-2 p-3 [&:not(:first-child)]:border-t">
            <Skeleton height="4.5rem" class="hidden xs:block"/>
            <div class="flex flex-col w-full">
                <div class="flex mt-1 gap-3 items-center">
                    <Skeleton height="0.6rem" width="4rem"/>
                    <Skeleton height="0.6rem" width="6rem"/>
                </div>
                <div class="flex flex-col mt-4 gap-2">
                    <Skeleton height="0.9rem"/>
                    <Skeleton height="0.9rem" width="70%"/>
                </div>
            </div>
        </div>
    </div>
    <template v-else>
        <div v-if="postVersions.length === 0">
            <p class="text-muted">Заявки не найдены.</p>
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
        :class="{'hidden': !isLoading && postVersions.length === 0}"
    />
</template>

<style scoped>

</style>
