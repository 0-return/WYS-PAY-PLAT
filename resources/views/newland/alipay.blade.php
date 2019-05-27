<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta name="imagemode" content="force">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <title>向商户付款</title>
    <link rel="stylesheet" href="{{asset('/payviews/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('/phone/css/swiper.min.css')}}">
    <script type="text/javascript" src="{{asset('/phone/js/Screen.js')}}"></script>
    <style>
        .swiper-container {
            width: 100%;
            height: 1.85rem;
            margin-top: .2rem;
        }

        .swiper-container .swiper-wrapper .swiper-slide img {
            width: 100%;
        }

        /*遮罩*/
        .popup_bg {
            width: 100%;
            height: 100%;
            background: #000;
            opacity: .6;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1200
        }

        .result_layer {
            width: 5.6rem;
            height: 3rem;
            z-index: 1300;
            position: fixed;
            border-radius: .05rem;
            background: #fff;
            top: 50%;
            left: 50%;
            margin: -1.5rem auto auto -2.8rem;
            text-align: center
        }

        .result_layer div {
            float: left;
            width: 100%;
            box-sizing: border-box;
            vertical-align: middle;
        }

        .result_layer div p {
            font-size: .3rem;
            font-weight: 500;
            word-wrap: break-word;
        }

        .result_layer p b {
            color: #f30;
            font-size: .42rem;
            font-family: serif;
            margin: 0.05rem
        }

        .result_layer a {
            float: left;
            width: 50%;
            font: .32rem/.8rem "微软雅黑";
            color: #333;
            border-top: 1px solid #ccc
        }

        .result_layer a:last-child {
            color: #f30;
            border-left: 1px solid #ccc;
            box-sizing: border-box
        }

        .result_layer a.confirm {
            border-left: none;
            width: 100%
        }


    </style>
</head>
<body>
<div class="payment">
    <div class="title">
        <img src="{{url('/payviews/img/touxiang.png')}}">{{$data['store_name']}}
    </div>
    <div class="pay_money">
        <span>支付金额</span>
        <i></i>
        <div class="ipt"> ￥ <span id="price"></span></div>
    </div>
    <div class="remark">
        <textarea class="remark_con" id="remark" placeholder="添加备注(30字以内)"></textarea>
        <img src="{{url('/payviews/img/qingkong.png')}}" class="quxiao">
    </div>

</div>

<!--广告轮播图-->
<!-- <div class="swiper-container">
    <div class="swiper-wrapper">
        <div class="swiper-slide"><img src="img/ad.jpg"></div>
        <div class="swiper-slide"><img src="img/ad.jpg"></div>
    </div>
    Add Pagination
    <div class="swiper-pagination"></div>
</div> -->
<!-- 键盘 -->
<div class="keyboard">
    <ul>
        <li data="1"></li>
        <li data="2"></li>
        <li data="3"></li>
        <!-- <li class="xyk_pay" data="信用卡分期"></li> -->
        <li class="confirm" id="payLogButton" data="确认"></li>
        <li data="4"></li>
        <li data="5"></li>
        <li data="6"></li>
        <li data="7"></li>
        <li data="8"></li>
        <li data="9"></li>
        <li data="0"></li>
        <!-- <li class="disable_li"></li> -->
        <li data="."></li>
        <li class="del"><img src="{{url('/payviews/img/tui.png')}}"></li>
    </ul>
</div>
<input type="hidden" value="{{$data['store_id']}}" id="store_id">
<input type="hidden" value="{{$data['open_id']}}" id="open_id">
<input type="hidden" value="{{$data['merchant_id']}}" id="merchant_id">
<input type="hidden" value="{{$data['other_no']}}" id="other_no">
<input type="hidden" value="{{$data['notify_url']}}" id="notify_url">

<input type="hidden" id="token" value="{{csrf_token()}}">
<div class="load-hidden"></div>
<div class="load-hiddenbg" style="display: none"></div>


<!-- 弹框 -->
<!-- <div class="show" style="display: block">
    <div class="popup_bg"></div>
    <div class="result_layer" id="accountMsg">        
    <div style="padding: .48rem 0;">
        <p style="font-size:.35rem;height: .6rem;font-weight: 500;">温馨提示</p>
        <p>请确认是门店付款，谨防网络诈骗</p>
    </div>
    <a class="qd" style="width:100%;border-left:1px solid #ccc;color:#219aff">我知道了</a>
    </div>
