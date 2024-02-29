import {createRouter, createWebHistory} from 'vue-router'
import {type Component, nextTick} from 'vue'
import routes from '@/router/routes'
import {useAuthStore} from '@/stores/auth'
import {changeTitle} from '@/helpers'
import AuthRequired from '@/components/auth/AuthRequired.vue'
import NoPermission from '@/components/NoPermission.vue'

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
})

router.beforeEach((to, _from, next) => {
    const authStore = useAuthStore()

    function displayComponent(component?: Component) {
        to.matched[0].components!.default = component ?? to.matched[0].meta.defaultComponent!
        next()
    }

    if (!to.matched[0].meta.defaultComponent) {
        to.matched[0].meta.defaultComponent = to.matched[0].components!.default
    }

    authStore.fetchUser().then(() => {
        if (
            to.matched.some(record => record.meta.requiresAuth) &&
            !authStore.isAuthenticated
        ) {
            displayComponent(AuthRequired)
            return
        }

        if (
            to.matched.some(record => record.meta.requiresModerator) &&
            !authStore.isModerator
        ) {
            displayComponent(NoPermission)
            return
        }

        if (
            to.matched.some(record => record.meta.requiresAdmin) &&
            !authStore.isAdmin
        ) {
            displayComponent(NoPermission)
            return
        }

        displayComponent()
    })
})

router.afterEach((to) => {
    nextTick(() => {
        changeTitle(to.meta.title)
    }).then()
})

export default router
