<script setup lang="ts">
import Button from 'primevue/button'
import type {TooltipOptions} from 'primevue/tooltip'
import {onUnmounted, type PropType, reactive} from 'vue'
import EditorVerticalMenu from '@/components/post/editor/EditorVerticalMenu.vue'
import type {EditorMenuItem} from '@/types'

defineProps({
    items: {
        type: Object as PropType<EditorMenuItem[]>,
        required: true
    }
})

const verticalMenus = reactive<{ [key: string]: typeof EditorVerticalMenu }>({})

document.addEventListener('scroll', onScroll)

onUnmounted(() => {
    document.removeEventListener('scroll', onScroll)
})

function onScroll() {
    for (const menuKey in verticalMenus) {
        verticalMenus[menuKey]?.hide()
    }
}

function getItemTooltip(item: EditorMenuItem): TooltipOptions {
    return {
        value: `${item.displayName}${item.shortcut ? `<p class="text-gray-400">${item.shortcut}</p>` : ''}`,
        escape: false,
        pt: {
            text: {
                style: {
                    padding: '0.3rem 0.4rem',
                    fontSize: '0.8rem',
                    textAlign: 'center',
                    lineHeight: '1.2em'
                }
            }
        }
    }
}
</script>

<template>
    <div class="bg-[var(--surface-overlay)] h-[2.5rem] overflow-x-auto flex" @mousedown.prevent>
        <template v-for="(menuItem, id) in items">
            <template v-if="menuItem.isVisible !== false">
                <template v-if="menuItem.isSeparator">
                    <div class="w-[1px] bg-[var(--surface-border)] inline-block ml-1 mr-1 flex-shrink-0"/>
                </template>

                <template v-else-if="menuItem.children">
                    <EditorVerticalMenu
                        :ref="(val: typeof EditorVerticalMenu) => verticalMenus[`editor-vertical-menu-${id}`] = val"
                        :id="`editor-vertical-menu-${id}`"
                        :items="menuItem.children"
                        :title="menuItem.displayName"
                    />

                    <Button
                        text
                        v-if="verticalMenus[`editor-vertical-menu-${id}`]"
                        :severity="menuItem.isActive ? 'primary' : 'secondary'"
                        v-tooltip.top="getItemTooltip(menuItem)"
                        aria-haspopup="true"
                        :aria-controls="`editor-vertical-menu-${id}`"
                        class="flex-shrink-0"
                        @click="verticalMenus[`editor-vertical-menu-${id}`].toggle"
                    >
                        <span :class="menuItem.icon"/>
                        <span class="ml-3 fa-solid fa-angle-down"/>
                    </Button>
                </template>

                <template v-else>
                    <Button
                        text
                        :icon="menuItem.icon"
                        :severity="menuItem.isActive ? 'primary' : 'secondary'"
                        v-tooltip.top="getItemTooltip(menuItem)"
                        class="flex-shrink-0"
                        @click="menuItem.callback"
                    />
                </template>
            </template>
        </template>
    </div>
</template>

<style scoped>

</style>
