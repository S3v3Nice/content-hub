import axios from 'axios'
import {defineStore} from 'pinia'
import type {User} from "@/types/user";

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
        email: (state) => state.user?.email,
        firstName: (state) => state.user?.firstName,
        lastName: (state) => state.user?.lastName,
        isAdmin: (state) => state.user?.isAdmin,
        createdAt: (state) => state.user?.createdAt,
        updatedAt: (state) => state.user?.updatedAt,
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
                    email: data.id,
                    firstName: data.first_name,
                    lastName: data.last_name,
                    isAdmin: data.is_admin,
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
