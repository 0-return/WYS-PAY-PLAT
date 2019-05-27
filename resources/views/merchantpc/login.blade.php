<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>登陆-教育缴费系统</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/login.css')}}" media="all">
    <style>
        #LAY-user-login {
            background: url("../../layuiadmin/layui/images/bg.png") no-repeat;
            background-size: 100% 100%;
        }

        .logo {
            position: absolute;
            top: 12px;
            left: 50px;
        }

        .school_name {
            position: absolute;
            top: 15px;
            right: 50px;
            font-size: 22px;
        }

        .layadmin-user-login-main {
            background: url("../../layuiadmin/layui/images/shurukuang-bg.png");
            border-radius: 10px;
            margin-top: 80px;
            width: 370px;
        }

        .layadmin-user-login-body .layui-form-item .layui-input {
            padding-left: 10px;
            background-color: transparent !important;
            border: none;
            border-bottom: 1px solid #fff;
            color: #fff !important;
        }

        .layadmin-user-login-body .layui-form-item .layui-input::-webkit-input-placeholder { /* WebKit browsers */
            color: #fff;
        }

        .layadmin-user-login-body .layui-form-item .layui-input:-moz-placeholder { /* Mozilla Firefox 4 to 18 */
            color: #fff;
        }

        .layadmin-user-login-body .layui-form-item .layui-input::-moz-placeholder { /* Mozilla Firefox 19+ */
            color: #fff;
        }

        .layadmin-user-login-body .layui-form-item .layui-input:-ms-input-placeholder { /* Internet Explorer 10+ */
            color: #fff;
        }

        .layui-btn {
            background-color: #00a3fe;
        }

        input:-webkit-autofill {
            background-color: #FAFFBD;
            background-image: none;
            color: #000;
            -webkit-box-shadow: 0 0 0 1000px white inset;

        }

        @media screen and (max-width: 1366px) {
            .logo img {
                width: 150px;
                height: 30px;
            }

            .school_name {
                font-size: 18px;
            }
        }

    </style>
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">

    <div class="logo">
        <img src="{{asset('/layuiadmin/layui/images/logo.png')}}">
    </div>
    <div class="school_name">


    </div>

    <div class="layadmin-user-login-main">
        <div class="layadmin-user-login-box layadmin-user-login-header">
            <h2 style="color:#fff;padding-top: 40px;font-size: 22px;">欢迎您登陆</h2>
        </div>

        <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
            <div class="layui-form-item">
                <!--<label class="layadmin-user-login-icon layui-icon layui-icon-username" for="LAY-user-login-username"></label>-->
                <input type="text" name="phone" id="LAY-user-login-username" lay-verify="required" placeholder="请输入手机号码"
                       class="layui-input phone">
            </div>
            <div class="layui-form-item">
                <!--<label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-password"></label>-->
                <input type="password" name="password" id="LAY-user-login-password" lay-verify="required"
                       placeholder="请输入登录密码" class="layui-input password">
            </div>

            <div class="layui-form-item" style="margin-bottom: 20px;padding: 30px 0;">
                <!-- <input type="checkbox" name="remember" lay-skin="primary" title="记住密码"> -->
                <a href="{{url('/merchantpc/forget')}}" class="layadmin-user-jump-change layadmin-link"
                   style="color:#fff !important;">忘记密码？</a>
            </div>
            <div class="layui-form-item">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="LAY-user-login-submit" id="submit">登
                    录
                </button>
                <!--<div class="login">登录</div>-->
            </div>

        </div>
    </div>


</div>

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script>
<script>
    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'user'], function () {
        var $ = layui.$
            , setter = layui.setter
            , admin = layui.admin
            , form = layui.form
            , router = layui.router()
            , search = router.search;

        form.render();


        //提交

        $("#submit").click(function () {
            $.get("{{url('/api/merchant/login')}}",
                {
                    phone: $('.phone').val(),
                    password: $('.password').val()
                }, function (res) {

                    console.log(res);
                    if (res.status == 1) {
                        localStorage.setItem('token', res.data.token);
                        localStorage.setItem('store_id', res.data.store_id);
                        layer.msg('登录成功', {
                            offset: '15px'
                            , icon: 1
                            , time: 1000
                        }, function () {
                            location.href = "{{url('/merchantpc/index')}}"; //后台主页
                        });

                    } else {

                        layer.msg(res.message, {
                            offset: '15px'
                            , icon: 2
                            , time: 3000
                        });

                    }

                }, "json");
        })
        $("body").keydown(function () {
            if (event.keyCode == "13") {//keyCode=13是回车键
                $('#submit').click();
            }
        });

    });
</script>
</body>
</html>