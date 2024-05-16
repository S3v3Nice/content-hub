<script setup lang='ts'>
import {useToast} from 'primevue/usetoast'
import DataTable, {type DataTablePageEvent, type DataTableSortEvent} from 'primevue/datatable'
import Column from 'primevue/column'
import Tag from 'primevue/tag'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import {type User, UserRole} from '@/types'
import Dialog from 'primevue/dialog'
import UserForm from '@/components/dashboard/UserForm.vue'
import {useAuthStore} from '@/stores/auth'

interface ResponseData {
    success: boolean
    message?: string
    errors?: string[][]
    records?: User[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const apiUrl = '/api/users'
const authStore = useAuthStore()
const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const records = ref<User[]>([])
const totalRecords = ref(0)
const loadRecordsData = ref({
    page: 1,
    per_page: 10,
    sort_field: 'created_at',
    sort_order: -1
})
const currentRecord = ref<User | null>(null)
const isAddRecordModal = ref(false)
const isDeleteSelectedModal = ref(false)
const isEditRecordModal = ref(false)
const isDeleteRecordModal = ref(false)
const userRoles = ref([
    {label: 'Пользователь', value: UserRole.USER},
    {label: 'Модератор', value: UserRole.MODERATOR},
    {label: 'Администратор', value: UserRole.ADMIN},
])
const selectedRecords = ref<User[]>([])

loadRecords()

function loadRecords() {
    isLoading.value = true

    axios.get(apiUrl, {params: loadRecordsData.value}).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            records.value = data.records!
            totalRecords.value = data.pagination!.total_records
        } else {
            toastHelper.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    }).finally(() => {
        isLoading.value = false
    })
}

function getUserRoleSeverity(role: UserRole) {
    switch (role) {
        case UserRole.MODERATOR:
            return 'info'
        case UserRole.ADMIN:
            return 'danger'
        default:
            return 'secondary'
    }
}

function getUserRoleLabel(role: UserRole) {
    return userRoles.value.find((item) => item.value === role)?.label || ''
}

function onChangePage(event: DataTablePageEvent) {
    loadRecordsData.value.page = event.page + 1
    loadRecords()
}

function onSort(event: DataTableSortEvent) {
    loadRecordsData.value.sort_field = <string>event.sortField!
    loadRecordsData.value.sort_order = event.sortOrder!
    loadRecordsData.value.page = 1
    loadRecords()
}

function deleteRecord(record: User) {
    axios.delete(`${apiUrl}/${record.id}`).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            loadRecords()
            toastHelper.success(`Пользователь ${record.username} удалён.`)
        } else {
            toastHelper.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    })
}

function deleteSelected() {
    axios.delete(`${apiUrl}`, {params: {ids: selectedRecords.value.map((user) => user.id)}}).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            loadRecords()
            toastHelper.success(`Выбранные пользователи удалены.`)
        } else {
            toastHelper.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    })
}

function onEditClick(record: User) {
    currentRecord.value = {...record}
    isEditRecordModal.value = true
}

function onDeleteClick(record: User) {
    currentRecord.value = record
    isDeleteRecordModal.value = true
}

function onConfirmDeleteClick() {
    deleteRecord(currentRecord.value!)
    isDeleteRecordModal.value = false
}

function onConfirmDeleteSelectedClick() {
    deleteSelected()
    isDeleteSelectedModal.value = false
}

function onRecordSave() {
    loadRecords()
    isAddRecordModal.value = false
    isEditRecordModal.value = false
}
</script>

