<template>
    <div id="tariq">
        <!-- Main navbar -->
        <main-navbar/>
        <!-- /main navbar -->


        <!-- Page content -->
        <div class="page-content">

            <!-- Main sidebar -->
            <main-sidebar/>
            <!-- /main sidebar -->


            <!-- Main content -->
            <div class="content-wrapper">


                <router-view></router-view>

                <!-- Footer -->
                <app-footer/>
                <!-- /footer -->

            </div>
            <!-- /main content -->

        </div>
        <!-- /page content -->
    </div>
</template>

<script>

    import MainNavbar from './partials/main_navbar.vue'
    import MainSidebar from './partials/main_sidebar.vue'
    import AppFooter from './partials/footer.vue'
    import {App} from "../custom";

    export default {
        name: "admin-page",
        components: {
            MainNavbar, MainSidebar, AppFooter
        },
        mounted() {
            App.initBeforeLoad();
            App.initCore();
            App.initAfterLoad();
        }
    }
</script>

<style scoped>

</style>
