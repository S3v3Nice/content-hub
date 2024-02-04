<script setup lang='ts'>
import {useAuthStore} from '@/stores/auth'
import {computed, nextTick, ref} from 'vue'
import axios from 'axios'
import useThemeManager from '@/theme-manager'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import Avatar from 'primevue/avatar'
import Menu from 'primevue/menu'
import InputSwitch from 'primevue/inputswitch'
import LoginForm from '@/components/auth/LoginForm.vue'
import RegisterForm from '@/components/auth/RegisterForm.vue'
import {useRouter} from 'vue-router'
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'
import type {MenuItem} from 'primevue/menuitem'
import {useModalStore} from '@/stores/modal'

const router = useRouter()
const authStore = useAuthStore()
const modalStore = useModalStore()
const themeManager = ref(useThemeManager())
const isLightTheme = computed(() => themeManager.value.isLight())
const userMenu = ref<Menu>()
const userMenuItems = computed<MenuItem[]>(() => [
    {
        visible: authStore.isAuthenticated,
        separator: true,
    },
    {
        label: 'Настройки',
        icon: 'fa-solid fa-gear',
        visible: authStore.isAuthenticated,
        command: () => router.push({name: 'settings'}),
    },
    {
        visible: authStore.isAuthenticated,
        separator: true,
    },
    {
        label: 'Тёмная тема',
        icon: 'fa-regular fa-moon',
        switchValue: !themeManager.value.isLight(),
        command: () => themeManager.value.toggleTheme(),
    },
    {
        separator: true,
    },
    {
        label: 'Войти',
        icon: 'fa-solid fa-arrow-right-to-bracket',
        visible: !authStore.isAuthenticated,
        command: () => modalStore.setLoginVisible(),
    },
    {
        label: 'Регистрация',
        icon: 'fa-solid fa-user-plus',
        visible: !authStore.isAuthenticated,
        command: () => modalStore.setRegisterVisible(),
    },
    {
        label: 'Выйти',
        icon: 'fa-solid fa-arrow-right-from-bracket',
        visible: authStore.isAuthenticated,
        command: logout,
    },
])

function toggleUserMenu(event: Event) {
    userMenu.value!.toggle(event)
}

function invokeUserMenuCommand(item: MenuItem) {
    if (item.command) {
        item.command(null!)
    }

    const shouldHideMenu = item.switchValue === undefined

    if (shouldHideMenu) {
        userMenu.value!.hide()
    }
}

function logout() {
    axios.post('/logout').then(() => {
        router.go(0)
    })
}
</script>

<template>
    <div class="surface-overlay pb-2 pt-2 border-b" style="height: 3.5rem; max-width: 100%">
        <div class="container-md flex space-x-4 justify-between" style="height: 100%">
            <RouterLink :to="{name: 'home'}">
                <img v-if="isLightTheme" src="/images/logo.svg" alt="Logo" style="height: 100%">
                <img v-else src="/images/logo-dark.svg" alt="Logo" style="height: 100%">
            </RouterLink>
            <Button
                v-if="!authStore.isAuthenticated"
                icon="fa-regular fa-user"
                @click="toggleUserMenu"
                aria-haspopup="true"
                aria-controls="user-menu"
                aria-label="Меню пользователя"
            />
            <Button
                v-else
                unstyled
                @click="toggleUserMenu"
                aria-haspopup="true"
                aria-controls="user-menu"
                aria-label="Меню пользователя"
            >
                <Avatar :label="authStore.username![0]" shape="circle"/>
            </Button>
        </div>
    </div>

    <Menu
        ref="userMenu"
        id="user-menu"
        :model="userMenuItems"
        :popup="true"
        @focus="() => nextTick(() => (userMenu!['focusedOptionIndex'] = -1))"
    >
        <template v-if="authStore.isAuthenticated" #start>
      <span class="flex p-2 gap-2 items-center">
        <Avatar size="large" :label="authStore.username![0]" shape="circle"/>
        <span>{{ authStore.username }}</span>
      </span>
        </template>
        <template #item="{ item, props }">
            <a class="flex space-x-5 justify-between" v-bind="props.action" @click.stop="invokeUserMenuCommand(item)">
                <div>
                    <span :class="item.icon" style="width: 20px;"/>
                    <span class="ml-2">{{ item.label }}</span>
                </div>
                <InputSwitch v-if="item.switchValue != undefined" :model-value="item.switchValue"></InputSwitch>
            </a>
        </template>
    </Menu>

    <Dialog
        header="Вход"
        position="top"
        :visible="modalStore.isLogin"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        @update:visible="modalStore.setLoginVisible"
        style="width: 30rem;"
    >
        <LoginForm
            @switch-to-forgot-password="modalStore.setForgotPasswordVisible(true)"
            @switch-to-register="modalStore.setRegisterVisible(true)"
        />
    </Dialog>

    <Dialog
        header="Сброс пароля"
        position="top"
        :visible="modalStore.isForgotPassword"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        @update:visible="modalStore.setForgotPasswordVisible"
        style="width: 30rem;"
    >
        <template #header>
            <div class="inline-flex items-center">
                <Button
                    icon="fa-solid fa-arrow-left"
                    aria-label="Назад"
                    class="p-dialog-header-icon"
                    @click="modalStore.setLoginVisible()"
                />
                <span class="p-dialog-title">Сброс пароля</span>
            </div>
        </template>
        <ForgotPasswordForm/>
    </Dialog>

    <Dialog
        header="Регистрация"
        position="top"
        :visible="modalStore.isRegister"
        :modal="true"
        :draggable="false"
        :dismissable-mask="true"
        @update:visible="modalStore.setRegisterVisible"
        style="width: 30rem;"
    >
        <RegisterForm @switch-to-login="modalStore.setLoginVisible()"/>
    </Dialog>
</template>

<style scoped>

</style>
