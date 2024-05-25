export interface EditorMenuItem {
    isVisible?: boolean
    isSeparator?: boolean
    displayName?: string
    icon?: string
    shortcut?: string
    isActive?: boolean | ((...args: any) => boolean)
    callback?: (event: Event) => void
    children?: EditorMenuItem[]
}
