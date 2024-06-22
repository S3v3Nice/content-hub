<script setup lang="ts">
import {computed, ref, watch} from 'vue'
import {useRoute} from 'vue-router'
import Button from 'primevue/button'
import {useAuthStore} from '@/stores/auth'

const authStore = useAuthStore()
const route = useRoute()
const isMobileMenuShown = ref(false)

interface DashboardMenuItem {
    label: string
    icon: string
    route?: string
    visible?: boolean | ((...args: any) => boolean)
}

const menuItems = ref<DashboardMenuItem[]>([
    {
        label: 'Заявки на публикацию',
        icon: 'fa-solid fa-file-alt',
        route: 'dashboard.post-submissions',
    },
    {
        label: 'Пользователи',
        icon: 'fa-solid fa-user',
        route: 'dashboard.users',
    },
    {
        label: 'Категории',
        icon: 'fa-solid fa-list',
        route: 'dashboard.categories',
        visible: authStore.isAdmin
    },
])
const activeMenuItem = ref<DashboardMenuItem>(getActualActiveMenuItem())

watch(route, () => {
    activeMenuItem.value = getActualActiveMenuItem()
})

function getActualActiveMenuItem() {
    return menuItems.value.find(item => item.route === route.name)!
}

function onSectionSelect(item: DashboardMenuItem) {
    isMobileMenuShown.value = false
    activeMenuItem.value = item
}

const visibleMenuItems = computed<DashboardMenuItem[]>(() => {
    return menuItems.value.filter(
        (item) => typeof item.visible === 'function' ? item.visible() : item.visible !== false
    )
})
</script>

<template>
    <div v-if="!isMobileMenuShown"
         class="lg:hidden surface-overlay rounded-xl border flex p-2 items-center mb-2">
        <Button
            icon="fa-solid fa-arrow-left"
            text
            severity="secondary"
            aria-label="Назад"
            @click="() => isMobileMenuShown = true"
        />
        <div>
            <p class="text-xs text-muted font-semibold">Панель управления</p>
            <p class="text-xl font-semibold">{{ activeMenuItem.label }}</p>
        </div>
    </div>

    <div class="flex gap-4 items-start">
        <div class="lg:block w-full lg:w-[18rem] surface-overlay rounded-xl border"
             :class="{ 'hidden': !isMobileMenuShown, 'block': isMobileMenuShown }"
        >
            <p class="text-xl font-semibold p-3">Панель управления</p>

            <div class="pl-2 pr-2 pb-2">
                <Component
                    v-for="(item) of visibleMenuItems"
                    :is="item.route ? 'RouterLink' : 'a'"
                    :to="{ name: item.route }"
                    @click="onSectionSelect(item)"
                >
                    <Button class="w-full gap-2" plain :text="item !== activeMenuItem">
                        <span class="w-[20px]" :class="item.icon"/>
                        <p>{{ item.label }}</p>
                    </Button>
                </Component>
            </div>
        </div>

        <div class="lg:block p-4 surface-overlay rounded-xl border flex-1 overflow-x-auto"
             :class="{ 'hidden': isMobileMenuShown, 'block': !isMobileMenuShown }"
        >
            <RouterView/>
        </div>
    </div>
</template>

<style scoped>

</style>
