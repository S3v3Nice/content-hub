import Home from '@/components/Home.vue'
import NotFound from '@/components/NotFound.vue'
import EmailVerification from '@/components/auth/EmailVerification.vue'
import PasswordReset from '@/components/auth/PasswordReset.vue'

export default [
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
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFound,
        meta:
            {
                title: 'Не найдено'
            }
    },
]
