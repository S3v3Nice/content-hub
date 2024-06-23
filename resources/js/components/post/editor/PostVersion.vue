<script setup lang="ts">
import PostEditor from '@/components/post/editor/PostEditor.vue'
import PostVersionActionComponent from '@/components/post/PostVersionAction.vue'
import Button from 'primevue/button'
import OverlayPanel from 'primevue/overlaypanel'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Dialog from 'primevue/dialog'
import {computed, onUnmounted, reactive, ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {
    getAppUrl,
    getErrorMessageByCode,
    getFullDate,
    getPostVersionStatusInfo,
    getRelativeDate,
    ToastHelper
} from '@/helpers'
import {useToast} from 'primevue/usetoast'
import {
    type PostVersion,
    type PostVersionActionReject,
    type PostVersionActionRequestChanges,
    PostVersionActionType,
    PostVersionStatus,
    type User,
    UserRole
} from '@/types'
import ProgressSpinner from 'primevue/progressspinner'
import Message from 'primevue/message'
import slugify from '@sindresorhus/slugify'
import Tag from 'primevue/tag'
import {useAuthStore} from '@/stores/auth'
import {useRouter} from 'vue-router'
import Dropdown, {type DropdownPassThroughOptions} from 'primevue/dropdown'
import UserAvatar from '@/components/user/UserAvatar.vue'

const props = defineProps({
    id: {
        type: Number,
        required: true
    }
})

const router = useRouter()
const authStore = useAuthStore()
const toastHelper = new ToastHelper(useToast())
const postVersion = ref<PostVersion>()
const moderators = ref<User[]>()

const isLoading = ref(false)
const isSubmitting = ref(false)
const isUpdatingDraft = ref(false)
const isAccepting = ref(false)
const isRejecting = ref(false)
const isRequestingChanges = ref(false)
const isHistoryDialog = ref(false)

const moderatorDropdown = ref<Dropdown>()
const submitOverlayPanel = ref<OverlayPanel>()
const acceptOverlayPanel = ref<OverlayPanel>()
const rejectOverlayPanel = ref<OverlayPanel>()
const requestChangesOverlayPanel = ref<OverlayPanel>()

const rejectDetails = reactive<PostVersionActionReject>({reason: ''})
const requestChangesDetails = reactive<PostVersionActionRequestChanges>({message: ''})
const customSlug = ref<string>()

const moderatorOptions = computed(() => {
    if (!moderators.value) {
        const options: User[] = []
        if (postVersion.value!.assigned_moderator) {
            options.push(postVersion.value!.assigned_moderator)
        }
        if (postVersion.value!.assigned_moderator_id !== authStore.id) {
            options.push(authStore.user!)
        }

        return options
    }

    const firstModeratorIds = [postVersion.value!.assigned_moderator_id, authStore.id]

    return moderators.value.sort((a: User, b: User) => {
        if (firstModeratorIds.includes(a.id) && firstModeratorIds.includes(b.id)) {
            return firstModeratorIds.indexOf(a.id) - firstModeratorIds.indexOf(b.id)
        } else if (firstModeratorIds.includes(a.id)) {
            return -1
        } else if (firstModeratorIds.includes(b.id)) {
            return 1
        } else {
            return a.username!.localeCompare(b.username!)
        }
    })
})
const isFirstVersion = computed(() => !postVersion.value!.post || postVersion.value!.updated_at === postVersion.value!.post.created_at)
const wasUpdated = computed(() => postVersion.value!.updated_at !== postVersion.value!.created_at)
const isReviewing = computed(() => authStore.isModerator && postVersion.value!.status === PostVersionStatus.PENDING)
const isOwnDraft = computed(() => postVersion.value!.author_id === authStore.id && postVersion.value!.status === PostVersionStatus.DRAFT)
const postVersionStatusInfo = computed(() => getPostVersionStatusInfo(postVersion.value?.status!))
const slug = computed({
    get() {
        return customSlug.value ?? slugify(postVersion.value?.title ?? '')
    },
    set(newValue) {
        customSlug.value = newValue
    }
})
const postUrl = computed(() =>
    new URL(router.resolve({name: 'post', params: {slug: slug.value}}).fullPath, getAppUrl()).href
)
const lastAction = computed(() => postVersion.value!.actions!.at(postVersion.value!.actions!.length - 1))

const moderatorDropdownPassThroughOptions: DropdownPassThroughOptions = {
    root: {
        style: 'background: none; border: none; box-shadow: none; outline: none;'
    },
    input: {
        style: 'padding: 0; font-size: 0.875rem;',
    },
    trigger: {
        style: 'width: 2rem;'
    },
    panel: {
        style: 'z-index: 100;'
    },
    item: {
        style: 'padding: 0;'
    }
}

loadPostVersion()
document.addEventListener('scroll', onScroll)

onUnmounted(() => {
    document.removeEventListener('scroll', onScroll)
})

function onScroll() {
    moderatorDropdown.value?.hide()

    const overlayPanels = [submitOverlayPanel, acceptOverlayPanel, rejectOverlayPanel, requestChangesOverlayPanel]
    for (const overlayPanel of overlayPanels) {
        if (overlayPanel.value?.['visible']) {
            overlayPanel.value?.alignOverlay()
        }
    }
}

function loadPostVersion() {
    isLoading.value = true

    axios.get(`/api/post-versions/${props.id}`).then((response) => {
        const version: PostVersion = response.data

        if (Object.keys(version).length !== 0) {
            postVersion.value = version
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function loadModerators() {
    if (moderators.value) {
        return
    }

    axios.get(`/api/users`, {params: {roles: [UserRole.MODERATOR, UserRole.ADMIN]}}).then((response) => {
        moderators.value = response.data.records
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    })
}

function assignModerator(moderator: User) {
    if (moderator.id === postVersion.value!.assigned_moderator_id) {
        return
    }

    postVersion.value!.assigned_moderator = moderator

    axios.put(
        `/api/post-versions/${props.id}/assigned-moderator`,
        {moderator_id: moderator.id}
    ).then((response) => {
        if (response.data.success) {
            toastHelper.success(`Назначен модератор ${moderator.username}.`)
        } else {
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    })
}

function submit() {
    isSubmitting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))

    axios.patch(`/api/post-versions/${props.id}/submit`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал отправлен на модерацию.')
            submitOverlayPanel.value?.hide()
            loadPostVersion()
        } else {
            if (response.data.errors) {
                toastHelper.error('Не все поля заполнены корректно.')
                // TODO отображать ошибки в редакторе
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isSubmitting.value = false
    })
}

function updateDraft() {
    isUpdatingDraft.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))

    axios.patch(`/api/post-versions/${props.id}`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Изменения сохранены.')
        } else {
            if (response.data.errors) {
                toastHelper.error('Не все поля заполнены корректно.')
                // TODO отображать ошибки в редакторе
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isUpdatingDraft.value = false
    })
}

function accept() {
    isAccepting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    if (!postVersion.value?.post_id) {
        formData.append('slug', slug.value)
    }

    axios.patch(`/api/post-versions/${props.id}/accept`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал опубликован.')
            acceptOverlayPanel.value?.hide()
            loadPostVersion()
        } else {
            if (response.data.errors) {
                toastHelper.error('Не все поля заполнены корректно.')
                // TODO отображать ошибки в редакторе
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isAccepting.value = false
    })
}

function reject() {
    isRejecting.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    Object.keys(rejectDetails).forEach(key => formData.append(`details[${key}]`, rejectDetails[key]))

    axios.patch(`/api/post-versions/${props.id}/reject`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал отклонён.')
            rejectOverlayPanel.value?.hide()
            loadPostVersion()
        } else {
            if (response.data.errors) {
                toastHelper.error('Не все поля заполнены корректно.')
                // TODO отображать ошибки в редакторе
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isRejecting.value = false
    })
}

function requestChanges() {
    isRequestingChanges.value = true

    const formData = new FormData()
    Object.keys(postVersion.value!).forEach(key => formData.append(key, postVersion.value![key]))
    Object.keys(requestChangesDetails).forEach(key => formData.append(`details[${key}]`, requestChangesDetails[key]))

    axios.patch(`/api/post-versions/${props.id}/request-changes`, formData).then((response) => {
        if (response.data.success) {
            toastHelper.success('Материал возвращён на доработку.')
            requestChangesOverlayPanel.value?.hide()
            loadPostVersion()
        } else {
            if (response.data.errors) {
                toastHelper.error('Не все поля заполнены корректно.')
                // TODO отображать ошибки в редакторе
            }
            if (response.data.message) {
                toastHelper.error(response.data.message)
            }
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isRequestingChanges.value = false
    })
}
</script>

<template>
    <div v-if="isLoading" class="flex items-center">
        <ProgressSpinner/>
    </div>
    <template v-else>
        <Dialog
            v-if="postVersion"
            header="История действий"
            v-model:visible="isHistoryDialog"
            :modal="true"
            :draggable="false"
            :dismissable-mask="true"
            style="width: 50rem; height: 50rem;"
        >
            <div class="flex flex-col gap-5">
                <PostVersionActionComponent v-for="action in postVersion.actions" :action="action"/>
            </div>
        </Dialog>
        <PostEditor
            v-if="postVersion"
            v-model="postVersion"
            :author="postVersion.author"
            :editable="isOwnDraft || isReviewing"
        >
            <template v-slot:header>
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col sm:flex-row gap-2 sm:w-full items-start sm:items-center">
                        <p class="text-2xl font-semibold">Заявка на публикацию</p>
                        <div class="flex gap-4 sm:ml-auto">
                            <Tag
                                :title="isFirstVersion
                                    ? 'Запрос на добавление нового материала'
                                    : 'Запрос на внесение изменений в уже опубликованный материал'"
                                :value="isFirstVersion ? 'Новый материал' : 'Обновление'"
                                :severity="isFirstVersion ? 'info' : 'primary'"
                            />
                            <div
                                :title="`${wasUpdated ? 'Обновлено' : 'Создано'} ${getFullDate(postVersion!.updated_at!)}`"
                                class="text-muted text-xs lg:text-sm flex items-center gap-1.5"
                            >
                                <span
                                    class="text-[var(--gray-400)]"
                                    :class="{'fa-regular fa-calendar': !wasUpdated,
                                             'fa-solid fa-clock-rotate-left': wasUpdated}"
                                />
                                <p>{{ getRelativeDate(postVersion!.updated_at!) }}</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-[max-content,auto] justify-items-start items-center gap-y-2 gap-x-4
                               lg:hidden border-t pt-3"
                    >
                        <div
                            v-if="authStore.isModerator &&
                                  (postVersion.status !== PostVersionStatus.DRAFT || postVersion.assigned_moderator)"
                            class="header-details-item"
                        >
                            <p class="header-details-item-header">Модератор</p>
                            <Dropdown
                                v-if="postVersion!.status === PostVersionStatus.PENDING"
                                v-model="postVersion!.assigned_moderator_id"
                                :options="moderatorOptions"
                                placeholder="–"
                                option-label="username"
                                option-value="id"
                                :pt="moderatorDropdownPassThroughOptions"
                                @before-show="loadModerators"
                            >
                                <template #value="{placeholder}: {placeholder: string}">
                                    <div v-if="postVersion!.assigned_moderator" class="flex gap-2 items-center">
                                        <UserAvatar :user="postVersion!.assigned_moderator"/>
                                        <p class="hidden xs:block text-sm line-clamp-1">
                                            {{ postVersion!.assigned_moderator.username }}
                                        </p>
                                    </div>
                                    <span v-else>
                                        {{ placeholder }}
                                    </span>
                                </template>
                                <template #option="{option}: { option: User}">
                                    <div class="flex gap-2 items-center py-2 px-3 w-full"
                                         @click="assignModerator(option)">
                                        <UserAvatar :user="option"/>
                                        <p class="text-sm line-clamp-1">{{ option.username }}</p>
                                    </div>
                                </template>
                            </Dropdown>
                            <div v-else-if="postVersion!.assigned_moderator" class="flex gap-2 items-center">
                                <UserAvatar :user="postVersion!.assigned_moderator"/>
                                <p class="text-sm line-clamp-1">{{ postVersion!.assigned_moderator.username }}</p>
                            </div>
                            <p v-else class="text-sm">–</p>
                        </div>

                        <div v-if="postVersion!.post" class="header-details-item">
                            <p class="header-details-item-header">Материал</p>
                            <RouterLink
                                :to="{name: 'post', params: {slug: postVersion!.post.slug}}"
                                class="text-[var(--primary-color)] hover:underline line-clamp-2 text-sm xs:text-base"
                            >
                                {{ postVersion!.post.version!.title }}
                            </RouterLink>
                        </div>

                        <div class="header-details-item">
                            <p class="header-details-item-header">Статус</p>
                            <Tag
                                :value="postVersionStatusInfo.name"
                                :severity="postVersionStatusInfo.severity"
                            />
                        </div>
                    </div>

                    <div v-if="postVersion.actions!.length !== 0" class="flex flex-col gap-2 lg:hidden">
                        <PostVersionActionComponent
                            v-if="[PostVersionActionType.REJECT, PostVersionActionType.REQUEST_CHANGES].includes(lastAction?.type!)"
                            :action="lastAction"
                            :minimized="true"
                        />
                        <Button
                            icon="fa-solid fa-clock-rotate-left"
                            label="История действий"
                            severity="secondary xs:self-start"
                            @click="isHistoryDialog = true"
                        />
                    </div>
                </div>
            </template>
            <template v-slot:sidebar>
                <div class="flex flex-col">
                    <div
                        v-if="authStore.isModerator &&
                              (postVersion.status !== PostVersionStatus.DRAFT || postVersion.assigned_moderator)"
                        class="sidebar-item flex flex-col gap-2 items-start"
                    >
                        <p class="sidebar-item-header">Модератор</p>
                        <Dropdown
                            v-if="postVersion!.status === PostVersionStatus.PENDING"
                            ref="moderatorDropdown"
                            v-model="postVersion!.assigned_moderator_id"
                            :options="moderatorOptions"
                            placeholder="–"
                            option-label="username"
                            option-value="id"
                            :pt="moderatorDropdownPassThroughOptions"
                            class="w-full"
                            @before-show="loadModerators"
                        >
                            <template #value="{placeholder}: {placeholder: string}">
                                <div v-if="postVersion!.assigned_moderator" class="flex gap-2 items-center">
                                    <UserAvatar :user="postVersion!.assigned_moderator"/>
                                    <p class="text-sm line-clamp-1">{{ postVersion!.assigned_moderator.username }}</p>
                                </div>
                                <span v-else>
                                    {{ placeholder }}
                                </span>
                            </template>
                            <template #option="{option}: { option: User }">
                                <div class="flex gap-2 items-center py-2 px-3 w-full" @click="assignModerator(option)">
                                    <UserAvatar :user="option"/>
                                    <p class="text-sm line-clamp-1">{{ option.username }}</p>
                                </div>
                            </template>
                        </Dropdown>
                        <div v-else-if="postVersion!.assigned_moderator" class="flex gap-2 items-center">
                            <UserAvatar :user="postVersion!.assigned_moderator"/>
                            <p class="text-sm line-clamp-1">{{ postVersion!.assigned_moderator.username }}</p>
                        </div>
                        <p v-else class="text-sm">–</p>
                    </div>

                    <div v-if="postVersion!.post" class="sidebar-item flex flex-col gap-2 items-start">
                        <p class="sidebar-item-header">Материал</p>
                        <RouterLink
                            :to="{name: 'post', params: {slug: postVersion!.post.slug}}"
                            class="text-[var(--primary-color)] hover:underline line-clamp-2"
                        >
                            {{ postVersion!.post.version!.title }}
                        </RouterLink>
                    </div>

                    <div class="sidebar-item flex flex-col gap-2 items-start">
                        <p class="sidebar-item-header">Статус</p>
                        <Tag :value="postVersionStatusInfo.name" :severity="postVersionStatusInfo.severity"/>
                    </div>

                    <div v-if="postVersion.actions!.length !== 0" class="sidebar-item flex flex-col gap-2">
                        <PostVersionActionComponent
                            v-if="[PostVersionActionType.REJECT, PostVersionActionType.REQUEST_CHANGES].includes(lastAction?.type!)"
                            :action="lastAction"
                            :minimized="true"
                        />
                        <Button
                            icon="fa-solid fa-clock-rotate-left"
                            label="История действий"
                            severity="secondary w-full"
                            @click="isHistoryDialog = true"
                        />
                    </div>

                    <div v-if="isOwnDraft || isReviewing" class="sidebar-item flex flex-col gap-2">
                        <template v-if="isOwnDraft">
                            <Button
                                icon="fa-solid fa-check"
                                label="Отправить на модерацию"
                                outlined
                                @click="submitOverlayPanel?.toggle"
                            />
                            <Button
                                icon="fa-solid fa-floppy-disk"
                                label="Сохранить изменения"
                                severity="secondary"
                                outlined
                                :loading="isUpdatingDraft"
                                @click="updateDraft"
                            />
                        </template>
                        <template v-else>
                            <Button
                                icon="fa-solid fa-check"
                                label="Принять и опубликовать"
                                outlined
                                @click="acceptOverlayPanel?.toggle"
                            />
                            <Button
                                icon="fa-solid fa-ban"
                                label="Отклонить"
                                severity="danger"
                                outlined
                                @click="rejectOverlayPanel?.toggle"
                            />
                            <Button
                                icon="fa-solid fa-rotate-left"
                                label="Вернуть на доработку"
                                severity="secondary"
                                outlined
                                @click="requestChangesOverlayPanel?.toggle"
                            />
                        </template>
                    </div>
                </div>
            </template>
        </PostEditor>
        <div v-else class="flex justify-center">
            <Message :closable="false" severity="error">
                Запрашиваемая заявка на публикацию не существует, либо у вас нет к ней доступа.
            </Message>
        </div>
    </template>

    <OverlayPanel ref="submitOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <p>Вы точно хотите отправить материал на модерацию?</p>
            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="submitOverlayPanel?.hide()"/>
                <Button size="small" label="Да, отправить" :loading="isSubmitting" @click="submit"/>
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel ref="acceptOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <template v-if="postVersion?.post_id">
                <p>Вы точно хотите принять эту версию материала?</p>
            </template>
            <template v-else>
                <label for="accept-slug" class="text-sm">URL идентификатор</label>
                <InputText id="accept-slug" v-model="slug"/>
                <p class="text-xs text-muted">{{ postUrl }}</p>
            </template>
            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="acceptOverlayPanel?.hide()"/>
                <Button size="small" label="Опубликовать" :loading="isAccepting" @click="accept"/>
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel ref="rejectOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <label for="reject-reason" class="text-sm">Причина отклонения</label>
            <Textarea id="reject-reason" v-model="rejectDetails.reason" rows="3"/>
            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="rejectOverlayPanel?.hide()"/>
                <Button size="small" label="Отклонить" :loading="isRejecting" severity="danger" @click="reject"/>
            </div>
        </div>
    </OverlayPanel>

    <OverlayPanel ref="requestChangesOverlayPanel" class="w-[25rem] max-w-[100vw]">
        <div class="flex flex-col gap-2">
            <label for="request-changes-message" class="text-sm">Что не так?</label>
            <Textarea id="request-changes-message" v-model="requestChangesDetails.message" rows="5"/>
            <div class="flex gap-2 justify-end">
                <Button size="small" label="Отмена" severity="secondary" @click="requestChangesOverlayPanel?.hide()"/>
                <Button size="small" label="Отправить" :loading="isRequestingChanges" @click="requestChanges"/>
            </div>
        </div>
    </OverlayPanel>
</template>

<style scoped>
.header-details-item {
    @apply contents gap-4 items-start;
}

.header-details-item-header {
    @apply text-xs;
    color: var(--text-color-secondary);
}

.sidebar-item:not(:first-child) {
    @apply mt-3 pt-3 border-t border-[var(--surface-border)]
}

.sidebar-item-header {
    @apply text-xs;
    color: var(--text-color-secondary);
}
</style>
