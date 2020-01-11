<template>
    <div class="navbar navbar-expand-md navbar-dark">
        <div class="navbar-brand">
            <router-link :to="{name: 'auth.dashboard'}" class="d-inline-block">
                <img :src="getLogo" alt="">
            </router-link>
        </div>

        <div class="d-md-none">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-mobile">
                <i class="icon-tree5"></i>
            </button>
            <button class="navbar-toggler sidebar-mobile-main-toggle" type="button">
                <i class="icon-paragraph-justify3"></i>
            </button>
        </div>

        <div class="collapse navbar-collapse" id="navbar-mobile">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="#" class="navbar-nav-link sidebar-control sidebar-main-toggle d-none d-md-block">
                        <i class="icon-paragraph-justify3"></i>
                    </a>
                </li>

            </ul>

            <span class="badge bg-success ml-md-3 mr-md-auto">Online</span>

            <ul class="navbar-nav">

                <li class="nav-item dropdown dropdown-user">
                    <a href="#" class="navbar-nav-link d-flex align-items-center dropdown-toggle"
                       data-toggle="dropdown">
                        <img :src="getAuthUser('avatar')" class="rounded-circle mr-2" height="34" alt="">
                        <span v-text="getAuthUser('full_name')"/>
                    </a>

                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item"><i class="icon-user-plus"></i> My profile</a>
                        <a href="#" class="dropdown-item"><i class="icon-coins"></i> My balance</a>
                        <a href="#" class="dropdown-item"><i class="icon-comment-discussion"></i> Messages <span
                            class="badge badge-pill bg-blue ml-auto">58</span></a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item"><i class="icon-cog5"></i> Account settings</a>
                        <a href="#" class="dropdown-item" @click.prevent="logout"><i class="icon-switch2"/>
                            {{trans('auth.logout')}}</a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>

<script>
    export default {
        name: "main_navbar",
        methods: {
            logout() {
                helper.logout().then(() => {
                    this.$router.push('/login')
                })
            }, getAuthUser(name) {
                return helper.getAuthUser(name);
            },
            getConfig(name) {
                return helper.getConfig(name);
            },
            hasPermission(permission) {
                return helper.hasPermission(permission);
            },
            hasRole(role) {
                return helper.hasRole(role);
            }
        },
        computed: {
            getLogo() {
                return helper.getLogo();
            },

        }
    }
</script>

<style scoped>

</style>
