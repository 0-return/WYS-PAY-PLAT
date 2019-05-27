<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>缴费模板</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .cur{color:#009688;}
    /*.laytable-cell-1-school_icon{height:100%;}*/
  </style>
  <style>
    /*.qrcode{width:560px;height:820px;background: url("../../layuiadmin/layui/images/bg-katai.png") no-repeat;background-size: 100%;margin:0 auto;}*/
    .qrcode{text-align: center;padding:50px 0;position: relative;}
    .qrcode img.bg_img{width:450px;height:auto;}
    .img{position: absolute;left: 50%;margin-left: -71px;top: 50%;margin-top: -6px;width: 142px;}
    .img canvas{width: 100%;border-radius: 8px;}
    .schoolname{position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);margin-top: 177px; color: #fff;}
    .g_c_name{display:none;width:184px;height:140px;background:url("../../layuiadmin/layui/images/banji-bg.png") no-repeat;background-size: 100%; position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);margin-top: 177px; color: #fff;margin-top: 276px;margin-left: .5%;color:#2aa1f7;}
    .logo{position: absolute;left: 50%;bottom: 0;transform: translate(-140%, -50%);width: 120px;margin-bottom: 38px;}
    .logo img{width:100%;}
    .content{position: absolute;left: 50%;bottom: 0;transform: translate(40%, -50%);width: 120px;margin-bottom: 50px;color:#fff;font-size: 12px;}
  </style>

</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">

        <div class="layui-fluid">
          <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
              <div class="layui-card" style="height:350px;"> 
                <div class="qrcode">
                  <div class="img" id="code"></div>
                </div>
                <div style="text-align: center;padding-top: 100px;"><span class="name"></span>(<span class="code"></span>)</div>
                <div style="text-align: center;padding-top: 10px;">请用支付宝或者微信扫码注册</div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <input type="hidden" class="user_id">
  <input type="hidden" class="status">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
   <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>

    <script>
    var token = localStorage.getItem("Usertoken");
    var s_code = localStorage.getItem("s_code");
    var s_agentname = localStorage.getItem("s_agentname");
    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form', 'table','laydate'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,table = layui.table
            ,form = layui.form
            ,laydate = layui.laydate;
        $('.name').html(s_agentname);
        $('.code').html(s_code);
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })
        // 生成二维码
        var protocolStr = document.location.protocol;
        var str= document.domain;
        
        url=protocolStr+"//"+str;
        
        $('#code').qrcode(url+"/school/register"+"?&register_type="+'school'+"&s_code="+s_code);

    });


  </script>

</body>
</html>