import type {ToastServiceMethods} from 'primevue/toastservice'
import {PostVersionStatus} from '@/types'

interface ImportMeta {
    env: {
        VITE_APP_NAME: string
        VITE_APP_URL: string
    }
}

export function changeTitle(title: string) {
    document.title = title + ' - ' + import.meta.env.VITE_APP_NAME
}

export function getErrorMessageByCode(code: number) {
    switch (code) {
        case 403:
            return 'У вас нет доступа для совершения данной операции.'
        case 429:
            return 'Слишком много запросов. Повторите позже.'
        default:
            return ''
    }
}

export function getPostVersionStatusInfo(status: PostVersionStatus) {
    switch (status) {
        case PostVersionStatus.DRAFT:
            return {
                name: 'Черновик',
                severity: 'secondary'
            }
        case PostVersionStatus.PENDING:
            return {
                name: 'На рассмотрении',
                severity: 'info'
            }
        case PostVersionStatus.ACCEPTED:
            return {
                name: 'Принято',
                severity: 'success'
            }
        case PostVersionStatus.REJECTED:
            return {
                name: 'Отклонено',
                severity: 'danger'
            }
    }
}

export class ToastHelper {
    private static readonly lifeTime = 3000

    private toast: ToastServiceMethods

    constructor(toast: ToastServiceMethods) {
        this.toast = toast
    }

    public success(message?: string) {
        this.toast.add({severity: 'success', summary: 'Успех', detail: message || '', life: ToastHelper.lifeTime})
    }

    public error(message?: string) {
        this.toast.add({severity: 'error', summary: 'Ошибка', detail: message || '', life: ToastHelper.lifeTime})
    }
}
