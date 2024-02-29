<script setup lang='ts'>
import BaseLayout from '@/components/layout/BaseLayout.vue'
import {computed, onUpdated, ref} from 'vue'
import Skeleton from 'primevue/skeleton'
import {useRoute} from 'vue-router'
import Button from 'primevue/button'
import {useAuthStore} from '@/stores/auth'

const authStore = useAuthStore()
const route = useRoute()
const isLoadingSection = ref(false)
const isMobileMenuShown = ref(false)

interface DashboardMenuItem {
    label: string
    icon: string
    route?: string
    visible?: boolean | ((...args: any) => boolean)
}

const menuItems = ref<DashboardMenuItem[]>([
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
const currentMenuItem = ref<DashboardMenuItem>(getActualCurrentMenuItem())

onUpdated(() => {
    isLoadingSection.value = false
    currentMenuItem.value = getActualCurrentMenuItem()
})

function getActualCurrentMenuItem() {
    return menuItems.value.find(item => item.route === route.name)!
}

function onSectionSelect(item: DashboardMenuItem) {
    isMobileMenuShown.value = false

    if (item !== currentMenuItem.value) {
        currentMenuItem.value = item
        isLoadingSection.value = true
    }
}

const visibleMenuItems = computed<DashboardMenuItem[]>(() => {
    return menuItems.value.filter(
        (item) => typeof item.visible === 'function' ? item.visible() : item.visible !== false
    )
})
</script>

<template>
    <BaseLayout>
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
                <p class="text-xs font-semibold" style="color: var(--text-color-secondary)">Панель управления</p>
                <p class="text-xl font-semibold">{{ currentMenuItem.label }}</p>
            </div>
        </div>

        <div class="flex gap-4 items-start">
            <div class="lg:block dashboard-menu-width surface-overlay rounded-xl border"
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
                        <Button class="w-full gap-2" plain :text="item !== currentMenuItem">
                            <span class="menu-item-icon" :class="item.icon"/>
                            <p>{{ item.label }}</p>
                        </Button>
                    </Component>
                </div>
            </div>

            <div class="lg:block p-4 surface-overlay rounded-xl border flex-1" style="overflow: auto;"
                 :class="{ 'hidden': isMobileMenuShown, 'block': !isMobileMenuShown }"
            >
                <RouterView v-if="!isLoadingSection"/>
                <div v-else>
                    <Skeleton width="9rem" height="1.5rem" class="mb-1"></Skeleton>
                    <Skeleton height="3rem" class="mb-7"></Skeleton>
                    <Skeleton width="9rem" height="1.5rem" class="mb-1"></Skeleton>
                    <Skeleton height="3rem" class="mb-7"></Skeleton>
                    <Skeleton width="11rem" height="3rem"></Skeleton>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>

<style scoped>
.dashboard-menu-width {
    width: 100%;

    @screen lg {
        width: 18rem;
    }
}

.menu-item-icon {
    width: 20px;
}
</style>
