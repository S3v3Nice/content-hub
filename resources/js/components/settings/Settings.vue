<script setup lang='ts'>
import type {MenuItem} from 'primevue/menuitem'
import {ref, watch} from 'vue'
import TabMenu from 'primevue/tabmenu'
import {useRoute} from 'vue-router'

const route = useRoute()
const tabs = ref<MenuItem[]>([
    {label: 'Профиль', route: 'settings.profile', icon: 'fa-regular fa-user'},
    {label: 'Безопасность', route: 'settings.security', icon: 'fa-solid fa-shield-halved', isWarningBadge: true},
])
const activeTabIndex = ref(getActualActiveTabIndex())

watch(route, () => {
    activeTabIndex.value = getActualActiveTabIndex()
})

function getActualActiveTabIndex() {
    return tabs.value.findIndex(tab => tab.route === route.name)
}
</script>

<template>
    <div class="surface-overlay rounded-xl border">
        <p class="text-3xl font-semibold p-4">Настройки</p>
        <TabMenu class="pl-2 pr-2" :model="tabs" :active-index="activeTabIndex">
            <template #item="{ item, props }">

                <RouterLink v-bind="props.action" :to="{ name: item.route }" class="flex align-items-center gap-2">
                    <span class="p-menuitem-icon" :class="item.icon"/>
                    <span class="p-menuitem-text text">
                        {{ item.label }}
                    </span>
                </RouterLink>
            </template>
        </TabMenu>
    </div>
    <div class="p-4 surface-overlay rounded-xl border mt-3">
        <div class="settings-section-content">
            <RouterView/>
        </div>
    </div>
</template>

<style scoped>
.settings-section-content {
    max-width: 42rem;
}
</style>
