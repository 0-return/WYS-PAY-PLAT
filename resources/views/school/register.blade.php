<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="">
    <title>注册</title>
    <link rel="stylesheet" href="{{asset('/school/css/style.css')}}" media="all">
    <script type="text/javascript" src="{{asset('/school/js/Screen.js')}}"></script>
    <style>
        .login{text-align: center;padding-top:.5rem;}
    </style>
</head>
<body>
<div class="register-con">
    <div class="input-con">
        <label>手机号码</label>
        <input type="text" placeholder="请输入手机号码" class="js-tel" />
    </div>
    <div class="input-con">
        <label>验证码</label>
        <input type="text" class="code" placeholder="请输入验证码" />
        <button type="button" class="form-control get-code js-send" id="btnSendCode" onclick="sendMessage()">获取验证码</button>
    </div>
    <div class="input-con">
        <label>设置密码</label>
        <input type="text" class="js-pass" placeholder="请设置登录密码" />
    </div>
    <div class="input-con">
        <label>确认密码</label>
        <input type="text" class="js-pass-again" placeholder="请再次输入登录密码" />
    </div>
    <div class="input-con">
        <label>激活码</label>
        <input type="text" placeholder="请输入推广员ID(必填)" class="active-code" value="{{$_GET['s_code']}}" disabled="disabled" />
    </div>
    <div class="login">直接登录</div>
</div>
<button type="button" class="confirm">确定</button>
<!-- 提示 -->
<div class="mask" style="display: none">
    <div class="popup_bg"></div>
    <div class="result_layer" id="accountMsg">        
    <div style="padding: .6rem 0;">
        <p style="font-size:.35rem;height: .6rem;font-weight: 500;">提示</p>
        <p class="con-tip"></p>
    </div>
    <a class="qr" style="width:100%;border-left:1px solid #ccc;color:#219aff">我知道了</a>
    </div>
</div>

<input type="hidden" class="appid" value="">
<input type="hidden" class="login_type" value="{{$_GET['register_type']}}">
</body>
<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/fastclick.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/jsencrypt.min.js')}}"></script>
<script>$(function() {FastClick.attach(document.body);});</script>
<script type="text/javascript">
    $('.login').click(function(){
        window.location.href = "{{url('/school/login?login_type=')}}"+$('.login_type').val()+"&s_code="+$('.active-code').val();
    });
    //发送验证码
    var InterValObj; //timer变量，控制时间
    var count = 60; //间隔函数，1秒执行
    var curCount;//当前剩余秒数

    //设置手机号
    $('.js-send').click(function(){
        var textIput=$('.js-tel').val();
        if(textIput==''){
            // alert('请输入手机号码');
            // swal({title:"请输入手机号码",timer: 1000,showConfirmButton: false,type:"warning"});
            window.clearInterval(InterValObj);//停止计时器
            $("#btnSendCode").removeAttr("disabled");//启用按钮
            $("#btnSendCode").html("获取验证码");
        }
        else{          
           var phone = $(".js-tel").val();
           if(phone && /^1[3|4|5|7|8|9]\d{9}$/.test(phone)){
               //对
                $("#btnSendCode").attr("disabled", "true");
                $("#btnSendCode").html(curCount+'(s)');
                window.clearInterval(InterValObj);//停止计时器
                InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
           } else{
               //不对
               // console.log('手机号码输入错误');
               // swal({title:"手机号码输入错误",timer: 1000,showConfirmButton: false,type:"warning"});
               $('.mask').show();
               $('.con').html("手机号码输入错误");
           }

        }
    });
    function sendMessage() {
        var encrypt = new JSEncrypt();
        // phone=$('.js-tel').val()&info="2"&type=type
        var phone=$('.js-tel').val();
        var appid=$('.appid').val();
        // console.log(appid);
        encrypt.setPublicKey("MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4COVutRbOUfQNjvVOzwK49NzHIPRwwksnJ6QtdHwGmdUZiT2HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS0lfeBMqUcm+bY7aZ54wClm75RI90uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Ycr8S8O004ExtvQqu2QCl7Aai/WC4URIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx37Y9AMR0V94R13v4BFlMQDG+2REVJsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzLPYV0jsiit2AUkyURRLxEaZm2C0YYhfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DXkQIDAQAB");
        var data = encrypt.encrypt('phone='+phone+'&info='+"2"+'&type='+'register'+'&app_id='+appid);
        // alert(data);

        curCount = count;
        //设置button效果，开始计时
        $("#btnSendCode").attr("disabled", "true");
        $("#btnSendCode").val(curCount+'(s)');
        InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
        //向后台发送处理数据
        $.post("{{url('/api/Sms/send')}}",
            {
                sign:data
            },
            function (res){
                console.log(res);
                if(res.status==1){
                    $("#btnSendCode").attr("disabled", "true");
                    $("#btnSendCode").html(curCount+'(s)');
                    window.clearInterval(InterValObj);//停止计时器
                    InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
                }else{
                    $("#btnSendCode").html("获取验证码");
                    window.clearInterval(InterValObj);//停止计时器
                    $("#btnSendCode").removeAttr("disabled");//启用按钮                    
                    ;

                    var status=res.message;
                    $('.mask').show();
                    $('.con-tip').html(status);
                    
                    
                    // swal({title:status,timer: 1000,showConfirmButton: false,type:"warning"});
                }
            },'json');
    }

    //timer处理函数
    function SetRemainTime() {
        if (curCount == 0) {
            window.clearInterval(InterValObj);//停止计时器
            $("#btnSendCode").removeAttr("disabled");//启用按钮
            $("#btnSendCode").html("获取验证码");
        }
        else {
            curCount--;
            $("#btnSendCode").html(curCount+'(s)');
        }
    } 
    
    

    $('.qr').click(function(){
        $('.mask').hide();
    });
    $('.confirm').click(function(){
        var pass=$('.js-pass').val();
        var passagain=$('.js-pass-again').val();
        if(pass==passagain){
            $.get("{{url('/api/merchant/register')}}",
            {                
                phone:$('.js-tel').val(),
                password:$(".js-pass").val(),
                password_confirmed:$(".js-pass-again").val(),                
                msn_code:$('.code').val(),
                register_type:"{{$_GET['register_type']}}",                
                s_code:"{{$_GET['s_code']}}"
            },function(res){
                console.log(res);
                if(res.status==1){     
                    window.location.href=res.data.url;                                      
                }else{                    
                    var status=res.message;
                    $('.mask').show();
                    $('.con-tip').html(status);                 
                }                
            },"json");
        }else{
            
            $('.mask').show();
            $('.con-tip').html('两次输入的密码不一致');
        }              
    });
    $('.login').click(function(){

    })
</script>
</html>