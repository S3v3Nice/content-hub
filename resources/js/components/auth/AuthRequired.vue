<script setup lang='ts'>
import {useModalStore} from '@/stores/modal'
import Message from 'primevue/message'

useModalStore().auth = true
</script>

<template>
    <div class="flex justify-center">
        <Message :closable="false" severity="info">
            Для просмотра страницы необходимо войти в аккаунт.
        </Message>
    </div>
</template>

<style scoped>

</style>
