<script setup lang="ts">
import {changeTitle} from '@/helpers'
import {computed, ref, watch} from 'vue'
import {useRoute} from 'vue-router'
import Posts from '@/components/post/Posts.vue'

const route = useRoute()
const posts = ref<InstanceType<typeof Posts>>()

const searchTerm = computed(() => route.query.term)
const totalRecordsCount = computed(() => posts.value?.getTotalRecordsCount() ?? 0)

watch(route, () => {
    updateTitle()
})

updateTitle()

function updateTitle() {
    changeTitle(`Поиск «${searchTerm.value as string | undefined ?? ''}»`)
}
</script>

<template>
    <Posts ref="posts" :additional-load-data="route.query">
        <template v-slot:title>
            <p class="text-xl">Результаты поиска</p>
            <p class="text-sm text-muted">
                Всего {{ totalRecordsCount }} по запросу «{{ (searchTerm as string | undefined)?.trim() ?? '' }}».
            </p>
        </template>
    </Posts>
</template>

<style scoped>
</style>
