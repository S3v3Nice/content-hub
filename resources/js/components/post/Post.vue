<script setup lang="ts">
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {type Post, type PostVersion} from '@/types'
import ProgressSpinner from 'primevue/progressspinner'
import Message from 'primevue/message'

const props = defineProps({
    slug: {
        type: String,
        required: true
    }
})

const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const post = ref<Post>()

loadPost()

function loadPost() {
    isLoading.value = true

    axios.get(`/api/posts/${props.slug}`).then((response) => {
        const version: PostVersion = response.data

        if (Object.keys(version).length !== 0) {
            post.value = response.data
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}
</script>

<template>
    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <div v-if="!post" class="flex justify-center">
            <Message :closable="false" severity="error">Материал не найден.</Message>
        </div>
        <div v-else class="grid lg:grid-cols-[1fr,19rem] gap-4">
            <div class="min-w-0">
                <div class="surface-overlay rounded-xl border p-4">
                    <div class="space-y-4">
                        <h1 class="post-title">{{ post.version!.title }}</h1>
                        <img :src="post.version!.cover_url" alt="" class="post-cover">
                    </div>

                    <div v-html="post.version!.content" class="post-content"/>
                </div>
            </div>

            <div
                class="hidden lg:block lg:sticky lg:right-0 lg:top-[--header-with-margin-height] lg:max-h-[calc(100vh-var(--header-with-margin-height))]"
            >
            </div>
        </div>
    </template>
</template>

<style scoped>

</style>
