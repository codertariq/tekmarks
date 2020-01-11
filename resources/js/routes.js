import Vue from 'vue';
import VueRouter from 'vue-router';
import Loading from 'vue-loading-overlay';
// Import stylesheet
import 'vue-loading-overlay/dist/vue-loading.css';

import frontendRoutes from '@routers/frontend';
import authRoutes from '@routers/auth';
import guestRoutes from '@routers/guest';
import errorRoutes from '@routers/errors';


Vue.use(Loading);
let pageLoader;
let routes = [
    {
        path: '/',
        component: () => import('@layouts/frontend-page'),
        meta: { validate: ['is_frontend_website']},
        children: [
            ...frontendRoutes,
        ]
    },
    {
        path: '/',                      // all the routes which can be access without authentication
        component: () => import('@layouts/guest-page' /* webpackChunkName: "js/guest-page" */),
        meta: { validate: ['is_guest'] },
        children: [
            ...guestRoutes,
        ]
    },
    {
        path: '/',
        component: () => import('@layouts/admin-page'),
        meta: { validate: ['is_auth','two_factor_security','is_screen_locked']},
        children: [
            ...authRoutes,
        ]
    },
    /*{
        path: '/',
        component : require('@layouts/error-page'),
        children: [
            ...errorRoutes
        ]
    }*/
];
const router = new VueRouter({
    routes,
    mode: 'history',
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition
        } else {
            return {x: 0, y: 0}
        }
    }
});


router.beforeEach((to, from, next) => {
    pageLoader = Vue.$loading.show();
    // Initialize toastr notification
    helper.notification();

    helper.setConfig().then(() => {

        let auth_token = Vue.cookie.get('auth_token');
        if (to.matched.some(m => m.meta.validate)) {
            const m = to.matched.find(m => m.meta.validate);
            // Check for authentication; If no, redirect to "/login" route
            if (m.meta.validate.indexOf('is_auth') > -1 && ! auth_token){
                helper.clearSession();
                toastr.error(i18n.auth.auth_required);
                pageLoader.hide();
                return next({ path: '/login' })
            }
            // Check for two factor security; If enabled, redirect to "/auth/security" route after login
            if (m.meta.validate.indexOf('two_factor_security') > -1 && helper.getConfig('two_factor_security') && helper.getAuthUser('two_factor_code')){
                pageLoader.hide();
                return next({ path: '/auth/security' })
            }

            // Check for screen lock; If enabled, redirect to "/auth/lock" route after screen lock timeout
            if (m.meta.validate.indexOf('is_screen_locked') > -1 && helper.getConfig('lock_screen') && helper.isScreenLocked()){
                pageLoader.hide();
                return next({ path: '/auth/lock' })
            }

            if (m.meta.validate.indexOf('is_frontend_website') > -1 && !helper.getConfig('frontend_website')) {
                pageLoader.hide();
                return next({path: '/login'})
            }
// Check for authentication; If authenticated, redirect to "/dashboard" route
            if (m.meta.validate.indexOf('is_guest') > -1 && auth_token){
                pageLoader.hide();
                toastr.error(i18n.auth.guest_required);
                return next({ path: '/dashboard' })
            }
        }

        return next();

    });
});

router.afterEach((to, from) => {
    pageLoader.hide();
});
export default router;
