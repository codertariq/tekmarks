<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('/images/favicon.ico') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Global stylesheets -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
{{--    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">--}}
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
    <link href="{{ asset('global_assets/css/icons/icomoon/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
{{--    <link href="{{ asset('css/limitless_default.css') }}" rel="stylesheet">--}}
    <link href="{{ mix('/css/'.(config('config.theme') ? : 'limitless_default').'.css') }}" id="theme" rel="stylesheet">
    <!-- /global stylesheets -->

</head>

<body>
<div id="app">
    <router-view></router-view>
</div>
<!-- /core JS files -->
<script src="{{ route('assets.lang') }}"></script>
<script src="{{ mix('/js/app.js') }}" defer></script>
<script src="{{ mix('/js/plugin.js') }}" defer></script>
</body>
</html>
