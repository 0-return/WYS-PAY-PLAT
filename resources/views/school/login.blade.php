<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="">
    <title>登录</title>
     <link rel="stylesheet" href="{{asset('/school/css/style.css')}}" media="all">
    <script type="text/javascript" src="{{asset('/school/js/Screen.js')}}"></script>
    <style>
        .beianhao{text-decoration: none;}
    </style>
</head>
<body>
<div class="logo-box">
    <div class="logo-img">
        <img src="{{asset('/phone/img/logo.png')}}">
    </div>
    <div class="logo-con">
        <div class="input-con">
            <label>账号</label>
            <input type="tel" placeholder="请输入账号" class="js-tel" />
        </div>
        <div class="input-con">
            <label>密码</label>
            <input type="password" placeholder="请输入密码" class="js-password" />
            <i class="see"></i>
        </div>
    </div>
    <div class="password-con">
        <div class="pass-sign"><i class="change-img change-img-new"></i> <span>记住密码</span></div>
        <div class="pass-sign"><a href="{{url('/school/forget')}}">忘记密码</a></div>
    </div>
    <div class="submit">
        <button class="login-btn" id="js-btn" onclick="fn()">立即登录</button>
        <button class="register-btn">立即注册</button>
    </div>
    <p style="text-align: center;" class="company"></p>
</div>
<!-- 提示 -->
<div class="mask" style="display: none;">
    <div class="popup_bg"></div>
    <div class="result_layer" id="accountMsg">        
    <div style="padding: .6rem 0;">
        <p style="font-size:.35rem;height: .6rem;font-weight: 500;">提示</p>
        <p class="tip"></p>
    </div>
    <a class="qr" style="width:100%;border-left:1px solid #ccc;color:#219aff">我知道了</a>
    </div>
</div>

<div class="shengming" style="font-size: 12px;color: #999999;text-align: center;position: fixed;bottom:0;    width: 100%;margin-bottom: 16px;"><span class="banquan"></span>&nbsp;<a target="_blank" href="http://www.miitbeian.gov.cn/" class="beianhao" style="font-size: 12px;color: #999999;"></a></div>
</body>
<input type="hidden" class="js_type" value="{{$_GET['login_type']}}">
<input type="hidden" class="s_code" value="{{$_GET['s_code']}}">


<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/fastclick.js')}}"></script>
<script>$(function() {FastClick.attach(document.body);});</script>
<script>
    // $(document).ready(function(){
    //     $.post("{{url('/api/info/get_ym_info')}}",
    //     {
            
    //     },
    //     function(res){
    //         console.log(res);
    //         $('.banquan').html(res.data.banquan);
    //         $('.beianhao').html(res.data.beianhao);
            
    //     },'json');
    // });
    
    $('.change-img').click(function(){
        $(this).toggleClass('change-img-new');
    });
    $('.see').click(function(){
        $(this).toggleClass('see-block');
        if($(this).hasClass('see-block')){
            $('.js-password').attr('type','text');
        }else{
            $('.js-password').attr('type','password');
        }
    });

    $('.qr').click(function(){
        $('.mask').hide();
    });
    $('#js-btn').click(function(){
        $.post("{{url('/api/merchant/login')}}",
        {
            phone:$('.js-tel').val(),
            password:$('.js-password').val(),
            login_type:"{{$_GET['login_type']}}"
        },
        function(res){
            console.log(res);
            if(res.status==303){
                $('.mask').show();
                $('.tip').html(res.message);
            }else{                
                // var tokenValue=res.token;  
                // var type=res.type;                
                // localStorage.setItem("token", tokenValue);
                // localStorage.setItem("type", type);                   
                window.location.href = res.data.url;
            }               
            
        },'json');
    });
    $('.register-btn').click(function(){
        window.location.href = "{{url('/school/register?register_type=')}}"+$('.js_type').val()+"&s_code="+$('.s_code').val();
    });
    
    $("body").keydown(function() {
      if (event.keyCode == "13") {//keyCode=13是回车键
        $('#js-btn').click();
      }
    });  
      
    var h=$(window).height();
    // console.log(h);
    $(window).resize(function() {
        if($(window).height()<h){
            $('.logo-box p').hide();
        }
        if($(window).height()>=h){
            $('.logo-box p').show();
        }
    });  

    var user = $("input")[0],
        pass = $("input")[1],
        
        check = $(".change-img"),
        loUser = localStorage.getItem("user") || "";
        loPass = localStorage.getItem("pass") || "";
        user.value = loUser;
        pass.value = loPass;


//    if(loUser !== "" && loPass !== ""){
//        check.setAttribute("checked","");
//    }


    function fn(){
        if(check.hasClass('change-img-new')){
            localStorage.setItem("user",user.value);
            localStorage.setItem("pass",pass.value);
        }else{
            localStorage.setItem("user","");
            localStorage.setItem("pass","");
        }
    }
</script>
</html>