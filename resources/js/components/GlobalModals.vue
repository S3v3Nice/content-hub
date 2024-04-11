<script setup lang="ts">
import Dialog from 'primevue/dialog'
import LoginForm from '@/components/auth/LoginForm.vue'
import {useModalStore} from '@/stores/modal'
import {computed, ref, watch} from 'vue'
import Button from 'primevue/button'
import RegisterForm from '@/components/auth/RegisterForm.vue'
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'
import {storeToRefs} from 'pinia'

enum AuthFormType {
    LOGIN,
    REGISTER,
    FORGOT_PASSWORD
}

const modalStore = useModalStore()
const {auth: isAuthModal} = storeToRefs(modalStore)
const authFormType = ref(AuthFormType.LOGIN)

const authDialogTitle = computed(() => {
    switch (authFormType.value) {
        case AuthFormType.LOGIN:
            return 'Вход'
        case AuthFormType.REGISTER:
            return 'Регистрация'
        case AuthFormType.FORGOT_PASSWORD:
            return 'Сброс пароля'
    }
})

watch(isAuthModal, (showAuthModal: boolean) => {
    if (!showAuthModal) {
        authFormType.value = AuthFormType.LOGIN
    }
})
</script>

<template>
    <Dialog
        v-model:visible="isAuthModal"
        position="top"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        style="width: 30rem;"
    >
        <template #header>
            <div class="inline-flex items-center">
                <Button
                    v-if="authFormType !== AuthFormType.LOGIN"
                    icon="fa-solid fa-arrow-left"
                    aria-label="Назад (к входу)"
                    class="p-dialog-header-icon"
                    @click="authFormType = AuthFormType.LOGIN"
                />
                <span class="p-dialog-title">{{ authDialogTitle }}</span>
            </div>
        </template>

        <LoginForm
            v-if="authFormType === AuthFormType.LOGIN"
            @switch-to-forgot-password="authFormType = AuthFormType.FORGOT_PASSWORD"
            @switch-to-register="authFormType = AuthFormType.REGISTER"
        />
        <RegisterForm
            v-else-if="authFormType === AuthFormType.REGISTER"
            @switch-to-login="authFormType = AuthFormType.LOGIN"
        />
        <ForgotPasswordForm
            v-else-if="authFormType === AuthFormType.FORGOT_PASSWORD"
        />
    </Dialog>
</template>

<style scoped>

</style>
