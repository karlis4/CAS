import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '../stores/user';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
        path: '/',
        name: 'cameras',
        component: () => import('../pages/MapAndCameras.vue'),
        meta: {
            title: 'Камеры',
            requiresAuth: true
        },
    },
    {
        path: '/cameras/:id',
        name: 'camera',
        component: () => import('../pages/ShowCameraInfo.vue'),
        meta: {
            title: 'Загрузка...',
            requiresAuth: true
        }
    },
    {
        path: '/cameras/edit/:id',
        name: 'changeCameraInfo',
        component: () => import('../pages/ChangeCameraInfo.vue'),
        meta: {
            title: 'Загрузка...',
            requiresAuth: true
        }
    },
    {
        path: '/cameras/photos',
        name: 'photos',
        component: () => import('../pages/Photos.vue'),
        meta: {
            title: 'Снимки',
            requiresAuth: true
        }
    },
    {
        path: '/cameras/videos',
        name: 'videos',
        component: () => import('../pages/Videos.vue'),
        meta: {
            title: 'Видео',
            requiresAuth: true
        }
    },
    {
        path: '/login',
        name: 'login',
        component: () => import('../pages/Login.vue'),
        meta: {
            title: 'Вход',
            requiresGuest: true
        }
    },
    {
        path: '/register',
        name: 'register',
        component: () => import('../pages/Registration.vue'),
        meta: {
            title: 'Регистрация',
            requiresGuest: true
        }
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: () => import('../pages/ForgotPassword.vue'),
        meta: {
            title: 'Смена пароля',
            requiresGuest: true
        }
    },
    {
        path: '/reset-password/:token?',
        name: 'reset-password',
        component: () => import('../pages/ResetPassword.vue'),
        meta: {
            title: 'Смена пароля',
            requiresGuest: true
        },
        props: true
    },
    {
        path: '/email-verified',
        name: 'email-verified',
        component: () => import('../pages/EmailVerified.vue'),
        meta: {
            title: 'Подтверждение почты',
            requiresGuest: true
        }
    }
  ],
});

router.beforeEach((to, from) => {
    const isAuthenticated =  localStorage.getItem('isAuthenticated');
    const token = localStorage.getItem('token');

    const auth = useUserStore();

    auth.userInfo.isAuthenticated = Boolean(isAuthenticated);
    auth.userInfo.auth_token = token;

    document.title = to.meta.title;

    if (to.meta.requiresAuth && !auth.userInfo.isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath }
        };
    }

    if (to.meta.requiresGuest && auth.userInfo.isAuthenticated) {
        return { name: 'cameras' };
    }

    console.log(`🔄 Навигация: ${from.name || '/'} → ${to.name || '/'}`);
})

export default router
