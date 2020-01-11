<template>
    <div id="tariq">



        <!-- Page content -->
        <div class="page-content">

            <!-- Main content -->
            <div class="content-wrapper">

                <router-view/>

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
    import AppFooter from "./partials/footer";

    export default {
        name: "guest-page",
        components: {
           AppFooter
        },
    }
</script>

<style scoped>

</style>
