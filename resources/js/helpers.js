export function changeTitle(title) {
    document.title = title + ' - ' + import.meta.env.VITE_APP_NAME
}