<template>
    <div v-if="authStore.isAdmin" class="flex mb-4">
        <Button
            title="Добавить"
            size="small"
            icon="fa-solid fa-plus"
            severity="success"
            class="mr-2"
            @click="() => isAddRecordModal = true"
        />
        <Button
            title="Удалить"
            size="small"
            icon="fa-solid fa-trash"
            severity="danger"
            @click="() => isDeleteSelectedModal = true"
            :disabled="selectedRecords.length === 0"
        />
    </div>

    <DataTable
        :value="records"
        lazy
        paginator
        removable-sort
        v-model:selection="selectedRecords"
        selectionMode="multiple"
        :rows="loadRecordsData.per_page"
        :total-records="totalRecords"
        @page="onChangePage($event)"
        @sort="onSort($event)"
    >
        <Column selectionMode="multiple" headerStyle="width: 3rem"></Column>
        <Column :sortable="true" field="username" header="Имя"></Column>
        <Column :sortable="true" field="email" header="E-mail">
            <template #body="{ data }">
                <div class="flex align-items-center gap-2">
                    <span>{{ data['email'] }}</span>
                    <Tag v-if="!data['email_verified_at']" severity="warning" title="Не подтверждён">
                        <template #icon>
                            <span class="fa-solid fa-triangle-exclamation"></span>
                        </template>
                    </Tag>
                </div>
            </template>
        </Column>
        <Column :sortable="true" field="role" header="Роль">
            <template #body="{ data }">
                <Tag :value="getUserRoleLabel(data['role'])" :severity="getUserRoleSeverity(data['role'])"></Tag>
            </template>
        </Column>
        <Column>
            <template #body="{ data }">
                <div class="flex align-items-center gap-2">
                    <Button
                        v-if="authStore.isAdmin"
                        icon="fa-solid fa-pen"
                        outlined
                        rounded
                        title="Редактировать"
                        @click="onEditClick(data)"
                    />
                    <Button
                        v-if="authStore.isAdmin"
                        icon="fa-solid fa-trash"
                        outlined
                        rounded
                        severity="danger"
                        title="Удалить"
                        @click="onDeleteClick(data)"
                    />
                </div>
            </template>
        </Column>
    </DataTable>

    <Dialog
        header="Добавление пользователя"
        position="top"
        :visible="isAddRecordModal"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        @update:visible="() => isAddRecordModal = !isAddRecordModal"
        style="width: 30rem;"
    >
        <UserForm @cancel="() => isAddRecordModal = false" @processed="onRecordSave"/>
    </Dialog>

    <Dialog
        header="Подтверждение"
        position="top"
        :visible="isDeleteSelectedModal"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        @update:visible="() => isDeleteSelectedModal = !isDeleteSelectedModal"
        style="width: 30rem;"
    >
        <div class="flex">
            <span class="fa-solid fa-triangle-exclamation mr-3" style="font-size: 2rem"/>
            Вы действительно хотите удалить выбранных пользователей?
        </div>

        <div class="flex justify-end gap-2 mt-2">
            <Button label="Отмена" severity="secondary" @click="() => { isDeleteSelectedModal = false }"></Button>
            <Button label="Удалить" @click="onConfirmDeleteSelectedClick" severity="danger"></Button>
        </div>
    </Dialog>

    <Dialog
        header="Редактирование пользователя"
        position="top"
        :visible="isEditRecordModal"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        @update:visible="() => isEditRecordModal = !isEditRecordModal"
        style="width: 30rem;"
    >
        <UserForm :user="currentRecord" @cancel="() => isEditRecordModal = false" @processed="onRecordSave"/>
    </Dialog>

    <Dialog
        header="Подтверждение"
        position="top"
        :visible="isDeleteRecordModal"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        @update:visible="() => isDeleteRecordModal = !isDeleteRecordModal"
        style="width: 30rem;"
    >
        <div class="flex">
            <span class="fa-solid fa-triangle-exclamation mr-3" style="font-size: 2rem"/>
            Вы действительно хотите удалить пользователя {{ currentRecord!.username }}?
        </div>

        <div class="flex justify-end gap-2 mt-2">
            <Button label="Отмена" severity="secondary" @click="() => { isDeleteRecordModal = false }"></Button>
            <Button label="Удалить" @click="onConfirmDeleteClick" severity="danger"></Button>
        </div>
    </Dialog>
</template>

<style scoped>

</style>
