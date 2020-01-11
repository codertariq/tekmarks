import Vue from 'vue';
import Vuex from 'vuex';
Vue.use(Vuex);
import createPersistedState from 'vuex-persistedstate';


const store = new Vuex.Store({
    state: {
        auth: {
            status: false,
            id: '',
            first_name: '',
            middle_name: '',
            last_name: '',
            email: '',
            username: '',
            avatar: '',
            roles: [],
            color_theme: '',
            direction: '',
            locale: '',
            sidebar: '',
            two_factor_code: null,
            permissions: [],
            last_activity: ''
        },
        config: {}
    },
    mutations: {
        setAuthUserDetail (state, auth) {
            for (let key of Object.keys(auth)) {
                state.auth[key] = auth[key] !== null ? auth[key] : '';
            }
            if ('avatar' in auth)
                state.auth.avatar = auth.avatar !== null ? auth.avatar : '';
            state.auth.status = true;
            state.auth.roles = auth.roles;
            state.auth.permissions = auth.permissions;
            state.auth.last_activity = moment().format();
        },
        resetAuthUserDetail (state) {
            for (let key of Object.keys(state.auth)) {
                state.auth[key] = '';
            }
            state.auth.status = false;
            state.auth.roles = [];
            state.auth.permissions = [];
            state.auth.last_activity = null;
            Vue.cookie.delete('auth_token');
            axios.defaults.headers.common['Authorization'] = null;
        },
        setConfig (state, config) {
            for (let key of Object.keys(config)) {
                state.config[key] = config[key];
            }
        },
        resetConfig (state) {
            for (let key of Object.keys(state.config)) {
                delete state.config[key];
            }
        },
        resetTwoFactorCode (state) {
            state.auth.two_factor_code = '';
        },
        setLastActivity(state) {
            state.auth.last_activity = moment().format();
        }
    },
    actions: {
        setAuthUserDetail ({ commit }, auth) {
            commit('setAuthUserDetail',auth);
        },
        resetAuthUserDetail ({commit}){
            commit('resetAuthUserDetail');
        },
        setConfig ({ commit }, data) {
            commit('setConfig',data);
        },
        resetConfig({ commit }) {
            commit('resetConfig');
        },
        resetTwoFactorCode({ commit }) {
            commit('resetTwoFactorCode');
        },
        setLastActivity({ commit }) {
            commit('setLastActivity');
        }
    },
    getters: {
        getAuthUser: (state) => (name) => {
            return state.auth[name];
        },
        getAuthUserFullName: (state) => {
            return state.auth['first_name']+' '+state.auth['last_name'];
        },
        getAuthStatus: (state) => {
            return state.auth.status;
        },
        hasRole: (state) => (name) => {
            return (state.auth.roles.indexOf(name) >= 0)
        },
        hasAnyRole: (state) => (roles) => {
            return (state.auth.roles.some(role => {
                return roles.indexOf(role) > -1;
            }));
        },
        hasNotAnyRole: (state) => (roles) => {
            return (state.auth.roles.every(role => {
                return roles.indexOf(role) < 0;
            }));
        },
        getConfig: (state) => (name) => {
            return state.config[name];
        },
        hasPermission: (state) => (name) => {
            return (state.auth.permissions.indexOf(name) > -1);
        },
        hasAnyPermission: (state) => (permissions) => {
            return (state.auth.permissions.some(permission => {
                return permissions.indexOf(permission) > -1;
            }));
        },
        getLastActivity: (state) => {
            return state.auth.last_activity;
        },
        getDefaultRole: (state) => (name) => {
            return state.config.default_roles ? state.config.default_roles[name] : '';
        }
    },
    plugins: [
        createPersistedState({
            key: 'SATT IT',
        })
    ]
});

export default store;
