import {createRouter, createWebHistory} from 'vue-router'
import {type Component} from 'vue'
import routes from '@/router/routes'
import {useAuthStore} from '@/stores/auth'
import {changeTitle} from '@/helpers'
import AuthRequired from '@/components/auth/AuthRequired.vue'
import NoPermission from '@/components/NoPermission.vue'

let hashObserver: ResizeObserver | undefined = undefined
let hashObserverDisconnectTimeout: NodeJS.Timeout | undefined = undefined

function disconnectHashObserver() {
    if (hashObserver) {
        hashObserver.disconnect()
        clearTimeout(hashObserverDisconnectTimeout)
        hashObserver = undefined
        hashObserverDisconnectTimeout = undefined
    }
}

function getCssVariableValue(variable: string) {
    return getComputedStyle(document.documentElement).getPropertyValue(variable).trim()
}

function remToPixels(remValue: string) {
    const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize)
    return parseFloat(remValue) * rootFontSize
}

const router = createRouter({
    history: createWebHistory(),
    routes: routes,
    scrollBehavior(to, from, savedPosition) {
        return new Promise((resolve) => {
            if (to.hash) {
                disconnectHashObserver()

                hashObserver = new ResizeObserver(entries => {
                    const element = entries[0].target.querySelector(to.hash)
                    if (element) {
                        disconnectHashObserver()
                        const headerHeight = remToPixels(getCssVariableValue('--header-height'))
                        resolve({el: to.hash, top: headerHeight + 16, behavior: 'smooth'})
                    }
                })
                hashObserver.observe(document.body)

                hashObserverDisconnectTimeout = setTimeout(disconnectHashObserver, 60000)
            } else if (savedPosition) {
                savedPosition.behavior = 'smooth'

                const resizeObserver = new ResizeObserver(entries => {
                    if (entries[0].target.clientHeight >= savedPosition.top + screen.height) {
                        resolve(savedPosition)
                        resizeObserver.disconnect()
                        clearTimeout(timeoutId)
                    }
                })

                resizeObserver.observe(document.body)
                const timeoutId = setTimeout(() => {
                    resolve(savedPosition)
                    resizeObserver.disconnect()
                }, 1000)
            } else {
                resolve({top: 0})
            }
        })
    }
})

router.beforeEach((to, _from, next) => {
    const authStore = useAuthStore()

    function displayComponent(component?: Component) {
        to.matched[0].components!.default = component ?? to.matched[0].meta.defaultComponent!
        next()
    }

    function processRouteNavigation() {
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
    }

    if (!to.matched[0].meta.defaultComponent) {
        to.matched[0].meta.defaultComponent = to.matched[0].components!.default
    }

    if (!authStore.isFetched) {
        authStore.fetchUser().then(processRouteNavigation)
    } else {
        processRouteNavigation()
    }
})

router.afterEach((to) => {
    if (to.meta.title) {
        changeTitle(to.meta.title)
    }
})

export default router
