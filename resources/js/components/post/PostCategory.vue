<script setup lang="ts">
import {changeTitle} from '@/helpers'
import {computed, watch} from 'vue'
import {useRoute} from 'vue-router'
import {usePostCategoryStore} from '@/stores/postCategory'
import Posts from '@/components/post/Posts.vue'

const props = defineProps({
    slug: {
        type: String,
        required: true
    }
})

const postCategoryStore = usePostCategoryStore()
const route = useRoute()

const postCategory = computed(() => postCategoryStore.getBySlug(props.slug))
const categoryName = computed(() => postCategory.value?.name ?? `Категория ${props.slug}`)
const loadData = computed(() => ({category_id: postCategory.value?.id ?? -1}))

watch(route, () => {
    updateTitle()
})

updateTitle()

function updateTitle() {
    changeTitle(categoryName.value)
}
</script>

<template>
    <Posts :additional-load-data="loadData">
        <template v-slot:title>
            <p class="text-xl">{{ categoryName }}</p>
        </template>
    </Posts>
</template>

<style scoped>
</style>
