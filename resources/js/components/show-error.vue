<template>
    <span class="help invalid-feedback" v-if="formName.errors.has(propName)" v-text="formName.errors.get(propName)"/>
</template>

<script>
    export default {
        name: 'show-error',
        props:['formName','propName']
    }
</script>
