<template>

    <div class="content d-flex justify-content-center align-items-center">

        <!-- Login form -->
        <form class="login-form" id="loginform" @submit.prevent="submit"  @keydown="loginForm.errors.clear($event.target.name)">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="icon-reading icon-2x text-slate-300 border-slate-300 border-3 rounded-round p-3 mb-3 mt-1"/>
                        <h5 class="mb-0">Login to your account</h5>
                        <span class="d-block text-muted">Enter your credentials below</span>
                    </div>

                    <div class="form-group form-group-feedback form-group-feedback-left">
                        <input type="text" :class="{'is-invalid' : loginForm.errors.has('email_or_username')}" class="form-control" name="email_or_username" :placeholder="trans('auth.email_or_username')" v-model="loginForm.email_or_username" autocomplete="off" autofocus>
                        <div class="form-control-feedback">
                            <i class="icon-user text-muted"/>
                        </div>
                        <show-error :form-name="loginForm" prop-name="email_or_username"/>
                    </div>

                    <div class="form-group form-group-feedback form-group-feedback-left">
                        <input type="password" :class="{'is-invalid' : loginForm.errors.has('password')}" class="form-control"  name="password" :placeholder="trans('auth.password')" v-model="loginForm.password" autocomplete="off">
                        <div class="form-control-feedback">
                            <i class="icon-lock2 text-muted"/>
                        </div>
                        <show-error :form-name="loginForm" prop-name="password"/>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">Sign in <i
                            class="icon-circle-right2 ml-2"/></button>
                    </div>

                    <div class="text-center"  v-if="getConfig('reset_password')">
                       {{trans('auth.forgot_your_password?')}} <router-link to="/password" class="text-info m-l-5"><b>{{trans('auth.reset_here!')}}</b></router-link>
                    </div>
                </div>
            </div>
        </form>
        <!-- /login form -->

    </div>
</template>

<script>
    export default {
        name: "login",
        data(){
            return {
                loginForm: new Form({
                    email_or_username: '',
                    password: ''
                })
            }
        },
        methods: {
            submit(){
                let loader = this.$loading.show();
                this.loginForm.post('/api/auth/login')
                    .then(response =>  {
                        this.$cookie.set('auth_token',response.token,1);
                        axios.defaults.headers.common['Authorization'] = 'TeamTRT ' + response.token;
                        this.$store.dispatch('setConfig',response.config);
                        this.$store.dispatch('setAuthUserDetail',{
                            id: response.user.id,
                            email: response.user.email,
                            username: response.user.username,
                            roles: response.user.user_roles,
                            permissions: response.user.user_permissions,
                            two_factor_code: response.user.two_factor_code,
                            color_theme: response.user.user_preference.theme || this.getConfig('theme'),
                            locale: response.user.user_preference.locale || this.getConfig('locale'),
                            direction: response.user.user_preference.direction || this.getConfig('direction'),
                            sidebar: response.user.user_preference.sidebar || this.getConfig('sidebar')
                        });

                        toastr.success(response.message);

                        if(helper.getConfig('two_factor_security') && response.user.two_factor_code){
                            this.$router.push('/auth/security');
                        }
                        else {
                            var redirect_path = response.reload ? '/dashboard?reload=1' : '/dashboard';

                            let role = response.user.roles.find(o => o.name === 'admin');
                            if(role && helper.getConfig('setup_wizard'))
                                redirect_path = '/setup';

                            this.$store.dispatch('resetTwoFactorCode');
                            this.$router.push(redirect_path);
                        }
                        loader.hide();
                    }).catch(error => {
                    loader.hide();
                    helper.showErrorMsg(error);
                });
            },
            getConfig(config){
                return helper.getConfig(config);
            }
        },
        computed: {
            getLogo(){
                return helper.getLogo();
            }
        }
    }
</script>

<style scoped>

</style>
