<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="">
    <title>商家付款</title>
    <link rel="stylesheet" href="{{asset('/zhifu/css/zhifu.css')}}">
    <link rel="stylesheet" href="{{asset('/zhifu/css/swiper.min.css')}}">
    <script type="text/javascript" src="{{asset('/school/js/Screen.js')}}"></script>
    <style>
        .swiper-container {
            width: 94%;
            height: 3.2rem;
            margin-top:.2rem;
        }
        .swiper-container .swiper-wrapper .swiper-slide img{
            width:100%;
        }

    </style>
</head>
<body>
    <div class="box">
        <img src="{{asset('/zhifu/img/shibai.png')}}">
        <div class="errors">{{$message}}</div>
    </div>

    <div class="line"><span>广告</span></div>

    <!--广告轮播图-->
    <div class="swiper-container">
        <div class="swiper-wrapper">
            @foreach($ad_data as $k=>$v)
                <div class="swiper-slide"><a href="{{$v['click_url']}}"><img src="{{$v['img_url']}}"></a></div>
            @endforeach
        </div>
        <!-- Add Pagination -->
        <div class="swiper-pagination"></div>
    </div>

    <div class="company"></div>



<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/zhifu/js/swiper.jquery.min.js')}}"></script>
<script type="text/javascript">

    $(document).ready(function () {
        var swiper = new Swiper('.swiper-container', {
            pagination: '.swiper-pagination',
            paginationClickable: true,
            spaceBetween: 30,
            centeredSlides: true,
            autoplay: 2000,
            autoplayDisableOnInteraction: false,
            loop : true,

        });
    });


</script>
</body>
</html>