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
    like_count: number
    comment_count: number
    view_count: number
    is_liked: boolean
    created_at: string
    updated_at: string
}

export interface PostComment {
    id: bigint
    parent_comment_id?: bigint | null
    parent_comment?: PostComment | null
    post_id: bigint
    post?: Post
    user_id: bigint | null
    user?: User | null
    content: string
    like_count: number
    is_liked: boolean
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
    actions?: PostVersionAction[]
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
