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
    <link rel="stylesheet" href="<?php echo e(asset('/payviews/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('/phone/css/swiper.min.css')); ?>">
    <script type="text/javascript" src="<?php echo e(asset('/phone/js/Screen.js')); ?>"></script>
    <style>
        .swiper-container {
            width: 100%;
            height: 1.85rem;
            margin-top: .2rem;
        }

        .swiper-container .swiper-wrapper .swiper-slide img {
            width: 100%;
        }

        .swindle {
            width: 100% !important;
            height: .6rem !important;
            font-size: .24rem !important;
        }

        .keyboard ul li.swindle:after {
            font-size: .24rem !important;
        }


    </style>
</head>
<body>
<div class="payment">
    <div class="title">
        <img src="<?php echo e(url('/payviews/img/touxiang.png')); ?>"><?php echo e($data['store_name']); ?>

    </div>
    <div class="pay_money">
        <span>支付金额</span>
        <i></i>
        <div class="ipt"> ￥ <span id="price"></span></div>
    </div>
    <div class="remark">
        <textarea class="remark_con" id="remark" placeholder="添加备注(30字以内)"></textarea>
        <img src="<?php echo e(url('/payviews/img/qingkong.png')); ?>" class="quxiao">
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
        <li class="del"><img src="<?php echo e(url('/payviews/img/tui.png')); ?>"></li>
    </ul>
</div>
<input type="hidden" value="<?php echo e($data['store_id']); ?>" id="store_id">
<input type="hidden" value="<?php echo e($data['open_id']); ?>" id="open_id">
<input type="hidden" value="<?php echo e($data['merchant_id']); ?>" id="m_id">
<input type="hidden" id="token" value="<?php echo e(csrf_token()); ?>">


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

<script type="text/javascript" src="<?php echo e(asset('/phone/js/jquery-2.1.4.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('/phone/js/swiper.jquery.min.js')); ?>"></script>
<script src="<?php echo e(asset('/phone/js/fastclick.js')); ?>"></script>
<script>
    $(function () {
        FastClick.attach(document.body);
    });
    document.documentElement.addEventListener('dblclick', function (e) {
        e.preventDefault();
    });
    document.addEventListener('touchmove', function (event) {
        event.preventDefault();
    }, false);
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
        } else {
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
        window.location.href = "<?php echo e(url('/phone/stages?undertake=')); ?>" + undertake + "&price=" + price + "&store_id=" + store_id + "&m_id=" + m_id;

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
    })
</script>
<script>
    $("#payLogButton").click(function () {
        $('.load-hidden').addClass('loading');
        $('.load-hiddenbg').show();
        $.post("<?php echo e(url('/api/merchant/qr_auth_pay')); ?>", {
            total_amount: $("#price").html(),
            store_id: $("#store_id").val(),
            remark: $("#remark").val(),
            _token: $("#token").val(),
            merchant_id: $("#m_id").val(),
            open_id: $("#open_id").val(),
            ways_type: '1000'
        }, function (data) {
            console.log(data);
            alert(data.status);
            alert(data.message);
            alert(data.data.trade_no);
            $('.load-hidden').removeClass('loading');
            $('.load-hiddenbg').hide();
            if (data.status == 1) {

                $("#payLogButton").attr('disabled', 'disabled');
                $('.confirm').removeClass('color');
                AlipayJSBridge.call("tradePay", {
                    tradeNO: data.data.trade_no
                }, function (result) {
                    //付款成功
                    if (result.resultCode == "9000") {
                        window.location.href = "<?php echo e(url('admin/alipayopen/PaySuccess?price=')); ?>" + $("#price").html() + "&out_trade_no=" + data.out_trade_no + "&store_id=" + $("#store_id").val() + "&store_id=" + $("#store_id").val();
                    }
                    if (result.resultCode == "6001") {
                        window.location.href = "<?php echo e(url('admin/alipayopen/OrderErrors?code=6001')); ?>" + "&store_id=" + $("#store_id").val();
                    }
                });
            } else {
                window.location.href = "<?php echo e(url('admin/alipayopen/OrderErrors?code=')); ?>" + data.status + "&msg=" + data.message + "&store_id=" + $("#store_id").val();
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