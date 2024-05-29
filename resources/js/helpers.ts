import type {ToastServiceMethods} from 'primevue/toastservice'
import {PostVersionStatus} from '@/types'

interface ImportMeta {
    env: {
        VITE_APP_NAME: string
        VITE_APP_URL: string
    }
}

export function getAppUrl(): string {
    return import.meta.env.VITE_APP_URL
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

export function convertDateToString(date: Date) {
    return date.toISOString()
}

export function getFullDate(date: Date | string) {
    if (typeof date === 'string') {
        date = new Date(date)
    }
    return date.toLocaleString()
}

export function getRelativeDate(date: Date | string): string {
    const now = new Date()
    if (typeof date === 'string') {
        date = new Date(date)
    }
    const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000)

    if (diffInSeconds < 60) {
        return 'только что'
    }

    const diffInMinutes = Math.floor(diffInSeconds / 60)
    if (diffInMinutes < 60) {
        return `${diffInMinutes} мин. назад`
    }

    const diffInHours = Math.floor(diffInMinutes / 60)
    if (diffInHours < 24) {
        return `${diffInHours} ч. назад`
    }

    const diffInDays = Math.floor(diffInHours / 24)
    if (diffInDays < 7) {
        return `${diffInDays} дн. назад`
    }

    const diffInWeeks = Math.floor(diffInDays / 7)
    if (diffInWeeks < 4) {
        return `${diffInWeeks} нед. назад`
    }

    const diffInMonths = Math.floor(diffInDays / 30)
    if (diffInMonths < 12) {
        return `${diffInMonths} мес. назад`
    }

    const diffInYears = Math.floor(diffInDays / 365)
    return `${diffInYears} г. назад`
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
