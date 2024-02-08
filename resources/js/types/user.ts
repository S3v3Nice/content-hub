export interface User {
    id: bigint
    username: string
    email: string
    emailVerifiedAt: string | null
    firstName?: string
    lastName?: string
    role: number
    createdAt: string
    updatedAt: string
}
