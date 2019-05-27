<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>提现</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/template.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .tongbu{background-color: #4c9ef8;color:#fff;}
    .cur{color:#009688;}
    .way{height:38px;line-height: 38px;}

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }
    input[type="number"]{
        -moz-appearance: textfield;
    }
  </style>
</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">

        <div class="layui-fluid">
          <div class="layui-row layui-col-space15"> 

            <div class="layui-col-md12">
              <div class="layui-card"> 
                <div class="layui-card-header">提现</div>

                  <div class="layui-card-body">
                    <div class="layui-btn-container" style="font-size:14px;">
                      <div class="layui-row  layadmin-homepage-padding15">
                       
                        <div class="layui-col-md7 layadmin-homepage-padding8">
                          <div class="layui-row layadmin-homepage-text-center">
                            <div class="layui-col-md3 layui-col-sm3 layui-col-xs3">
                              <p class="h4 one">0.00</p>
                              <mdall>赏金余额</mdall>
                            </div>
                            <div class="layui-col-md3 layui-col-sm3 layui-col-xs3">
                              <p class="h4 two">0.00</p>
                              <mdall>已结算赏金</mdall>
                            </div>
                            <div class="layui-col-md3 layui-col-sm3 layui-col-xs3">
                              <p class="h4 three">0.00</p>
                              <mdall>未结算赏金</mdall>
                            </div>
                            
                          </div>
                        </div>
                        <div class="layui-col-md5">
                            <a href="javascript:;" class="layui-btn layui-btn-normal" id='tixianjine'>提现</a>
                            <a href="javascript:;" class="layui-btn layui-btn-normal" id='tixianjilu' lay-href=''>提现记录</a>
                            <a href="javascript:;" class="layui-btn" style='margin-bottom:0' id='change'>更换支付宝</a>
                            <a href="javascript:;" class="layui-btn" style='margin-bottom:0' id='add'>绑定支付宝</a>
                        </div>
                      </div>

                    </div>
                    
                  </div>
                </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

<div id="tixian" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">赏金金额:</label>
        <div class="layui-input-block">
            <div class="way jine1"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">提现到:</label>
        <div class="layui-input-block">
            <div class="way jine2"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">提现金额:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入提现金额" class="layui-input jine3">
            <div class="layui-form-mid layui-word-aux">注：单笔提现金额不得低于50元</div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">支付密码:</label>
        <div class="layui-input-block">
            <input type="password" placeholder="请输入支付密码" class="layui-input jine4">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn submit1">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="genghuan" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <!-- <label class="layui-form-label">验证码:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入验证码" class="layui-input change1">
        </div> -->
        <div class="layui-row">
          <label class="layui-form-label">短信验证码:</label>
          <div class="layui-input-block">
            <div class="layui-col-xs7">            
              <input type="text" placeholder="请输入短信验证码" class="layui-input js-code">
            </div>
            <div class="layui-col-xs5">
              <div style="margin-left: 10px;">
                <button type="button" class="layui-btn layui-btn-primary layui-btn-fluid js-send" id="btnSendCode">获取验证码</button>
              </div>
            </div>
          </div>
          
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">支付宝名称:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入支付宝名称" class="layui-input change2">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">支付宝账号:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入支付宝账号" class="layui-input change3">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn submit2">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="addaalipay" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      
      <div class="layui-form-item">
        <label class="layui-form-label">支付宝名称:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入支付宝名称" class="layui-input add1">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">支付宝账号:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入支付宝账号" class="layui-input add2">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn submit3">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
  <input type="hidden" class="store_id">
  <input type="hidden" class="sort">
  <input type="hidden" class="stu_class_no">

  <input type="hidden" class="stu_order_batch_no">
  <input type="hidden" class="user_id">

  <input type="hidden" class="pay_status">
  <input type="hidden" class="pay_type">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script type="text/javascript" src="{{asset('/school/js/jsencrypt.min.js')}}"></script>
    <script>
    var token = localStorage.getItem("Usertoken");
    var l_phone = localStorage.getItem("l_phone");
    var str=location.search;
    var user_id=str.split('?')[1];

    console.log(user_id)

    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form','table','laydate'], function(){
        var $ = layui.$
          ,admin = layui.admin
          ,form = layui.form
          ,table = layui.table
          ,laydate = layui.laydate;
          // 未登录,跳转登录页面
          $(document).ready(function(){        
              if(token==null){
                  window.location.href="{{url('/user/login')}}"; 
              }
          })


        // 查看赏金金额
        $.ajax({
          url : "{{url('/api/wallet/source_query')}}",
          data : {token:token,user_id:user_id,return_type:'1' },
          type : 'post',
          success : function(data) {
            console.log(data);
            $('.one').html(data.data.money)
            $('.two').html(data.data.settlement_money)
            $('.three').html(data.data.unsettlement_money)

            $('.jine1').html(data.data.money)
          },
            
        });
        // 查看是否绑定支付宝
        $.ajax({
          url : "{{url('/api/wallet/account')}}",
          data : {token:token},
          type : 'post',
          success : function(res) {
            console.log(res);
            if (res.status == 1) {//已绑定
              $('#add').hide()
              $('.jine2').html(res.data.alipay_account+'('+res.data.alipay_name+')')
            }else{//未绑定
              $('#tixianjine').hide() 
              $('#change').hide() 
            }
          },
            
        });
        // 提现

        $('#tixianjine').click(function(){
          layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: '516px',
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: $('#tixian')
          });
        });
        $('.submit1').click(function(){
          var encrypt = new JSEncrypt();        
          encrypt.setPublicKey("MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4COVutRbOUfQNjvVOzwK49NzHIPRwwksnJ6QtdHwGmdUZiT2HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS0lfeBMqUcm+bY7aZ54wClm75RI90uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Ycr8S8O004ExtvQqu2QCl7Aai/WC4URIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx37Y9AMR0V94R13v4BFlMQDG+2REVJsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzLPYV0jsiit2AUkyURRLxEaZm2C0YYhfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DXkQIDAQAB");
          var data = encrypt.encrypt('pay_password='+$('.jine4').val());
         
          $.post("{{url('/api/user/check_pay_password')}}",
          {
            token:token,
            sign:data 
          },
          function (res){
            console.log(res);
            if(res.status==1){
              $.post("{{url('/api/wallet/out_wallet')}}",
              {
                  token:token,
                  total_amount:$('.jine3').val(),
                  alipay_account:$('.jine2').html(), 

              },function(res){
                  console.log(res);
                  if(res.status==1){
                    layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 2000
                    },function(){
                      window.location.reload();
                    });
                  }else{
                    layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 3000
                    });
                  }
                  
              },"json");
            }else{
              layer.msg(res.message, {
                offset: '15px'
                ,icon: 2
                ,time: 3000
              });   
            }
          },'json');



          
        });
        
        var InterValObj; //timer变量，控制时间
        var count = 60; //间隔函数，1秒执行
        var curCount;//当前剩余秒数
        // 更换
        $('#change').click(function(){
          layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: '516px',
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: $('#genghuan')
          });
          var encrypt = new JSEncrypt();
          // phone=$('.js-tel').val()&info="2"&type=type
          var phone=l_phone;
          encrypt.setPublicKey("MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4COVutRbOUfQNjvVOzwK49NzHIPRwwksnJ6QtdHwGmdUZiT2HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS0lfeBMqUcm+bY7aZ54wClm75RI90uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Ycr8S8O004ExtvQqu2QCl7Aai/WC4URIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx37Y9AMR0V94R13v4BFlMQDG+2REVJsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzLPYV0jsiit2AUkyURRLxEaZm2C0YYhfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DXkQIDAQAB");
          var data = encrypt.encrypt('phone='+phone+'&info='+"2"+'&type='+'account');

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

        
        $('.submit2').click(function(){ 

          $.post("{{url('/api/wallet/add_account')}}",
          {
            token:token,
            code:$('.js-code').val(),
            alipay_name:$('.change2').val(),
            alipay_account:$('.change3').val(),        

          },function(res){
            console.log(res);
            if(res.status==1){
              layer.msg(res.message, {
                offset: '15px'
                ,icon: 1
                ,time: 2000
              },function(){
                window.location.reload();
              });
            }else{
              layer.msg(res.message, {
                offset: '15px'
                ,icon: 2
                ,time: 3000
              });
            }
              
          },"json");
        });


        $('#btnSendCode').click(function(){
          var encrypt = new JSEncrypt();
          // phone=$('.js-tel').val()&info="2"&type=type
          var phone=l_phone;
          encrypt.setPublicKey("MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4COVutRbOUfQNjvVOzwK49NzHIPRwwksnJ6QtdHwGmdUZiT2HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS0lfeBMqUcm+bY7aZ54wClm75RI90uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Ycr8S8O004ExtvQqu2QCl7Aai/WC4URIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx37Y9AMR0V94R13v4BFlMQDG+2REVJsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzLPYV0jsiit2AUkyURRLxEaZm2C0YYhfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DXkQIDAQAB");
          var data = encrypt.encrypt('phone='+phone+'&info='+"2"+'&type='+'account');

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





        // 添加支付宝账号
        $('#add').click(function(){
          layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: '516px',
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: $('#addaalipay')
          });
        })

        $('.submit3').click(function(){
          $.post("{{url('/api/wallet/add_account')}}",
          {
              token:token,
              alipay_name:$('.add1').val(),
              alipay_account:$('.add2').val(),        

          },function(res){
              console.log(res);
              if(res.status==1){
                layer.msg(res.message, {
                  offset: '15px'
                  ,icon: 1
                  ,time: 2000
                },function(){
                  window.location.reload();
                });
              }else{
                layer.msg(res.message, {
                  offset: '15px'
                  ,icon: 2
                  ,time: 3000
                });
              }
              
          },"json");
        });


        $('#tixianjilu').click(function(){
          $(this).attr('lay-href',"{{url('/user/putforward?')}}"+user_id)
        })



    

        
    });

  </script>

</body>
</html>





