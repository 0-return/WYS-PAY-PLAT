<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <meta content="email=no" name="format-detection">

    <title>向商户付款</title>
    <link rel="stylesheet" href="<?php echo e(asset('/payviews/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('/phone/css/swiper.min.css')); ?>">
    <script type="text/javascript" src="<?php echo e(asset('/phone/js/Screen.js')); ?>"></script>
    <style>
        .pay_money {
            border: 1px solid #20c020;
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
<!-- 
	通用说明： 
	1.模块的隐藏添加class:hide;
	2.body标签默认绑定ontouchstart事件，激活所有按钮的:active效果
-->
<body>
<form>
    <div class="payment">
        <div class="title">
            <img src="<?php echo e(url('/payviews/img/touxiang.png')); ?>"><?php echo e($data['store_name']); ?>

        </div>
        <div class="pay_money">
            <span>支付金额</span>
            <i></i>
            <div class="ipt"> ￥ <span id="total_amount"></span></div>
        </div>
        <div class="remark">
            <textarea class="remark_con" id="remark" placeholder="添加备注(30字以内)"></textarea>
            <img src="<?php echo e(url('/payviews/img/qingkong.png')); ?>" class="quxiao">
        </div>
    </div>


    <input type="hidden" id="csrf_token" value="<?php echo e(csrf_token()); ?>">
    <input type="hidden" id="store_id" value="<?php echo e($data['store_id']); ?>">
    <input type="hidden" id="merchant_id" value="<?php echo e($data['merchant_id']); ?>">
    <input type="hidden" id="open_id" value="<?php echo e($data['open_id']); ?>">


</form>
<div class="keyboard">
    <ul>
        <li data="1"></li>
        <li data="2"></li>
        <li data="3"></li>
        <!-- <li class="xyk_pay" data="信用卡分期"></li> -->
        <li class="confirm" data="确认"><input type="button" id="tijiao"></li>
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
<div class="load-hidden"></div>
<div class="load-hiddenbg" style="display: none"></div>

<!-- 提示 -->
<div class="mask" style="display: none;">
    <div class="popup_bg"></div>
    <div class="result_layer" id="accountMsg">
        <div style="padding: .5rem 0;">
            <p style="font-size:.35rem;height: .6rem;font-weight: 500;">提示</p>
            <p class="tip"></p>
        </div>
        <a class="qr" style="width:100%;border-left:1px solid #ccc;color:#219aff">我知道了</a>
    </div>
</div>

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
</body>
</html>
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
        var $money = $("#total_amount");
        var oldValue = $money.html();
        var newValue = "";


        if (_this.hasClass('del')) {
            newValue = oldValue.substring(0, oldValue.length - 1);
            if (newValue == "") {
                newValue = "";
                $('.confirm').removeClass('green');
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
                $('.confirm').addClass('green');
            } else {
                $money.html(oldValue);
                $('.confirm').addClass('green');
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
        var price = $('#total_amount').html();
        var store_id = $('#store_id').val();
        var m_id = $('#merchant_id').val();
        var open_id = $('#open_id').val();

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
    });
</script>
<script type="text/javascript">
    var jump_success = "<?php echo e(url('weixin/order/success')); ?>";
    var jump_fail = "<?php echo e(url('weixin/order/fail')); ?>";

    $(function () {
        function onBridgeReady() {
            var url = "<?php echo e(url('/api/merchant/qr_auth_pay')); ?>";
            var data =
                {
                    "total_amount": $("#total_amount").html(),
                    "remark": $("#remark").val(),
                    "store_id": $("#store_id").val(),
                    "merchant_id": $("#merchant_id").val(),
                    "_token": $("#csrf_token").val(),
                    "open_id": $('#open_id').val(),
                    "ways_type": '2000'
                };

            // console.log(data);

            $.post(url, data,
                function (back) {
                    $('.load-hidden').removeClass('loading');
                    $('.load-hiddenbg').hide();
                    alert(back.status);
                    alert(back.message);
                    alert(back.data);
                    if (back.status == 1) {
                        var json_data = eval('(' + back.data + ')');
                        WeixinJSBridge.invoke(
                            'getBrandWCPayRequest', json_data,
                            function (res) {
                                // alert(res.err_msg)  如果不成功的话，注意支付目录是否设置正确（支付目录是指当前页面的地址，注意最后的/）
                                // 示例  支付页面所在地址 baidu.com/pay/form    则支付目录设置为  baidu.com/pay/

                                // 支付成功
                                if (res.err_msg == "get_brand_wcpay_request:ok") {
                                    // alert('支付成功！');
                                    location.href = "<?php echo e(url('weixin/order/success')); ?>";
                                }
                                // 支付失败  JS API的返回结果get_brand_wcpay_request:ok仅在用户成功完成支付时返回。由于前端交互复杂，get_brand_wcpay_request:cancel或者get_brand_wcpay_request:fail可以统一处理为用户遇到错误或者主动放弃，不必细化区分。
                                else {
                                    // alert('支付失败！');
                                    // alert(jump_fail);
                                    location.href = "<?php echo e(url('weixin/order/fail')); ?>";
                                }
                            }
                        );
                    }
                    else {
                        $('.mask').show();
                        $('.tip').html(back.message);
                    }

                },
                "json");

        }

        // 触发支付事件
        $('#tijiao').click(function () {
            $('.load-hidden').addClass('loading');
            $('.load-hiddenbg').show();
            $(this).attr('disabled', 'disabled');

            if (typeof WeixinJSBridge == "undefined") {
                if (document.addEventListener) {
                    document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
                } else if (document.attachEvent) {
                    document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
                    document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
                }
            } else {
                onBridgeReady();
            }
        })
    });
    $('.qr').click(function () {
        $('.mask').hide();
        location.reload();
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


