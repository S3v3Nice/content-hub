<script setup lang="ts">
import {type PropType, ref} from 'vue'
import type {EditorMenuItem} from '@/components/editor/types'
import type {MenuItem} from 'primevue/menuitem'
import TieredMenu from 'primevue/tieredmenu'

defineProps({
    title: String,
    items: {
        type: Object as PropType<EditorMenuItem[]>,
        required: true
    }
})

const tieredMenu = ref<TieredMenu>()

defineExpose({
    show,
    hide,
    toggle,
})

function convertToTieredMenuItems(itemsToConvert: EditorMenuItem[]): MenuItem[] {
    return itemsToConvert.map((item) => ({
        separator: item.isSeparator,
        label: item.displayName,
        icon: item.icon,
        visible: item.isVisible,
        command: item.callback,
        items: item.children ? convertToTieredMenuItems(item.children) : undefined
    }))
}

function show(event: Event) {
    tieredMenu.value?.show(event)
}

function hide() {
    tieredMenu.value?.hide()
}

function toggle(event: Event) {
    tieredMenu.value?.toggle(event)
}
</script>

<template>
    <TieredMenu
        ref="tieredMenu"
        :model="convertToTieredMenuItems(items)"
        popup
        :pt="{root: {style: 'z-index: 100;'}}"
        @mousedown.prevent
    >
        <template #start v-if="title">
            <p class="p-2 text-muted">{{ title }}</p>
        </template>
    </TieredMenu>
</template>

<style scoped>

</style>
