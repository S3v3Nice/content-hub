<script setup lang='ts'>
import {useAuthStore} from '@/stores/auth'
import {computed, nextTick, onMounted, ref} from 'vue'
import axios from 'axios'
import useThemeManager from '@/theme-manager'
import Button from 'primevue/button'
import UserAvatar from '@/components/user/UserAvatar.vue'
import Menu from 'primevue/menu'
import OverlayPanel from 'primevue/overlaypanel'
import Sidebar from 'primevue/sidebar'
import InputSwitch from 'primevue/inputswitch'
import {useRouter} from 'vue-router'
import type {MenuItem} from 'primevue/menuitem'
import {useModalStore} from '@/stores/modal'
import InputText from 'primevue/inputtext'
import InputGroup from 'primevue/inputgroup'
import {usePostCategoryStore} from '@/stores/postCategory'

const router = useRouter()
const authStore = useAuthStore()
const categoryStore = usePostCategoryStore()
const modalStore = useModalStore()
const themeManager = useThemeManager()

const isNavigationSidebarVisible = ref(false)
const userMenu = ref<Menu>()
const userMenuItems = computed<MenuItem[]>(() => [
    {
        visible: authStore.isAuthenticated,
        separator: true,
    },
    {
        label: 'Панель управления',
        icon: 'fa-solid fa-hammer',
        visible: authStore.isAdmin || authStore.isModerator,
        route: 'dashboard',
    },
    {
        label: 'Контент-студия',
        icon: 'fa-solid fa-edit',
        visible: authStore.isAuthenticated,
        route: 'studio',
    },
    {
        label: 'Создать материал',
        icon: 'fa-solid fa-plus',
        visible: authStore.isAuthenticated,
        route: 'create-post',
    },
    {
        label: 'Настройки',
        icon: 'fa-solid fa-gear',
        visible: authStore.isAuthenticated,
        route: 'settings',
    },
    {
        visible: authStore.isAuthenticated,
        separator: true,
    },
    {
        label: 'Тёмная тема',
        icon: 'fa-regular fa-moon',
        switchValue: !themeManager.isLight(),
        command: () => themeManager.toggleTheme(),
    },
    {
        separator: true,
    },
    {
        label: 'Войти',
        icon: 'fa-solid fa-arrow-right-to-bracket',
        visible: !authStore.isAuthenticated,
        command: () => modalStore.auth = true,
    },
    {
        label: 'Выйти',
        icon: 'fa-solid fa-arrow-right-from-bracket',
        visible: authStore.isAuthenticated,
        command: logout,
    },
])

const searchOverlayPanel = ref<OverlayPanel>()
const isMobileSearchPanel = ref(false)
const searchTerm = ref('')

onMounted(() => {
    document.addEventListener('scroll', onScroll)
})

function onScroll() {
    userMenu.value?.hide()
    if (searchOverlayPanel.value?.['visible']) {
        searchOverlayPanel.value?.alignOverlay()
    }
}

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
    axios.post('/api/auth/logout').then(() => {
        router.go(0)
    })
}

function onSearch() {
    router.push({name: 'post-search', query: {term: searchTerm.value}, force: true})
    isMobileSearchPanel.value = false
}
</script>

<template>
    <div class="header-fixed flex flex-col">
        <div class="h-[var(--header-height)] surface-overlay p-2 lg:px-0 border-b flex items-center">
            <div class="page-container flex gap-2 h-full items-center">
                <Button
                    icon="fa-solid fa-bars"
                    text
                    severity="secondary"
                    aria-haspopup="true"
                    aria-controls="user-menu"
                    aria-label="Меню навигации"
                    @click="isNavigationSidebarVisible = true"
                    class="-ml-0.5 md:-ml-2.5"
                />

                <RouterLink :to="{name: 'home'}" class="h-[70%] sm:h-[90%]">
                    <img v-if="themeManager.isLight()" src="/images/logo.svg" alt="Logo" class="h-full">
                    <img v-else src="/images/logo-dark.svg" alt="Logo" class="h-full">
                </RouterLink>

                <div class="flex ml-auto gap-4 items-center">
                    <form @submit.prevent="onSearch" class="hidden sm:block">
                        <InputGroup>
                            <InputText v-model="searchTerm" placeholder="Поиск" autocomplete="off"/>
                            <Button title="Найти" icon="fa-solid fa-search" outlined type="submit"/>
                        </InputGroup>
                    </form>

                    <Button
                        :title="isMobileSearchPanel ? `Скрыть панель поиска` : `Показать панель поиска`"
                        icon="fa-solid fa-search"
                        :text="!isMobileSearchPanel"
                        severity="secondary"
                        class="block sm:hidden"
                        @click="isMobileSearchPanel = !isMobileSearchPanel"
                    />

                    <Button
                        v-if="!authStore.isAuthenticated"
                        icon="fa-regular fa-user"
                        @click="toggleUserMenu"
                        aria-haspopup="true"
                        aria-controls="user-menu"
                        aria-label="Меню пользователя"
                        class="mr-2 md:mr-0"
                    />
                    <Button
                        v-else
                        unstyled
                        @click="toggleUserMenu"
                        aria-haspopup="true"
                        aria-controls="user-menu"
                        aria-label="Меню пользователя"
                        class="mr-2 md:mr-0"
                    >
                        <UserAvatar :user="authStore.user"/>
                    </Button>
                </div>
            </div>

            <Sidebar v-model:visible="isNavigationSidebarVisible" header="Навигация">
                <RouterLink
                    v-for="category in categoryStore.categories"
                    :to="{name: 'post-category', params: {slug: category.slug}}"
                    class="flex flex-col"
                >
                    <Button severity="secondary" text @click="isNavigationSidebarVisible = false">
                        {{ category.name }}
                    </Button>
                </RouterLink>
            </Sidebar>

            <Menu
                ref="userMenu"
                id="user-menu"
                :model="userMenuItems"
                :popup="true"
                @focus="() => nextTick(() => (userMenu!['focusedOptionIndex'] = -1))"
            >
                <template v-if="authStore.isAuthenticated" #start>
            <span class="flex p-2 gap-2 items-center">
                <UserAvatar :user="authStore.user" size="large"/>
                <span>{{ authStore.username }}</span>
            </span>
                </template>
                <template #item="{ item, props }">
                    <Component
                        :is="item.route ? 'RouterLink' : 'a'"
                        :to="{ name: item.route }"
                        class="flex space-x-5 justify-between"
                        v-bind="props.action"
                        @click.stop="invokeUserMenuCommand(item)"
                    >
                        <div>
                            <span class="menu-item-icon" :class="item.icon"/>
                            <span class="ml-2">{{ item.label }}</span>
                        </div>
                        <InputSwitch v-if="item.switchValue != undefined" :model-value="item.switchValue"></InputSwitch>
                    </Component>
                </template>
            </Menu>
        </div>

        <form v-if="isMobileSearchPanel" class="block sm:hidden bg-[var(--surface-50)] border-b p-2"
              @submit.prevent="onSearch">
            <InputGroup>
                <InputText v-model="searchTerm" placeholder="Поиск" autocomplete="off"/>
                <Button title="Найти" icon="fa-solid fa-search" outlined type="submit"/>
            </InputGroup>
        </form>
    </div>
</template>

<style scoped>
.header-fixed {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
}

.menu-item-icon {
    width: 20px;
}
</style>
