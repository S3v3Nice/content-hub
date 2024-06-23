<script setup lang="ts">
import Avatar from 'primevue/avatar'
import {computed, type PropType} from 'vue'
import type {User} from '@/types'

const props = defineProps({
    user: {
        type: Object as PropType<User|null>,
        required: true
    },
    size: {
        type: String,
        validator: (val: string) => ['large', 'xlarge', 'normal'].includes(val)
    },
})

const backgroundColors = [
    '#FF6F61', '#6B5B95', '#88B04B', '#92A8D1',
    '#955251', '#6A5ACD', '#009B77', '#4682B4',
    '#D65076', '#45B8AC', '#5B5EA6', '#FF8D1A',
    '#DC143C', '#C3447A', '#E15D44', '#50B6C4',
    '#F4A460', '#E9967A', '#DA70D6', '#FA8072',
    '#778899', '#BC243C', '#4682B4', '#D2691E',
    '#CD5C5C', '#8FBC8F', '#FF4500', '#2E8B57',
    '#1E90FF', '#FF6347', '#8A2BE2', '#FF00FF',
    '#B565A7', '#32CD32', '#FF1493'
]

function pseudoRandom(seed: string): number {
    let hash = 0
    for (let i = 0; i < seed.length; i++) {
        hash = seed.charCodeAt(i) + ((hash << 5) - hash)
    }
    return Math.abs(hash)
}

const backgroundColor = computed(() => {
    if (!props.user) {
        return '#92A8D1'
    }

    const index = pseudoRandom(props.user.username!.toLowerCase()) % backgroundColors.length
    return backgroundColors[index]
})
</script>

<template>
    <Avatar
        :label="user?.username![0] ?? '?'"
        shape="circle"
        :size="size"
        :style="{ backgroundColor: backgroundColor, color: '#FFFFFF' }"
    />
</template>

<style scoped>
</style>
