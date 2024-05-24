export interface EditorMenuItem {
    isVisible?: boolean
    isSeparator?: boolean
    displayName?: string
    icon?: string
    shortcut?: string
    isActive?: boolean | ((...args: any) => boolean)
    callback?: ((...args: any) => void)
    children?: EditorMenuItem[]
}
