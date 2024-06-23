<script setup lang="ts">
import type {PropType} from 'vue'
import {
    type PostVersionAction,
    type PostVersionActionAssignModerator,
    type PostVersionActionReject,
    type PostVersionActionRequestChanges,
    PostVersionActionType
} from '@/types'
import {getFullDate, getRelativeDate} from '@/helpers'
import UserAvatar from '@/components/user/UserAvatar.vue'

const props = defineProps({
    action: {
        type: Object as PropType<PostVersionAction>,
        required: true
    },
    minimized: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <div v-if="minimized" class="inline-block minimized-action">
        <span v-if="action.type === PostVersionActionType.SUBMIT">
            <span class="fa-solid fa-paper-plane text-muted mr-1.5"/>
            <span>Отправлена заявка.</span>
        </span>
        <span v-else>
            <span v-if="action.type === PostVersionActionType.ACCEPT">
                <span class="fa-solid fa-circle-check text-[var(--green-500)] mr-1.5"/>
                <span>Принято.</span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.REJECT">
                <span class="fa-solid fa-circle-xmark p-error mr-1.5"/>
                <span>Отклонено: </span>
                <span class="text-muted">{{ (action.details as PostVersionActionReject).reason }}</span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.REQUEST_CHANGES">
                <span class="fa-solid fa-rotate-left text-muted mr-1.5"/>
                <span>Возвращено на доработку: </span>
                <span class="text-muted">{{ (action.details as PostVersionActionRequestChanges).message }}</span>
            </span>
            <span v-else-if="action.type === PostVersionActionType.ASSIGN_MODERATOR">
                <span class="fa-solid fa-hammer text-muted mr-1.5"/>
                Назначен модератор
                <span class="text-[var(--primary-color)]">
                    {{ (action.details as PostVersionActionAssignModerator).moderator!.username }}
                </span>
                <span>.</span>
            </span>
        </span>
    </div>
    <div v-else class="flex flex-col xs:flex-row gap-1 xs:gap-2">
        <p
            :title="getFullDate(action.created_at!)"
            class="text-muted text-xs whitespace-nowrap leading-6"
        >
            {{ getRelativeDate(action.created_at!) }}
        </p>

        <div class="flex gap-2 items-start w-full">
            <div class="flex gap-3">
                <UserAvatar v-if="action.type === PostVersionActionType.SUBMIT" :user="action.user"/>
                <span
                    v-else
                    class="fa-solid fa-hammer text-[var(--surface-500)] min-w-[2rem] min-h-[2rem] text-center pt-1"
                />
            </div>

            <div class="flex flex-col gap-2 w-full text-sm xs:text-base">
                <div class="inline-block">
                    <span class="text-[var(--primary-color)]">
                        {{
                            action.user === undefined
                                ? 'Модератор'
                                : (action.user === null
                                        ? 'Удалённый пользователь'
                                        : action.user!.username
                                )
                        }}
                    </span>

                    <span v-if="action.type === PostVersionActionType.SUBMIT"> отправил заявку на публикацию.</span>
                    <span v-else-if="action.type === PostVersionActionType.ACCEPT"> принял заявку.</span>
                    <span v-else-if="action.type === PostVersionActionType.REJECT">
                        отклонил заявку:
                    </span>
                    <span v-else-if="action.type === PostVersionActionType.REQUEST_CHANGES">
                        вернул заявку на доработку:
                    </span>
                    <span v-else-if="action.type === PostVersionActionType.ASSIGN_MODERATOR">
                        назначил модератора
                        <span class="text-[var(--primary-color)]">
                            {{ (action.details as PostVersionActionAssignModerator).moderator!.username }}
                        </span>
                        <span>.</span>
                    </span>
                </div>

                <p
                    v-if="action.type === PostVersionActionType.REJECT"
                    class="border rounded bg-[var(--surface-50)] px-3 py-2 w-full"
                >
                    {{ (action.details as PostVersionActionReject).reason }}
                </p>

                <p
                    v-if="action.type === PostVersionActionType.REQUEST_CHANGES"
                    class="border rounded bg-[var(--surface-50)] px-3 py-2 w-full"
                >
                    {{ (action.details as PostVersionActionRequestChanges).message }}
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.minimized-action {
    @apply text-sm line-clamp-2
}
</style>
