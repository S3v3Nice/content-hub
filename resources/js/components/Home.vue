<script setup lang="ts">
import Posts from '@/components/post/Posts.vue'
</script>

<template>
    <Posts>
        <template v-slot:title>
            <p class="text-xl">Последние материалы</p>
        </template>
    </Posts>
</template>

<style scoped>

</style>
