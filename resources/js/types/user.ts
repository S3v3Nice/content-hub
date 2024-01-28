export interface User {
    id: bigint
    username: string
    email: string
    firstName?: string
    lastName?: string
    isAdmin: boolean
    createdAt: string
    updatedAt: string
}
