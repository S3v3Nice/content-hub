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

export interface Post {
    id: bigint
    slug: string
    version?: PostVersion
    created_at: string
    updated_at: string
}

export enum PostVersionStatus {
    DRAFT,
    PENDING,
    ACCEPTED,
    REJECTED,
}

export interface PostVersion {
    id?: bigint
    post_id?: bigint | null
    post?: Post | null
    author_id?: bigint | null
    author?: User | null
    assigned_moderator_id?: bigint | null
    assigned_moderator?: User | null
    category_id?: bigint
    category?: PostCategory
    cover?: string
    cover_url?: string
    cover_file?: File
    title?: string
    description?: string
    content?: string
    status?: PostVersionStatus
    created_at?: string
    updated_at?: string
}

export enum PostVersionActionType {
    SUBMIT,
    REQUEST_CHANGES,
    ACCEPT,
    REJECT,
    ASSIGN_MODERATOR,
}

export interface PostVersionActionRequestChanges {
    message: string
}

export interface PostVersionActionReject {
    reason: string
}

export interface PostVersionActionAssignModerator {
    moderator_id: bigint
    moderator: User | null
}

export interface PostVersionAction {
    id?: bigint
    version_id?: bigint
    version?: PostVersion
    user_id?: bigint | null
    user?: User | null
    type?: PostVersionActionType
    details?: {} | PostVersionActionRequestChanges | PostVersionActionReject | PostVersionActionAssignModerator
    created_at?: string
    updated_at?: string
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