</div> -->

<script type="text/javascript" src="{{asset('/phone/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/phone/js/swiper.jquery.min.js')}}"></script>
<script src="{{asset('/phone/js/fastclick.js')}}"></script>
<script>
    window.addEventListener("load", function () {
        FastClick.attach(document.body);
    }, false);
    document.documentElement.addEventListener('dblclick', function (e) {
        e.preventDefault();
    });

    document.addEventListener('touchmove', function (e) {
        e.preventDefault()
    }, false);
</script>
<script>
    $('.keyboard ul li').on("touchend", function () {
        var _this = $(this);
        var num = _this.attr('data');
        var $money = $("#price");
        var oldValue = $money.html();
        var newValue = "";


        if (_this.hasClass('del')) {
            newValue = oldValue.substring(0, oldValue.length - 1);
            if (newValue == "") {
                newValue = "";
                $('.confirm').removeClass('color');
            }

            $money.html(newValue);
        }
        else {
            if (oldValue == "0.00") {
                if (num != ".") {
                    oldValue = "";
                }
            }
            if (oldValue == "0") {
                if (num != ".") {
                    oldValue = "";
                }
            }
            if (oldValue == "") {
                if (num == ".") {
                    oldValue = "0.";
                }
            }
            newValue = oldValue + num;

            //        控制输入的值为五位数和2位小数点
            reg = /^\d{0,8}(\.\d{0,2})?$/g;

            if (reg.test(newValue)) {
                $money.html(newValue);
                $('.confirm').addClass('color');
            } else {
                $money.html(oldValue);
                $('.confirm').addClass('color');
            }

            // 信用卡分期
            if (newValue >= 600 && newValue <= 50000) {
                $('.xyk_pay').addClass('fenqi');
            }
        }
    });
    // 信用卡分期star-----------------------------
    $('.keyboard').on("click", "ul li.fenqi", function () {
        var undertake = 1;
        var price = $('#price').html();
        var store_id = $('#store_id').val();
        var m_id = $('#m_id').val();
        window.location.href = "{{url('/phone/stages?undertake=')}}" + undertake + "&price=" + price + "&store_id=" + store_id + "&m_id=" + m_id;

    })
    // 信用卡分期end-----------------------------


    $(document).ready(function () {
        var swiper = new Swiper('.swiper-container', {
            pagination: '.swiper-pagination',
            paginationClickable: true
        });
    });
    $(".remark_con").focus(function () {
        $('.keyboard').hide();
    });
    $('.pay_money').click(function () {
        $('.keyboard').show();
    });
    $(".remark_con").bind("input propertychange", function () {

        var len = $(this).val().length;
        var max = 30;

        if (len > max) {
            var value = $(this).val().substring(0, max);
            $(this).val(value);
        }

    });
    $('.quxiao').click(function () {
        $('.remark_con').val('');
    });
    $('.remark_con').blur(function () {
        $('.keyboard').show();
    });


</script>
<script>

    var times = 1;
    $("#payLogButton").click(function () {

        $('.load-hidden').addClass('loading');
        $('.load-hiddenbg').show();
        $.post("{{url('/api/merchant/qr_auth_pay')}}", {
            total_amount: $("#price").html(),
            store_id: $("#store_id").val(),
            remark: $("#remark").val(),
            _token: $("#token").val(),
            merchant_id: $("#merchant_id").val(),
            open_id: $("#open_id").val(),
            ways_type: "8001",
            other_no: $("#other_no").val(),
            notify_url: $("#notify_url").val(),

        }, function (data) {
            $('.load-hidden').removeClass('loading');
            $('.load-hiddenbg').hide();
            var data_url = "&total_amount=" + $("#price").html() + "&store_id=" + $("#store_id").val();
            if (data.status == 1) {
                $("#payLogButton").attr('disabled', 'disabled');
                $('.confirm').removeClass('color');
                window.location.href = data.url;//直接跳转到新大陆的链接

            } else {
                data_url = data_url + "&ad_p_id=3";
                window.location.href = "{{url('page/pay_errors?message=')}}" + data.message+data_url;
            }
        }, "json");

    });


    $('.qd').click(function () {
        $('.show').hide();
        localStorage.setItem("show", '1');
    });
    var show = localStorage.getItem("show");
    if (show == 1) {
        $('.show').hide();
    } else {
        $('.show').show();
    }
</script>
</body>
</html>