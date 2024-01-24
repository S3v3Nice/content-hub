<script setup lang="ts">
import BaseLayout from "@/components/layout/BaseLayout.vue"
import {onMounted, ref} from "vue"
import axios from "axios"
import {useRoute} from "vue-router"
import Message from "primevue/message"
import ProgressSpinner from "primevue/progressspinner"

const route = useRoute()
const isProcessing = ref(true)
const isSuccess = ref(false)
const responseMessage = ref('')

onMounted(() => {
  tryVerifyEmail()
})

function tryVerifyEmail() {
  const fullUrl = route.fullPath

  axios.post(fullUrl).then((response) => {
    isSuccess.value = response.data.success
    responseMessage.value = response.data.message || ''
  }).finally(() => {
    isProcessing.value = false
  })
}
</script>

<template>
  <BaseLayout>
    <div class="flex items-center justify-center">
      <ProgressSpinner v-if="isProcessing"/>
      <div v-else>
        <Message v-if="isSuccess" :closable="false" severity="success">
          {{ responseMessage || 'E-mail адрес успешно подтвержден.' }}
        </Message>
        <Message v-else :closable="false" severity="error">{{ responseMessage || 'Неизвестная ошибка.' }}</Message>
      </div>
    </div>
  </BaseLayout>
</template>

<style scoped>

</style>