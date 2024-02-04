import '@/bootstrap'
import {createApp} from "vue"
import App from "@/components/App.vue"
import router from "@/router"
import {createPinia} from "pinia"
import PrimeVue from 'primevue/config'
import ToastService from 'primevue/toastservice'

const app = createApp(App)

app.use(router)
app.use(createPinia())
app.use(PrimeVue)
app.use(ToastService)

app.mount('#app')
