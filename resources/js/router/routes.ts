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
import CreatePost from '@/components/post/editor/CreatePost.vue'
import PostVersion from '@/components/post/editor/PostVersion.vue'
import Post from '@/components/post/Post.vue'
import PostSubmissionsDashboardSection from '@/components/dashboard/PostSubmissionsDashboardSection.vue'
import Studio from '@/components/studio/Studio.vue'
import PostsStudioSection from '@/components/studio/PostsStudioSection.vue'
import PostSubmissionsStudioSection from '@/components/studio/PostSubmissionsStudioSection.vue'
import PostSearch from '@/components/post/PostSearch.vue'
import PostCategory from '@/components/post/PostCategory.vue'

declare module 'vue-router' {
    interface RouteMeta {
        title?: string
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
        redirect: {name: 'dashboard.post-submissions'},
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
                {
                    path: 'post-submissions',
                    name: 'dashboard.post-submissions',
                    component: PostSubmissionsDashboardSection,
                    meta:
                        {
                            title: 'Заявки на публикацию - Панель управления',
                        },
                },
            ],
    },
    {
        path: '/studio',
        name: 'studio',
        component: Studio,
        redirect: {name: 'studio.posts'},
        meta:
            {
                title: 'Контент-студия',
                requiresAuth: true,
            },
        children:
            [
                {
                    path: 'posts',
                    name: 'studio.posts',
                    component: PostsStudioSection,
                    meta:
                        {
                            title: 'Материалы - Контент-студия',
                        },
                },
                {
                    path: 'post-submissions',
                    name: 'studio.post-submissions',
                    component: PostSubmissionsStudioSection,
                    meta:
                        {
                            title: 'Заявки на публикацию - Контент-студия',
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
        path: '/post-version/:id',
        name: 'post-version',
        props: ({params}) => ({id: Number.parseInt(params.id as string, 10) || 0}),
        component: PostVersion,
        meta:
            {
                title: 'Заявка на публикацию',
                requiresAuth: true,
            },
    },
    {
        path: '/post/:slug/',
        name: 'post',
        props: true,
        component: Post,
    },
    {
        path: '/category/:slug',
        name: 'post-category',
        props: true,
        component: PostCategory,
    },
    {
        path: '/search',
        name: 'post-search',
        component: PostSearch,
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
