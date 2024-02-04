interface ImportMeta {
    env: {
        VITE_APP_NAME: string
    }
}

export function changeTitle(title: string) {
    document.title = title + ' - ' + import.meta.env.VITE_APP_NAME
}
