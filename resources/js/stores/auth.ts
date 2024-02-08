import axios from 'axios'
import {defineStore} from 'pinia'
import type {User} from '@/types/user'

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
        emailVerifiedAt: (state) => state.user?.emailVerifiedAt,
        firstName: (state) => state.user?.firstName,
        lastName: (state) => state.user?.lastName,
        role: (state) => state.user?.role,
        createdAt: (state) => state.user?.createdAt,
        updatedAt: (state) => state.user?.updatedAt,
        hasVerifiedEmail: (state) => state.user?.emailVerifiedAt !== null,
    },
    actions: {
        async fetchUser() {
            await axios.get('/api/user').then(({data}) => {
                if (Object.keys(data).length === 0) {
                    this.reset()
                    return
                }

                this.isAuthenticated = true
                this.user = {
                    id: data.id,
                    username: data.username,
                    email: data.email,
                    emailVerifiedAt: data.email_verified_at,
                    firstName: data.first_name,
                    lastName: data.last_name,
                    role: data.role,
                    createdAt: data.created_at,
                    updatedAt: data.updated_at
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
