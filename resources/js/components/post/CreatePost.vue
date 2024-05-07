<script setup lang="ts">
import Editor from '@/components/post/editor/Editor.vue'
import Button from 'primevue/button'
</script>

<template>
    <Editor>
        <template v-slot:actions>
            <Button class="w-full" label="Отправить на модерацию"/>
        </template>
    </Editor>
</template>

<style scoped>

</style>
