export default [

    {
        path: '*',
        component: () => import('@views/errors/page-not-found' /* webpackChunkName: "js/errors/page-not-found" */)
    }
]
