<script setup lang="ts">
import {computed, type PropType} from 'vue'
import {type PostVersion, PostVersionActionType, PostVersionStatus} from '@/types'
import {RouterLink} from 'vue-router'
import Avatar from 'primevue/avatar'
import {getFullDate, getRelativeDate} from '@/helpers'
import Tag from 'primevue/tag'
import PostVersionActionComponent from '@/components/post/PostVersionAction.vue'

const props = defineProps({
    postVersion: {
        type: Object as PropType<PostVersion>,
        required: true
    },
    showAuthor: {
        type: Boolean,
        default: true
    }
})

const wasUpdated = computed(() => props.postVersion!.updated_at !== props.postVersion!.created_at)
const isFirstVersion = computed(() => !props.postVersion!.post || props.postVersion!.updated_at === props.postVersion!.post.created_at)
const lastAction = computed(() => props.postVersion!.actions!.at(props.postVersion!.actions!.length - 1))
</script>

<template>
    <div class="flex flex-col gap-2 p-3">
        <div class="flex xs:grid grid-cols-[6rem,1fr] gap-2">
            <RouterLink :to="{ name: 'post-version', params: {id: postVersion.id} }" class="hidden xs:block">
                <img :src="postVersion.cover_url" alt="" class="h-[4.5rem] object-cover object-center rounded-md">
            </RouterLink>
            <div class="flex flex-col gap-1.5 w-full">
                <div class="inline-block text-muted text-xs lg:text-sm">
                    <Tag
                        v-if="!isFirstVersion"
                        title="Запрос на внесение изменений в уже опубликованный материал"
                        value="Обновление"
                        severity="primary"
                        class="mr-2"
                    />
                    <span v-if="showAuthor">{{ postVersion.author!.username }} • </span>
                    <span
                        :title="`${wasUpdated ? 'Обновлено' : 'Создано'} ${getFullDate(postVersion!.updated_at)}`"
                    >
                        <span>{{ getRelativeDate(postVersion!.updated_at) }}</span>
                    </span>
                </div>
                <div class="flex gap-4 items-center mb-2">
                    <RouterLink
                        :to="{ name: 'post-version', params: {id: postVersion.id} }"
                        class="leading-5 lg:text-lg font-semibold hover:text-[var(--highlight-text-color)]
                               transition-colors line-clamp-2"
                    >
                        {{ postVersion.title }}
                    </RouterLink>
                    <Avatar
                        v-if="postVersion.assigned_moderator && postVersion.status !== PostVersionStatus.DRAFT"
                        :title="`Назначено на ${postVersion.assigned_moderator.username}`"
                        :label="postVersion.assigned_moderator.username![0]"
                        shape="circle"
                        class="ml-auto min-w-[2rem]"
                    />
                </div>

                <PostVersionActionComponent
                    v-if="[PostVersionActionType.REJECT, PostVersionActionType.REQUEST_CHANGES].includes(lastAction?.type)"
                    :action="lastAction"
                    :minimized="true"
                    class="mt-auto"
                />
            </div>
        </div>
    </div>
</template>

<style scoped>

</style>
