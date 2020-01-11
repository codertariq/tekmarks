export default [
    {
        path: '/',
        component: () => import('@views/auth/login'),
    },
    {
        path: '/login',
        component: () => import('@views/auth/login' /* webpackChunkName: "js/auth/login" */)
    },
    {
        path: '/password',
        component: () => import('@views/auth/password' /* webpackChunkName: "js/auth/login" */)
    },
    {
        path: '*',
        component: () => import('@views/errors/page-not-found' /* webpackChunkName: "js/errors/page-not-found" */)
    }
]
