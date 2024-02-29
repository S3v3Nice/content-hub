import axios from 'axios'
import {defineStore} from 'pinia'
import {type User, UserRole} from '@/types/user'

interface AuthUser {
    isAuthenticated: boolean
    user: User | null
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthUser => ({
        isAuthenticated: false,
        user: null
    }),
    getters: {
        id: (state) => state.user?.id,
        username: (state) => state.user?.username,
        email: (state) => state.user?.email,
        emailVerifiedAt: (state) => state.user?.email_verified_at,
        firstName: (state) => state.user?.first_name,
        lastName: (state) => state.user?.last_name,
        role: (state) => state.user?.role,
        createdAt: (state) => state.user?.created_at,
        updatedAt: (state) => state.user?.updated_at,
        hasVerifiedEmail: (state) => state.user?.email_verified_at !== null,
        isAdmin: (state) => state.user?.role === UserRole.ADMIN,
        isModerator: (state) => state.user?.role === UserRole.MODERATOR || state.user?.role === UserRole.ADMIN,
    },
    actions: {
        async fetchUser() {
            await axios.get('/api/auth/user').then(({data}) => {
                if (Object.keys(data).length === 0) {
                    this.reset()
                    return
                }

                this.isAuthenticated = true
                this.user = {
                    id: data.id,
                    username: data.username,
                    email: data.email,
                    email_verified_at: data.email_verified_at,
                    first_name: data.first_name,
                    last_name: data.last_name,
                    role: data.role,
                    created_at: data.created_at,
                    updated_at: data.updated_at
                }
            }).catch(() => {
                this.reset()
            })
        },

        reset() {
            this.isAuthenticated = false
            this.user = null
        }
    }
})
