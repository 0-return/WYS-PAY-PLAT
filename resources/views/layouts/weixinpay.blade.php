<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="renderer" content="webkit">
    @yield('meta')
    <title>@yield('title')</title>
    <meta name="keywords" content="@yield('keywords')">
    <meta name="description" content="@yield('description')">
    <!--[if lt IE 9]>
    <meta http-equiv="refresh" content="0;ie.html" />
    <![endif]-->
    @yield('css')
    <script src="{{asset('/js/jquery.min.js?v=2.1.4')}}" type="text/javascript"></script>
    <link rel="stylesheet" href="https://wx.gtimg.com/res/css/wepayui/0.0.1/wepayui.min.css">
</head>
<body>
@yield('content')
<!-- 全局js -->

@yield('js')
</body>

</html>
