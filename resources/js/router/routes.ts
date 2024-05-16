import Home from '@/components/Home.vue'
import NotFound from '@/components/NotFound.vue'
import EmailVerification from '@/components/auth/EmailVerification.vue'
import PasswordReset from '@/components/auth/PasswordReset.vue'
import Settings from '@/components/settings/Settings.vue'
import ProfileSettings from '@/components/settings/ProfileSettings.vue'
import SecuritySettings from '@/components/settings/SecuritySettings.vue'
import type {RouteRecordRaw} from 'vue-router'
import Dashboard from '@/components/dashboard/Dashboard.vue'
import CategoriesDashboardSection from '@/components/dashboard/CategoriesDashboardSection.vue'
import UsersDashboardSection from '@/components/dashboard/UsersDashboardSection.vue'
import type {Component} from 'vue'
import CreatePost from '@/components/post/CreatePost.vue'
import ReviewPost from '@/components/post/ReviewPost.vue'
import Post from '@/components/post/Post.vue'

declare module 'vue-router' {
    interface RouteMeta {
        title: string
        requiresAuth?: boolean
        requiresModerator?: boolean
        requiresAdmin?: boolean
        defaultComponent?: Component
    }
}

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'home',
        component: Home,
        meta:
            {
                title: 'Добро пожаловать'
            }
    },
    {
        path: '/email/verify/:id/:hash',
        name: 'email-verification',
        component: EmailVerification,
        meta: {
            title: 'Подтверждение E-mail',
        },
    },
    {
        path: '/password/reset',
        name: 'password-reset',
        component: PasswordReset,
        meta: {
            title: 'Сброс пароля',
        },
    },
    {
        path: '/settings',
        name: 'settings',
        component: Settings,
        redirect: {name: 'settings.profile'},
        meta:
            {
                title: 'Настройки',
                requiresAuth: true,
            },
        children:
            [
                {
                    path: 'profile',
                    name: 'settings.profile',
                    component: ProfileSettings,
                    meta:
                        {
                            title: 'Настройки профиля',
                        },
                },
                {
                    path: 'security',
                    name: 'settings.security',
                    component: SecuritySettings,
                    meta:
                        {
                            title: 'Настройки безопасности',
                        },
                },
            ],
    },
    {
        path: '/dashboard',
        name: 'dashboard',
        component: Dashboard,
        redirect: {name: 'dashboard.users'},
        meta:
            {
                title: 'Панель управления',
                requiresAuth: true,
                requiresModerator: true,
            },
        children:
            [
                {
                    path: 'users',
                    name: 'dashboard.users',
                    component: UsersDashboardSection,
                    meta:
                        {
                            title: 'Пользователи - Панель управления',
                        },
                },
                {
                    path: 'categories',
                    name: 'dashboard.categories',
                    component: CategoriesDashboardSection,
                    meta:
                        {
                            title: 'Категории - Панель управления',
                            requiresAdmin: true,
                        },
                },
            ],
    },
    {
        path: '/create-post',
        name: 'create-post',
        component: CreatePost,
        meta:
            {
                title: 'Создание материала',
                requiresAuth: true,
            },
    },
    {
        path: '/review-post/:id',
        name: 'review-post',
        props: ({params}) => ({id: Number.parseInt(params.id as string, 10) || 0}),
        component: ReviewPost,
        meta:
            {
                title: 'Проверка материала',
                requiresAuth: true,
                requiresModerator: true,
            },
    },
    {
        path: '/post/:slug',
        name: 'post',
        props: true,
        component: Post,
        meta:
            {
                title: 'Материал',
            },
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
        meta:
            {
                title: 'Не найдено'
            }
    },
]

export default routes
