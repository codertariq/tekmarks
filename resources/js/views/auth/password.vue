<template>

    <div class="content d-flex justify-content-center align-items-center">

        <!-- Login form -->
        <form class="login-form" id="passwordForm" @submit.prevent="submit"  @keydown="passwordForm.errors.clear($event.target.name)">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="icon-reading icon-2x text-slate-300 border-slate-300 border-3 rounded-round p-3 mb-3 mt-1"/>
                        <h5 class="mb-0">Login to your account</h5>
                        <span class="d-block text-muted">Enter your credentials below</span>
                    </div>

                    <div class="form-group form-group-feedback form-group-feedback-left">
                        <input type="text" :class="{'is-invalid' : passwordForm.errors.has('email')}" class="form-control" name="email" :placeholder="trans('auth.email')" v-model="passwordForm.email" autocomplete="off" autofocus>
                        <div class="form-control-feedback">
                            <i class="icon-user text-muted"/>
                        </div>
                        <show-error :form-name="passwordForm" prop-name="email"/>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">{{trans('passwords.reset_password')}}<i class="icon-circle-right2 ml-2"/></button>
                    </div>

                    <div class="text-center">
                        {{trans('auth.back_to_login?')}} <router-link to="/login" class="text-info m-l-5"><b>{{trans('auth.sign_in')}}</b></router-link>
                    </div>
                </div>
            </div>
        </form>
        <!-- /login form -->

    </div>
</template>

<script>
    export default {
        name: "password",
        data(){
            return {
                passwordForm: new Form({
                    email: ''
                })
            }
        },
        mounted() {
            if(!helper.featureAvailable('reset_password')){
                helper.featureNotAvailableMsg();
                return this.$router.push('/dashboard');
            }
        },
        methods: {
            submit(e){
                let loader = this.$loading.show();
                this.passwordForm.post('/api/auth/password')
                    .then(response =>  {
                        toastr.success(response.message);
                        loader.hide();
                        this.$router.push('/login');
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
