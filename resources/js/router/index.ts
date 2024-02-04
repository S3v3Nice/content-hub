import {createRouter, createWebHistory} from 'vue-router'
import {nextTick} from 'vue'
import routes from '@/router/routes'
import {useAuthStore} from '@/stores/auth'
import {changeTitle} from '@/helpers'
import AuthRequired from '@/components/auth/AuthRequired.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

router.beforeEach((to, _from, next) => {
    const authStore = useAuthStore()

    authStore.fetchUser().then(() => {
        let isAuthenticated = authStore.isAuthenticated

        if (to.matched.some(record => record.meta.requiresAuth)) {
            if (!isAuthenticated) {
                to.matched[0].components!.default = AuthRequired
            }
        }

        next()
    })
})

router.afterEach((to) => {
    nextTick(() => {
        changeTitle(to.meta.title)
    }).then()
})

export default router
