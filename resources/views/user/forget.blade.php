<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>忘记密码</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/login.css')}}" media="all">
</head>

  <div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">
    <div class="layadmin-user-login-main">
      <div class="layadmin-user-login-box layadmin-user-login-header">
        <h2>忘记密码</h2>
      </div>
      <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
      
        <div class="layui-form-item">
          <label class="layadmin-user-login-icon layui-icon layui-icon-cellphone" for="LAY-user-login-cellphone"></label>
          <input type="text" placeholder="请输入注册时的手机号" class="layui-input js-tel">
        </div>
        
        <div class="layui-form-item">
          <div class="layui-row">
            <div class="layui-col-xs7">
              <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-user-login-smscode"></label>
              <input type="text" placeholder="短信验证码" class="layui-input js-code">
            </div>
            <div class="layui-col-xs5">
              <div style="margin-left: 10px;">
                <button type="button" class="layui-btn layui-btn-primary layui-btn-fluid js-send" id="btnSendCode">获取验证码</button>
              </div>
            </div>
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-row">
            <div class="layui-col-xs12">
              <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-vercode"></label>
              <input type="text" placeholder="输入新密码" class="layui-input js-password">
            </div>
            
          </div>
        </div>



        <div class="layui-form-item">
          <button class="layui-btn layui-btn-fluid" id="js-btn">找回密码</button>
        </div>
   
      </div>
    </div>
    
    

  </div>

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/jsencrypt.min.js')}}"></script>
  <script>
  layui.config({
    base: '../../layuiadmin/' //静态资源所在路径
  }).extend({
    index: 'lib/index' //主入口模块
  }).use(['index', 'user'], function(){
    var $ = layui.$
    ,setter = layui.setter
    ,admin = layui.admin
    ,form = layui.form
    ,router = layui.router();

    

    //发送验证码
    var InterValObj; //timer变量，控制时间
    var count = 60; //间隔函数，1秒执行
    var curCount;//当前剩余秒数

    //设置手机号
    // $('.js-send').click(function(){
    //     var textIput=$('.js-tel').val();
    //     if(textIput==''){
    //         // alert("请输入手机号码");
    //        window.clearInterval(InterValObj);//停止计时器
    //        $("#btnSendCode").removeAttr("disabled");//启用按钮
    //        $("#btnSendCode").html("获取验证码");
    //     }else{          
    //        var phone = $(".js-tel").val();

    //        if(phone && /^1[3|4|5|7|8|9]\d{9}$/.test(phone)){

    //            //对
    //             $("#btnSendCode").attr("disabled", "true");
    //             $("#btnSendCode").html(curCount+'(s)');
    //             // window.clearInterval(InterValObj);//停止计时器
    //             InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
    //        } else{
            
    //            //不对
    //            // alert('手机号码输入错误');
    //            window.clearInterval(InterValObj);//停止计时器
    //            $("#btnSendCode").html("获取验证码");
    //        }

    //     }
    // });
    
    // function sendMessage() {
        
    // }
    $('#btnSendCode').click(function(){
      var encrypt = new JSEncrypt();
      // phone=$('.js-tel').val()&info="2"&type=type
      var phone=$('.js-tel').val();
      encrypt.setPublicKey("MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4COVutRbOUfQNjvVOzwK49NzHIPRwwksnJ6QtdHwGmdUZiT2HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS0lfeBMqUcm+bY7aZ54wClm75RI90uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Ycr8S8O004ExtvQqu2QCl7Aai/WC4URIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx37Y9AMR0V94R13v4BFlMQDG+2REVJsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzLPYV0jsiit2AUkyURRLxEaZm2C0YYhfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DXkQIDAQAB");
      var data = encrypt.encrypt('phone='+phone+'&info='+"1"+'&type='+'editpassword');

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
              layer.msg(res.message);
              $("#btnSendCode").attr("disabled", "true");
              $("#btnSendCode").html(curCount+'(s)');
              window.clearInterval(InterValObj);//停止计时器
              InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
          }else{
              
              window.clearInterval(InterValObj);//停止计时器
              $("#btnSendCode").removeAttr("disabled");//启用按钮                    
              alert(res.message);
              $("#btnSendCode").html("获取验证码");
          }
      },'json');
    })

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
    $('#js-btn').click(function(){
      $.post("{{url('/api/user/edit_password')}}",
        {
          phone:$('.js-tel').val(),
          code:$('.js-code').val(),
          newpassword:$('.js-password').val()

        },function(res){
          console.log(res);
            if(res.status==1){
              window.location.href = "{{url('/user/login')}}";
            }else{
              layer.alert(res.message, {icon: 2}); 
            }
        },'json');
    })
    
  });
  </script>
</body>
</html>