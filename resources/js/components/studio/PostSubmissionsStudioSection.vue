<script setup lang="ts">

import {type Post, type PostVersion, PostVersionStatus} from '@/types'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import Paginator, {type PageState} from 'primevue/paginator'
import {reactive, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {RouterLink} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import type {MenuItem} from 'primevue/menuitem'
import TabMenu, {type TabMenuChangeEvent} from 'primevue/tabmenu'
import ProgressSpinner from 'primevue/progressspinner'
import Button from 'primevue/button'

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

const authStore = useAuthStore()
const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const postVersions = ref<PostVersion[]>([])
const totalRecords = ref(0)
const loadRequestData = reactive({
    status: PostVersionStatus.DRAFT,
    page: 1,
    per_page: 6,
})

const tabs = ref<MenuItem[]>([
    {label: 'Черновики', icon: 'fa-regular fa-file-alt', status: PostVersionStatus.DRAFT},
    {label: 'Проверяются', icon: 'fa-solid fa-hourglass-half', status: PostVersionStatus.PENDING},
    {label: 'Принятые', icon: 'fa-solid fa-check-circle', status: PostVersionStatus.ACCEPTED},
    {label: 'Отклонённые', icon: 'fa-solid fa-times-circle', status: PostVersionStatus.REJECTED},
])

loadPostVersions()

function loadPostVersions() {
    isLoading.value = true

    axios.get(`/api/users/${authStore.id}/post-versions`, {params: loadRequestData}).then((response) => {
        const responseData: PostVersionLoadResponseData = response.data
        if (responseData.success) {
            postVersions.value = responseData.records!
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
        loadPostVersions()
    }
}

function onTabChange(event: TabMenuChangeEvent) {
    loadRequestData.status = tabs.value[event.index].status
    loadPostVersions()
}
</script>

<template>
    <RouterLink :to="{name: 'create-post'}">
        <Button icon="fa-solid fa-plus" label="Создать" outlined class="mb-3"/>
    </RouterLink>

    <TabMenu class="mb-4" :model="tabs" @tab-change="onTabChange"/>

    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <div v-if="postVersions.length === 0">
            <p class="text-muted">Заявки не найдены.</p>
        </div>
        <template v-else>
            <div class="rounded-md border">
                <div v-for="postVersion in postVersions"
                     class="h-[7rem] grid grid-cols-[5rem,1fr] gap-2 [&:not(:first-child)]:border-t p-3">
                    <img :src="postVersion.cover_url" alt="" class="h-full object-cover object-center rounded-md">
                    <div class="flex flex-col flex-grow overflow-hidden h-full">
                        <div class="text-muted text-xs lg:text-sm">
                            {{ new Date(postVersion.updated_at!).toLocaleDateString() }}
                        </div>
                        <RouterLink
                            :to="{ name: 'post-version', params: {id: postVersion.id} }"
                            class="leading-5 lg:text-lg font-semibold hover:text-[var(--highlight-text-color)]
                                   transition-colors line-clamp-3 lg:line-clamp-2"
                        >
                            {{ postVersion.title }}
                        </RouterLink>
                    </div>
                </div>
            </div>
            <Paginator :rows="loadRequestData.per_page" :totalRecords="totalRecords" @page="onPageChange"/>
        </template>
    </template>
</template>

<style scoped>

</style>
