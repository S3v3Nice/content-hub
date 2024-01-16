export interface User {
    id: bigint
    email: string
    firstName?: string
    lastName?: string
    isAdmin: boolean
    createdAt: string
    updatedAt: string
}
