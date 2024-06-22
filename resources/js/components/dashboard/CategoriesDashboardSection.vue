<script setup lang='ts'>
import {useToast} from 'primevue/usetoast'
import DataTable, {type DataTablePageEvent, type DataTableSortEvent} from 'primevue/datatable'
import Column from 'primevue/column'
import Button from 'primevue/button'
import {ref} from 'vue'
import axios, {type AxiosError} from 'axios'
import {getErrorMessageByCode, ToastHelper} from '@/helpers'
import Dialog from 'primevue/dialog'
import CategoryForm from '@/components/dashboard/CategoryForm.vue'
import type {PostCategory} from '@/types'
import Skeleton from 'primevue/skeleton'

interface ResponseData {
    success: boolean
    message?: string
    errors?: string[][]
    records?: PostCategory[]
    pagination?: {
        total_records: number
        current_page: number
        total_pages: number
    }
}

const apiUrl = '/api/post-categories'
const toastHelper = new ToastHelper(useToast())
const isLoading = ref(false)
const records = ref<PostCategory[]>([])
const totalRecords = ref(0)
const loadRecordsData = ref({
    page: 1,
    per_page: 10,
    sort_field: 'created_at',
    sort_order: -1
})
const currentRecord = ref<PostCategory | null>(null)
const isAddRecordModal = ref(false)
const isDeleteSelectedModal = ref(false)
const isEditRecordModal = ref(false)
const isDeleteRecordModal = ref(false)
const selectedRecords = ref<PostCategory[]>([])

loadRecords()

function loadRecords() {
    isLoading.value = true
    records.value = []

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

function deleteRecord(record: PostCategory) {
    axios.delete(`${apiUrl}/${record.id}`).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            loadRecords()
            toastHelper.success(`Категория "${record.name}" удалена.`)
        } else {
            toastHelper.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    })
}

function deleteSelected() {
    axios.delete(`${apiUrl}`, {params: {ids: selectedRecords.value.map((category) => category.id)}}).then((response) => {
        const data: ResponseData = response.data
        if (data.success) {
            loadRecords()
            toastHelper.success(`Выбранные категории удалены.`)
        } else {
            toastHelper.error(data.message || '')
        }
    }).catch((error: AxiosError) => {
        toastHelper.error(getErrorMessageByCode(error.response!.status))
    })
}

function onEditClick(record: PostCategory) {
    currentRecord.value = {...record}
    isEditRecordModal.value = true
}

function onDeleteClick(record: PostCategory) {
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
    <div class="flex mb-4">
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
        :value="isLoading ? new Array(3) : records"
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
        <Column v-if="isLoading" headerStyle="width: 3rem">
            <template #header>
                <Skeleton width="1.4rem" height="1.4rem"/>
            </template>
            <template #body>
                <Skeleton width="1.4rem" height="1.4rem"/>
            </template>
        </Column>
        <Column v-else selectionMode="multiple" headerStyle="width: 3rem"/>
        <Column :sortable="true" field="slug" header="Ярлык">
            <template #body="{ data, field }: {data: PostCategory, field: string}">
                <Skeleton v-if="isLoading" width="6rem"/>
                <template v-else>{{ data[field] }}</template>
            </template>
        </Column>
        <Column :sortable="true" field="name" header="Название">
            <template #body="{ data, field }: {data: PostCategory, field: string}">
                <Skeleton v-if="isLoading" width="10rem"/>
                <template v-else>{{ data[field] }}</template>
            </template>
        </Column>
        <Column>
            <template #body="{ data }: {data: PostCategory}">
                <div class="flex gap-2">
                    <template v-if="isLoading">
                        <Skeleton shape="circle" size="2.5rem"/>
                        <Skeleton shape="circle" size="2.5rem"/>
                    </template>
                    <template v-else>
                        <Button
                            icon="fa-solid fa-pen"
                            outlined
                            rounded
                            title="Редактировать"
                            @click="onEditClick(data)"
                        />
                        <Button
                            icon="fa-solid fa-trash"
                            outlined
                            rounded
                            severity="danger"
                            @click="onDeleteClick(data)"
                            title="Удалить"
                        />
                    </template>
                </div>
            </template>
        </Column>
    </DataTable>

    <Dialog
        header="Добавление категории"
        position="top"
        :visible="isAddRecordModal"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        :block-scroll="true"
        @update:visible="() => isAddRecordModal = !isAddRecordModal"
        style="width: 30rem;"
    >
        <CategoryForm @cancel="() => isAddRecordModal = false" @processed="onRecordSave"/>
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
            Вы действительно хотите удалить выбранные категории (всего {{ selectedRecords.length }})?
        </div>

        <div class="flex justify-end gap-2 mt-2">
            <Button label="Отмена" severity="secondary" @click="() => { isDeleteSelectedModal = false }"></Button>
            <Button label="Удалить" @click="onConfirmDeleteSelectedClick" severity="danger"></Button>
        </div>
    </Dialog>

    <Dialog
        header="Редактирование категории"
        position="top"
        :visible="isEditRecordModal"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        @update:visible="() => isEditRecordModal = !isEditRecordModal"
        style="width: 30rem;"
    >
        <CategoryForm :category="currentRecord" @cancel="() => isEditRecordModal = false" @processed="onRecordSave"/>
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
            Вы действительно хотите удалить категорию "{{ currentRecord!.name }}"?
        </div>

        <div class="flex justify-end gap-2 mt-2">
            <Button label="Отмена" severity="secondary" @click="() => { isDeleteRecordModal = false }"></Button>
            <Button label="Удалить" @click="onConfirmDeleteClick" severity="danger"></Button>
        </div>
    </Dialog>
</template>

<style scoped>

</style>
