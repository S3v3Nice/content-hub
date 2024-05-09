export enum UserRole {
    USER,
    MODERATOR,
    ADMIN,
}

export interface User {
    id?: bigint
    username?: string
    email?: string
    email_verified_at?: string | null
    first_name?: string
    last_name?: string
    role?: UserRole
    created_at?: string
    updated_at?: string
}

export interface PostCategory {
    id?: bigint
    slug?: string
    name?: string
}

export interface PostVersion {
    id?: bigint
    post_id?: bigint | null
    submitter_id?: bigint | null
    assigned_moderator_id?: bigint | null
    category_id?: bigint | null
    cover?: string
    cover_file?: File
    title?: string
    description?: string
    content?: string
}

export interface EditorNodeInfo {
    name: string
    attributes?: object
    displayName: string
    shortcut?: string
    icon: string
}

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
