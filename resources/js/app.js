require('./bootstrap');

import store from './store';
import router from './routes';

const app = new Vue({
    el: '#app',
    store,
    router
});
