<script setup lang="ts">
import {getErrorMessageByCode, getFullDate, getRelativeDate, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {reactive, ref, watch} from 'vue'
import {type Post} from '@/types'
import axios, {type AxiosError} from 'axios'
import Paginator, {type PageState} from 'primevue/paginator'
import Button from 'primevue/button'
import Dropdown, {type DropdownPassThroughOptions} from 'primevue/dropdown'
import PostSingleActionsBar from '@/components/post/PostSingleActionsBar.vue'
import Skeleton from 'primevue/skeleton'
import {useRoute} from 'vue-router'
import UserAvatar from '@/components/user/UserAvatar.vue'

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

enum PostSortType {
    LATEST,
    POPULAR
}

enum PostLoadPeriod {
    DAY,
    WEEK,
    MONTH,
    YEAR,
    ALL_TIME
}

const props = defineProps({
    additionalLoadData: Object,
})

defineExpose({
    getTotalRecordsCount
})

const route = useRoute()
const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const posts = ref<Post[]>([])
const totalRecordsCount = ref(0)
const loadData = reactive({
    page: 1,
    per_page: 12,
    sort_type: PostSortType.LATEST,
    period: PostLoadPeriod.ALL_TIME,
})

const periodDropdownItems = [
    {label: '24 часа', value: PostLoadPeriod.DAY},
    {label: 'Неделя', value: PostLoadPeriod.WEEK},
    {label: 'Месяц', value: PostLoadPeriod.MONTH},
    {label: 'Год', value: PostLoadPeriod.YEAR},
    {label: 'Всё время', value: PostLoadPeriod.ALL_TIME},
]
const periodDropdownPassThroughOptions: DropdownPassThroughOptions = {
    root: {
        style: 'background: none; border: none; box-shadow: none; outline: none;'
    },
    input: {
        style: 'padding: 0.25rem 0.5rem; font-size: 0.875rem;',
    },
    trigger: {
        style: 'width: 2rem;'
    }
}

watch(route, () => {
    loadPosts()
})

loadPosts()

function loadPosts() {
    isLoading.value = true

    axios.get('/api/posts', {params: {...loadData, ...props.additionalLoadData}}).then((response) => {
        const responseData: PostLoadResponseData = response.data
        if (responseData.success) {
            totalRecordsCount.value = responseData.pagination!.total_records
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
    if (selectedPage !== loadData.page) {
        loadData.page = selectedPage
        loadPosts()
    }
}

function onSortTypeSelect(type: PostSortType) {
    loadData.sort_type = type
    loadData.page = 1
    switch (type) {
        case PostSortType.LATEST:
            loadData.period = PostLoadPeriod.ALL_TIME
            break
        case PostSortType.POPULAR:
            loadData.period = PostLoadPeriod.WEEK
            break
    }
    loadPosts()
}

function wasPostUpdated(post: Post) {
    return post.updated_at !== post.created_at
}

function getTotalRecordsCount() {
    return totalRecordsCount.value
}
</script>

<template>
    <div class="flex flex-col gap-4 surface-overlay rounded-lg border p-4 mb-3">
        <slot name="title">
            <p class="text-xl">Материалы</p>
        </slot>
        <div class="flex flex-col xs:flex-row gap-4">
            <div class="flex gap-2">
                <Button
                    label="Свежие"
                    icon="fa-regular fa-clock"
                    size="small"
                    :severity="loadData.sort_type === PostSortType.LATEST ? 'primary' : 'secondary'"
                    @click="onSortTypeSelect(PostSortType.LATEST)"
                />
                <Button
                    label="Популярные"
                    icon="fa-solid fa-fire"
                    size="small"
                    :severity="loadData.sort_type === PostSortType.POPULAR ? 'primary' : 'secondary'"
                    @click="onSortTypeSelect(PostSortType.POPULAR)"
                />
            </div>
            <Dropdown
                v-if="loadData.sort_type === PostSortType.POPULAR"
                v-model="loadData.period"
                :options="periodDropdownItems"
                option-label="label"
                option-value="value"
                scroll-height="14rem"
                :pt="periodDropdownPassThroughOptions"
                class="self-start xs:self-center"
                @update:model-value="loadPosts"
            />
        </div>
    </div>

    <div v-if="isLoading" class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
        <div v-for="i in 6" class="flex flex-col surface-overlay rounded-lg border p-4 gap-3">
            <Skeleton height="auto" class="aspect-video"/>
            <div class="flex flex-col gap-4">
                <div class="flex gap-2 items-center">
                    <Skeleton shape="circle" size="2rem"/>
                    <Skeleton height="0.6rem" width="6rem"/>
                    <div class="flex gap-1 ml-auto items-center">
                        <Skeleton shape="circle" size="1rem"/>
                        <Skeleton height="0.6rem" width="4.5rem"/>
                    </div>
                </div>
                <div class="flex flex-col gap-2">
                    <Skeleton height="1.25rem"/>
                    <Skeleton height="1.25rem" width="60%"/>
                </div>
                <div class="flex flex-col gap-2">
                    <Skeleton height="0.7rem" width="100%"/>
                    <Skeleton height="0.7rem" width="95%"/>
                    <Skeleton height="0.7rem" width="90%"/>
                    <Skeleton height="0.7rem" width="80%"/>
                    <Skeleton height="0.7rem" width="85%"/>
                    <Skeleton height="0.7rem" width="95%"/>
                    <Skeleton height="0.7rem" width="40%"/>
                </div>
                <div class="flex mt-3 gap-2">
                    <div v-for="i in 2" class="flex gap-1 items-center">
                        <Skeleton shape="circle" size="1.25rem"/>
                        <Skeleton height="0.6rem" width="2rem"/>
                    </div>
                    <div class="flex gap-1 ml-auto items-center">
                        <Skeleton shape="circle" size="1.25rem"/>
                        <Skeleton height="0.6rem" width="3rem"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <template v-else>
        <div v-if="posts.length === 0" class="px-4">
            <p class="text-muted">Материалы не найдены.</p>
        </div>
        <div v-else class="grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="post in posts"
                class="flex flex-col surface-overlay rounded-lg border px-4 pt-4 gap-3"
            >
                <RouterLink :to="{name: 'post', params: {slug: post.slug}}">
                    <img :src="post.version!.cover_url" alt="" class="post-cover"/>
                </RouterLink>

                <div class="flex gap-2 items-center">
                    <UserAvatar :user="post.version!.author"/>
                    <p class="text-sm">{{ post.version!.author!.username }}</p>
                    <div
                        :title="`${wasPostUpdated(post) ? 'Обновлено' : 'Опубликовано'} ${getFullDate(post.updated_at)}`"
                        class="text-muted text-xs lg:text-sm flex items-center gap-1.5 ml-auto"
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
                    <div class="text-xl font-bold transition-colors">
                        {{ post.version!.title }}
                    </div>
                    <p>{{ post.version!.description }}</p>
                </RouterLink>

                <PostSingleActionsBar :post="post" class="mt-auto flex"/>
            </div>
        </div>
    </template>
    <Paginator
        :rows="loadData.per_page"
        :totalRecords="totalRecordsCount"
        @page="onPageChange"
        class="mt-3 rounded-lg border"
        :class="{'hidden': isLoading || posts.length === 0}"
    />
</template>

<style scoped>
</style>
