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
