<script setup lang='ts'>
import BaseLayout from '@/components/layout/BaseLayout.vue'
import type {MenuItem} from 'primevue/menuitem'
import {onUpdated, ref} from 'vue'
import type {TabMenuChangeEvent} from 'primevue/tabmenu'
import TabMenu from 'primevue/tabmenu'
import Skeleton from 'primevue/skeleton'
import {useRoute, useRouter} from 'vue-router'

const router = useRouter()
const route = useRoute()
const isLoadingSection = ref(false)
const tabs = ref<MenuItem[]>([
    {label: 'Профиль', route: 'settings.profile', icon: 'fa-regular fa-user'},
    {label: 'Безопасность', route: 'settings.security', icon: 'fa-solid fa-shield-halved'},
])
const activeTabIndex = ref(getActualActiveTabIndex())

onUpdated(() => {
    activeTabIndex.value = getActualActiveTabIndex()
})

function getActualActiveTabIndex() {
    return tabs.value.findIndex(tab => tab.route === route.name)
}

function onTabChange(event: TabMenuChangeEvent) {
    isLoadingSection.value = true
    const selectedSection = tabs.value[event.index]
    router.push({name: selectedSection.route}).finally(() => {
        isLoadingSection.value = false
    })
}
</script>

<template>
    <BaseLayout>
        <div class="surface-overlay rounded-xl border">
            <p class="text-3xl font-semibold p-4">Настройки</p>
            <TabMenu class="pl-2 pr-2" :model="tabs" :active-index="activeTabIndex" @tab-change="onTabChange"/>
        </div>
        <div class="p-4 surface-overlay rounded-xl border mt-3">
            <div class="settings-section-content">
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
.settings-section-content {
    max-width: 42rem;
}
</style>
