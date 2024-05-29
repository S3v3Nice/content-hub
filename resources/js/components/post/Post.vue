<script setup lang="ts">
import {computed, nextTick, onUnmounted, onUpdated, reactive, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {
    changeTitle,
    convertDateToString,
    getErrorMessageByCode,
    getFullDate,
    getRelativeDate,
    ToastHelper
} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {type Post, type PostComment} from '@/types'
import ProgressSpinner from 'primevue/progressspinner'
import Message from 'primevue/message'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import PostSingleActionsBar from '@/components/post/PostSingleActionsBar.vue'
import PostCommentComponent from '@/components/post/comment/PostComment.vue'
import PostCommentEditor from '@/components/post/comment/PostCommentEditor.vue'
import {useAuthStore} from '@/stores/auth'
import {useRoute} from 'vue-router'
import {useModalStore} from '@/stores/modal'

interface EditedComment {
    errors?: { [key: string]: string[] }
    content?: string
}

const props = defineProps({
    slug: {
        type: String,
        required: true
    }
})

const toastHelper = new ToastHelper(useToast())
const route = useRoute()
const authStore = useAuthStore()
const modalStore = useModalStore()
const isLoading = ref(true)
const post = ref<Post>()
const comments = ref<PostComment[]>()
const isLoadingComments = ref(true)
const isSubmittingNewComment = ref(false)
const commentsBlock = ref<Element>()
const newComment = reactive<EditedComment>({})
const commentsBlockObserver = new IntersectionObserver(observeCommentsBlock)

const rootComments = computed(() => comments.value?.filter((comment) => !comment.parent_comment_id))

onUpdated(() => {
    if (commentsBlock.value && !comments.value) {
        commentsBlockObserver.observe(commentsBlock.value!)
    }
})

onUnmounted(() => {
    commentsBlockObserver.disconnect()
})

loadPost()

function updateTitle() {
    nextTick(() => {
        changeTitle(post.value!.version!.title!)
    })
}

function observeCommentsBlock([entry]: IntersectionObserverEntry[]) {
    if (entry.isIntersecting) {
        loadComments()
    }
}

function loadPost() {
    axios.get(`/api/posts/${props.slug}`).then((response) => {
        const postData: Post = response.data

        if (Object.keys(postData).length !== 0) {
            post.value = postData
            updateTitle()

            if (route.hash === '#comments' || route.hash.match(/^#comment-\d+$/)) {
                loadComments()
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function loadComments() {
    commentsBlockObserver.disconnect()

    axios.get(`/api/posts/${post.value!.id}/comments`).then((response) => {
        comments.value = response.data.records
        updatePostCommentCount()
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoadingComments.value = false
    })
}

function submitNewComment() {
    newComment.errors = {}
    isSubmittingNewComment.value = true

    axios.post(`/api/posts/${post.value!.id}/comments`, {content: newComment.content}).then((response) => {
        if (response.data.success) {
            toastHelper.success('Комментарий отправлен.')
            addComment(response.data.id, newComment.content!)
            newComment.content = ''
        } else {
            if (response.data.errors) {
                newComment.errors = {} = response.data.errors
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmittingNewComment.value = false
    })
}

function addComment(id: bigint, content: string, parentComment: PostComment | null = null): void {
    const nowDate = convertDateToString(new Date())
    const comment: PostComment = {
        id: id,
        post_id: post.value!.id,
        parent_comment_id: parentComment?.id,
        parent_comment: parentComment,
        user_id: authStore.id!,
        user: authStore.user,
        content: content,
        like_count: 0,
        is_liked: false,
        created_at: nowDate,
        updated_at: nowDate
    }

    comments.value!.unshift(comment)
    updatePostCommentCount()
}

function removeComment(comment: PostComment) {
    comments.value = comments.value!.filter(v => v !== comment && !isDescendantComment(v, comment.id))
    updatePostCommentCount()
}

function updatePostCommentCount() {
    post.value!.comment_count = comments.value!.length
}

function isDescendantComment(comment: PostComment, ancestorId: bigint): boolean {
    while (comment?.parent_comment_id !== undefined) {
        if (comment.parent_comment_id === ancestorId) return true
        comment = comments.value!.find(c => c.id === comment.parent_comment_id)!
    }
    return false
}

function getDescendantComments(ancestorId: bigint): PostComment[] {
    return comments.value!
        .filter(comment => isDescendantComment(comment, ancestorId))
        .sort((a, b) => a.created_at.localeCompare(b.created_at))
}

function onNewCommentEditorClick() {
    if (!authStore.isAuthenticated) {
        modalStore.auth = true
    }
}
</script>

<template>
    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <div v-if="post" class="grid lg:grid-cols-[1fr,19rem] gap-4">
            <div class="min-w-0">
                <div class="surface-overlay rounded-xl border">
                    <div class="space-y-4 p-4">
                        <div class="flex flex-col xs:flex-row xs:justify-between xs:items-center gap-3">
                            <div class="flex gap-2 items-center">
                                <Avatar :label="post.version!.author!.username![0]" shape="circle"/>
                                <p class="text-sm">{{ post.version!.author!.username }}</p>
                            </div>
                            <div class="flex gap-4">
                                <div
                                    :title="`Опубликовано ${getFullDate(post.created_at)}`"
                                    class="text-muted text-xs lg:text-sm flex items-center gap-1.5"
                                >
                                    <span class="text-[var(--gray-400)] fa-regular fa-calendar"/>
                                    <p>{{ getRelativeDate(post.created_at) }}</p>
                                </div>
                                <div
                                    v-if="post.updated_at !== post.created_at"
                                    :title="`Обновлено ${getFullDate(post.updated_at)}`"
                                    class="text-muted text-xs lg:text-sm flex items-center gap-1.5"
                                >
                                    <span class="text-[var(--gray-400)] fa-solid fa-clock-rotate-left"/>
                                    <p>{{ getRelativeDate(post.updated_at) }}</p>
                                </div>
                            </div>
                        </div>
                        <h1 class="post-title">{{ post!.version!.title }}</h1>
                        <img :src="post.version!.cover_url" alt="" class="post-cover">
                    </div>

                    <div v-html="post.version!.content" class="post-content p-4"/>
                    <PostSingleActionsBar
                        :post="post"
                        class="flex sticky bottom-0 rounded-b-xl border-t overflow-x-auto
                               whitespace-nowrap surface-overlay px-2"
                    />
                </div>
            </div>

            <div
                class="hidden lg:block lg:sticky lg:right-0 lg:top-[--header-with-margin-height]
                       lg:max-h-[calc(100vh-var(--header-with-margin-height))]"
            >
            </div>

            <div id="comments" class="surface-overlay rounded-lg border p-4 min-w-0">
                <div class="flex gap-3">
                    <p class="text-lg font-semibold">Комментарии</p>
                    <p class="text-lg font-semibold text-[var(--primary-color)]">{{ post!.comment_count }}</p>
                </div>

                <div class="flex flex-col gap-2 mt-4">
                    <PostCommentEditor
                        v-model="newComment.content"
                        class="border rounded-lg"
                        @click="onNewCommentEditorClick"
                    />
                    <Button
                        label="Отправить"
                        outlined
                        size="small"
                        :loading="isSubmittingNewComment"
                        :disabled="!authStore.isAuthenticated"
                        class="self-start"
                        @click="submitNewComment"
                    />
                </div>

                <div ref="commentsBlock">
                    <div v-if="isLoadingComments" class="flex flex-col gap-6 mt-8">
                        <div v-for="i in Math.min(post.comment_count, 5)" class="flex flex-col gap-2">
                            <div class="flex gap-2">
                                <Skeleton shape="circle" size="2rem"/>
                                <div class="flex flex-col flex-1 gap-2">
                                    <Skeleton width="10rem"/>
                                    <Skeleton height="3rem"/>
                                    <Skeleton width="7rem"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else-if="comments!.length !== 0" class="flex flex-col gap-6 mt-8">
                        <div v-for="comment in rootComments" class="flex flex-col gap-6">
                            <PostCommentComponent
                                :comment="{...comment, ...{post: post}}"
                                :id="`comment-${comment.id}`"
                                :key="comment.id"
                                @submit-reply="(id, content) => addComment(id, content, comment)"
                                @remove="removeComment(comment)"
                            />
                            <PostCommentComponent
                                v-for="descendantComment in getDescendantComments(comment.id)"
                                :comment="{...descendantComment, ...{post: post}}"
                                :id="`comment-${descendantComment.id}`"
                                :key="descendantComment.id"
                                class="ml-10"
                                @submit-reply="(id, content) => addComment(id, content, descendantComment)"
                                @remove="removeComment(descendantComment)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="flex justify-center">
            <Message :closable="false" severity="error">Материал не найден.</Message>
        </div>
    </template>
</template>

<style scoped>

</style>
