window._ = require('lodash');
try {
    window.Popper = require('popper.js').default;
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}


window.Vue = require('vue');
import VueRouter from 'vue-router';
import VueCookie from 'vue-cookie';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';
import helper from './services/helper';
import Form from './services/form';
import {App} from './custom';

import showError from './components/show-error';


Vue.use(VueRouter);
Vue.use(VueCookie);
Vue.use(Loading);
Vue.use(App);


window.axios = require('axios');
window.toastr = require('toastr');
window.helper = helper;
window.Form = Form;
window.moment = require('moment');
window._get = require('lodash/get');
window._eachRight = require('lodash/eachRight');
window._has = require('lodash/has');

Vue.prototype.trans = (string, args) => {
    let value = _get(window.i18n, string);

    _eachRight(args, (paramVal, paramKey) => {
        value = _replace(value, `:${paramKey}`, paramVal);
    });
    return value;
};


Vue.prototype.$last = function (item, list) {
    return item === list[list.length - 1];
};
Vue.prototype.$first = function (item, list) {
    return item === list[0];
};

Vue.component('show-error', showError);

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Authorization'] = 'TeamTRT ' + Vue.cookie.get('auth_token');

axios.interceptors.response.use(response => {
    return response.data
});

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// import Echo from 'laravel-echo';

// window.Pusher = require('pusher-js');

// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });
