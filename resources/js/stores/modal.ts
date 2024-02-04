import {defineStore} from 'pinia'

export const useModalStore = defineStore('modal', {
    state: () => ({
        isLogin: false,
        isForgotPassword: false,
        isRegister: false,
    }),
    actions: {
        setLoginVisible(isVisible: boolean = true) {
            if (isVisible) {
                this.isForgotPassword = false
                this.isRegister = false
            }
            this.isLogin = isVisible
        },

        setForgotPasswordVisible(isVisible: boolean = true) {
            if (isVisible) {
                this.isLogin = false
                this.isRegister = false
            }
            this.isForgotPassword = isVisible
        },

        setRegisterVisible(isVisible: boolean = true) {
            if (isVisible) {
                this.isLogin = false
                this.isForgotPassword = false
            }
            this.isRegister = isVisible
        },
    }
})
