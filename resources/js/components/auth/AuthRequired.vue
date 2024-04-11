<script setup lang='ts'>
import BaseLayout from '@/components/layout/BaseLayout.vue'
import {useModalStore} from '@/stores/modal'
import Message from 'primevue/message'

useModalStore().auth = true
</script>

<template>
    <BaseLayout>
        <div class="flex justify-center">
            <Message :closable="false" severity="info">
                Для просмотра страницы необходимо войти в аккаунт.
            </Message>
        </div>
    </BaseLayout>
</template>

<style scoped>

</style>
