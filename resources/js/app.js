import './bootstrap'
import {createApp} from "vue"
import App from "@/components/App.vue"
import router from "@/router"
import {createPinia} from "pinia"
import PrimeVue from 'primevue/config'

const app = createApp(App)

app.use(router)
app.use(createPinia())
app.use(PrimeVue)

app.mount('#app')
