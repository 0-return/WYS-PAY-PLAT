<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="">
    <title>忘记密码</title>
    <link rel="stylesheet" href="{{asset('/school/css/style.css')}}" media="all">
    <script type="text/javascript" src="{{asset('/school/js/Screen.js')}}"></script>
</head>
<body>
<div class="forget-con">
    <div class="input-con">
        <label>手机号码</label>
        <input type="text" placeholder="请输入手机号码" class="js-tel" />
    </div>

    <div class="input-con">
        <label>验证码</label>
        <input type="text" class="code" placeholder="请输入验证码" />
        <p  class="get-code js-send" id="btnSendCode" onclick="sendMessage()">获取<p>
    </div>

    <div class="input-con">
        <label>新密码</label>
        <input type="text" placeholder="请设置新的登录密码" class="newpass" />
    </div>

</div>
<button type="button" class="confirm">确定</button>

<!-- 提示弹窗 -->
<div class="tip" style="display: none">
    <div class="popup_bg"></div>
    <div class="result_layer" id="accountMsg" style="height:auto;">        
    <div style="padding: .5rem 0;">
        <p style="font-size:.35rem;height: .6rem;font-weight: 500;">提示</p>
        <p id="con">请填写完整信息!</p>
    </div>
    <a class="qr" style="width:100%;border-left:1px solid #ccc;color:#219aff">我知道了</a>
    </div>
</div>
<input type="hidden" class="appid" value="">
<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/fastclick.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/jsencrypt.min.js')}}"></script>
<script>$(function() {FastClick.attach(document.body);});</script>
<script type="text/javascript">
// var token = localStorage.getItem("token");
// // 未登录,跳转登录页面
// $(document).ready(function(){
//     var token = localStorage.getItem("token");
//     if(token==null){
//         window.location.href="{{url('/phone/login')}}"; 
//     }
// });



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
        $("#btnSendCode").html("获取");
        
    }
    else{          
       var phone = $(".js-tel").val();
       if(phone && /^1[3|4|5|7|8|9]\d{9}$/.test(phone)){
           //对
            $('.tip').hide();
            $("#btnSendCode").attr("disabled", "true");
            $("#btnSendCode").html(curCount+'(s)');
            window.clearInterval(InterValObj);//停止计时器
            InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
       } else{
           //不对
           // console.log('手机号码输入错误');
           // swal({title:"手机号码输入错误",timer: 1000,showConfirmButton: false,type:"warning"});
           $('.tip').show();
           $('#con').html("手机号码输入错误");
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
    var data = encrypt.encrypt('phone='+phone+'&info='+"2"+'&type='+'editpassword'+'&app_id='+appid);
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
                $("#btnSendCode").html("获取");
                window.clearInterval(InterValObj);//停止计时器
                $("#btnSendCode").removeAttr("disabled");//启用按钮                    
                ;

                var status=res.message;
                $('.tip').show();
                $('#con').html(status);
                
            }
        },'json');
}

//timer处理函数
function SetRemainTime() {
    if (curCount == 0) {
        window.clearInterval(InterValObj);//停止计时器
        $("#btnSendCode").removeAttr("disabled");//启用按钮
        $("#btnSendCode").html("获取");
    }
    else {
        curCount--;
        $("#btnSendCode").html(curCount+'(s)');
    }
} 



$('.confirm').click(function(){
    var value1=$('.js_method').html();
    var value2=$('.js-tel').val();
    var value3=$('.code').val();

    $.get("{{url('/api/merchant/edit_password')}}",
    {
        phone:$('.js-tel').val(),
        code:$('.code').val(),
        new_password:$('.newpass').val()
    },function(res){
        console.log(res);
        if(res.status==1){
            window.location.href=window.history.go(-1);;
        }else{
            $('.tip').show();
            $('#con').html(res.message);
        }

    },"json");
});
$('.qr').click(function(){
    $('.tip').hide();
})
</script>
</body>
</html>