import axios from 'axios'
import store from '../store'
import notificationJSON from '../../var/notifications.json';
store.dispatch('setConfig',{loaded: false});

export default {

    setConfig() {
        return new Promise((resolve, reject) => {
            if(helper.getConfig('loaded')) {
                resolve()
            } else {
                axios.get('/api/config')
                    .then(response => {
                        store.dispatch('resetConfig');
                        response.loaded = true;
                        store.dispatch('setConfig',response);
                        resolve();
                    }).catch(error => {
                    this.showErrorMsg(error);
                    reject(error);
                })
                    .catch(error => {
                        reject(error)
                    })
            }
        })
    },

    // to logout user
    logout(){
        return axios.post('/api/auth/logout').then(response =>  {
            this.clearSession();
            store.dispatch('setConfig',response.config);
            toastr.success(response.message);
        }).catch(error => {
            this.showErrorMsg(error);
        });
    },

    clearSession(){
        Vue.cookie.delete('auth_token');
        store.dispatch('resetAuthUserDetail');
        store.dispatch('resetConfig');
        store.dispatch('setConfig',{loaded: false});
    },

    // to get authenticated user data
    authUser(){
        return axios.get('/api/auth/user').then(response =>  {
            return response;
        }).catch(error => {
            this.showErrorMsg(error);
        });
    },

    // to set notification position
    notification(){
        var notificationPosition = this.getConfig('notification_position') || 'toast-bottom-right';
        toastr.options = {
            "positionClass": notificationPosition
        };

        this.setLastActivity();

        $('[data-toastr]').on('click',function(){
            var type = $(this).data('toastr'),message = $(this).data('message'),title = $(this).data('title');
            toastr[type](message, title);
        });
    },

    setLastActivity(){
        if(!this.isScreenLocked())
            store.dispatch('setLastActivity')
    },

    // to check for last activity time and lock/unlock screen
    isScreenLocked(){
        let last_activity = this.getLastActivity();
        let lock_screen_timeout = this.getConfig('lock_screen_timeout');
        let last_activity_after_timeout = moment(last_activity).add(lock_screen_timeout,'minutes').format('LLL');
        return (moment().format('LLL') > last_activity_after_timeout);
    },

    // to append filter variables in the URL
    getFilterURL(data){
        let url = '';
        $.each(data, function(key,value) {
            url += (value) ? '&'+key+'='+encodeURI(value) : '';
        });
        return url;
    },

    getLastActivity(){
        return store.getters.getLastActivity;
    },

    // to get Auth Status
    isAuth(){
        return store.getters.getAuthStatus;
    },


    // to get Auth user detail
    getAuthUser(name){
        if(name === 'full_name')
            return store.getters.getAuthUser('first_name')+' '+store.getters.getAuthUser('last_name');
        else if(name === 'avatar'){
            if(store.getters.getAuthUser('avatar'))
                return '/'+store.getters.getAuthUser('avatar');
            else
                return '/global_assets/images/placeholders/user.png';
        }
        else
            return store.getters.getAuthUser(name);
    },

    // to get config
    getConfig(config){
        return store.getters.getConfig(config);
    },

    // to get default role name of system
    getDefaultRole(role){
        return store.getters.getDefaultRole(role);
    },

    // to check role of authenticated user
    hasRole(role){
        return store.getters.hasRole(this.getDefaultRole(role));
    },

    // to check any permission for authenticated user
    hasAnyRole(roles){
        return store.getters.hasAnyRole(roles);
    },

    // to check any permission for authenticated user
    hasNotAnyRole(roles){
        return store.getters.hasNotAnyRole(roles);
    },

    // to check permission for authenticated user
    hasPermission(permission){
        return store.getters.hasPermission(permission);
    },

    // to check any permission for authenticated user
    hasAnyPermission(permissions){
        return store.getters.hasAnyPermission(permissions);
    },

    // to check Admin role
    hasAdminRole(){
        if(this.hasRole('admin'))
            return 1;
        else
            return 0;
    },

    // to check whether a given user has given role
    userHasRole(user,role_name){
        if(!user.roles)
            return false;

        let user_role = user.roles.filter(role => role.name === this.getDefaultRole(role_name));
        return !!user_role.length;

    },

    // to check feature is available or not
    featureAvailable(feature){
        return this.getConfig(feature);
    },

    // returns not accessible message if permission is denied
    notAccessibleMsg(){
        toastr.error(i18n.user.permission_denied);
    },

    // returns feature not available message if permission is denied
    featureNotAvailableMsg(){
        toastr.error(i18n.general.feature_not_available);
    },

    getLogo(){
        if(this.getConfig('logo'))
            return '/'+this.getConfig('logo');
        else
            return '/global_assets/images/logo_light.png';
    },

    getIcon(){
        if(this.getConfig('icon'))
            return '/'+this.getConfig('icon');
        else
            return '/images/default_icon.png';
    },

    // returns user status
    getUserStatus(user){
        let status = [];

        if(user.status === 'activated')
            status.push({'color': 'success','label': i18n.user.status_activated});
        else if(user.status === 'pending_activation')
            status.push({'color': 'warning','label': i18n.user.status_pending_activation});
        else if(user.status === 'pending_approval')
            status.push({'color': 'warning','label': i18n.user.status_pending_approval});
        else if(user.status === 'banned')
            status.push({'color': 'danger','label': i18n.user.status_banned});
        else if(user.status === 'disapproved')
            status.push({'color': 'danger','label': i18n.user.status_disapproved});

        return status;
    },


    getDateDiff(date1, date2){
        if (date2 === 'undefined')
            date2 = moment().startOf('day');

        date1 = moment(date1,'YYYY-MM-DD').startOf('day');
        let day = date1.diff(date2, 'days');
        return Math.abs(day);
    },


    // to mass assign one object in another object
    formAssign(form, data){
        for (let key of Object.keys(form)) {
            if(key !== "originalData" && key !== "errors" && key !== "autoReset" && key !== "providers"){
                form[key] = data[key] || '';
            }
        }
        return form;
    },

    // to get date in desired format
    formatDate(date){
        if(!date)
            return;

        return moment(date).format(this.getConfig('date_format'));
    },

    // to get date in desired format
    defaultDate(){
        return moment(new Date).format(this.getConfig('date_format'));
    },

    // to get date time in desired format
    formatDateTime(date){
        if(!date)
            return;

        var date_format = this.getConfig('date_format');
        var time_format = this.getConfig('time_format');

        return moment(date).format(date_format+' '+time_format);
    },

    // to get time in desired format
    defaultDateTime(){
        return moment(new Date).format(this.getConfig('date_format')+' '+this.getConfig('time_format'));
    },

    // to get time in desired format
    formatTime(time){
        if(!time)
            return;

        var time_format = this.getConfig('time_format');
        let date = moment().format('YYYY-MM-DD')+' '+time;

        return moment(date).format(time_format);
    },

    // to get time in desired format
    defaultTime(){
        return moment(new Date).format(this.getConfig('time_format'));
    },

    // to get time from now
    formatDateTimeFromNow(datetime){
        if(!datetime)
            return;

        return moment(datetime).fromNow();
    },

    toDate(date){
        return date ? moment(date).format('YYYY-MM-DD') : '';
    },

    toTime(time){
        return (time.hour && time.minute) ? helper.formatWithPadding(time.hour,2)+':'+helper.formatWithPadding(time.minute,2)+' '+time.meridiem : '';
    },

    // to change first character of every word to upper case
    ucword(value){
        if(!value)
            return;

        return value.toLowerCase().replace(/\b[a-z]/g, function(value) {
            return value.toUpperCase();
        });
    },

    // to change string into human readable format
    toWord(value){
        if(!value)
            return;

        value = value.replace(/-/g, ' ');
        value = value.replace(/_/g, ' ');

        return value.toLowerCase().replace(/\b[a-z]/g, function(value) {
            return value.toUpperCase();
        });
    },

    createSlug(value){
        return value.toLowerCase()
            .replace(/\s+/g, '-') // Replace spaces with
            .replace(/&/g, '-and-') // Replace & with ‘and’
            .replace(/[^\w\-]+/g, '') // Remove all non-word characters
            .replace(/\-\-+/g, '-') // Replace multiple — with single -
            .replace(/^-+/, '') // Trim — from start of text .replace(/-+$/, '');
    },

    // shows toastr notification for axios form request
    showErrorMsg(error){
        this.setLastActivity();
        if (error.hasOwnProperty("response")) {
            const statusCode = error.response.status;

            const message = error.response.hasOwnProperty("data") ? error.response.data.message : error.response.message;
            const login = error.response.hasOwnProperty("data") ? error.response.data.login : error.response.login;

            if (statusCode === 400 || statusCode === 401 || statusCode === 403) {
                toastr.error(message);
            } else if(statusCode === 500) {
                toastr.error(i18n.general.something_wrong);
            } else if(statusCode === 422 && error.response.hasOwnProperty("error")) {
                toastr.error(error.response.error);
            } else if(statusCode === 422 && error.response.hasOwnProperty("data")) {
                toastr.error(error.response.data.errors.message[0]);
            } else if(statusCode === 404) {
                toastr.error(i18n.general.invalid_link);
            }

            if (login) {
                this.clearSession();
                location.reload();
            }
        } else if(error.hasOwnProperty("errors")) {
            const message = error.errors.hasOwnProperty("message") ? error.errors.message : '';

            if (message) {
                toastr.error(message);
            }
        }
    },

    // returns error message for axios form request
    fetchErrorMsg(error){
        return error.errors.message[0];
    },

    // round numbers as given precision
    roundNumber(number, precision){
        precision = Math.abs(parseInt(precision)) || 0;
        var multiplier = Math.pow(10, precision);
        return (Math.round(number * multiplier) / multiplier);
    },

    // round numbers as given precision
    formatNumber(number,decimal_place){
        if (decimal_place === undefined)
            decimal_place = 2;
        return this.roundNumber(number,decimal_place);
    },

    // fill number with padding
    formatWithPadding(n, width, z){
        z = z || '0';
        n = n + '';
        return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    },

    // generates random string of certain length
    randomString(length) {
        if (length === undefined)
            length = 40;
        var chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        var result = '';
        for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
        return result;
    },

    bytesToSize(bytes) {
        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        if (bytes === 0) return '0 Byte';
        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    },

    formatCurrency(price){
        var currency = helper.getConfig('default_currency');
        let decimal_place = currency.decimal_place || 2;
        if(currency.position === 'prefix')
            return currency.symbol+''+this.roundNumber(price,decimal_place);
        else
            return this.roundNumber(price,decimal_place)+' '+currency.symbol;
    },


    getVoucherNumber(transaction){
        return (transaction.prefix ? transaction.prefix : '')+transaction.number;
    },

    getExcerpts(content){
        return content.replace(/<[^>]+>/g, '');
    },

    truncateWords(text, length, suffix){
        var trimmedString = text.substr(0, length);
        return trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" "))) + suffix;
    },

    truncateLetters(text, length, suffix){
        return text.replace(new RegExp("^(.{"+length+"}[^\s]*).*"), "$1") + suffix;
    },

    frontendConfigurationAccessible(){
        return helper.hasPermission('configure-frontend');
    },

    getAuthToken(){
        return Vue.cookie.get('auth_token');
    },

    showDemoNotification(items){
        if(this.getConfig('mode'))
            return;

        if(Vue.cookie.get('hide_sattit_tour'))
            return;

        for (let i = 0; i < items.length; i++) {
            let item = items[i];

            let cookie_name = 'sattit_notification_' + item;

            if (Vue.cookie.get(cookie_name))
                continue;

            if(!notificationJSON.hasOwnProperty(item))
                continue;

            Vue.notify({
                group: 'demo',
                clean: true
            });

            Vue.notify({
                group: 'demo',
                title: notificationJSON[item].title,
                nextUrl: '/student/admission',
                text: notificationJSON[item].message,
                duration: 120000
            });

            Vue.cookie.set(cookie_name, this.randomString(20) , {expires: '30m'});
            break;
        }
    }
}
