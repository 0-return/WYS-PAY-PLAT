<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>支付宝授权</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    /*.qrcode{width:560px;height:820px;background: url("../../layuiadmin/layui/images/bg-katai.png") no-repeat;background-size: 100%;margin:0 auto;}*/
    .qrcode{text-align: center;padding:50px 0;position: relative;height:150px;}
    .qrcode img.bg_img{width:450px;height:auto;}
    .img{position: absolute;left: 50%;margin-left: -71px;top: 20%;margin-top: -6px;width: 142px;}
    .img canvas{width: 100%;border-radius: 8px;}
    .schoolname{position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);margin-top: 177px; color: #fff;}
    .g_c_name{display:none;width:184px;height:140px;background:url("../../layuiadmin/layui/images/banji-bg.png") no-repeat;background-size: 100%; position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);margin-top: 177px; color: #fff;margin-top: 276px;margin-left: .5%;color:#2aa1f7;}
    .logo{position: absolute;left: 50%;bottom: 0;transform: translate(-140%, -50%);width: 120px;margin-bottom: 38px;}
    .logo img{width:100%;}
    .content{position: absolute;left: 50%;bottom: 0;transform: translate(40%, -50%);width: 120px;margin-bottom: 50px;color:#fff;font-size: 12px;}
    .desc{position: absolute;left: 50%;transform: translate(-50%, -50%);}
    .desc div:nth-child(1){margin:30px 0 0;}
    .desc div:nth-child(2){margin:5px 0;}
    .desc a{margin:20px 0 0 50px;}
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
                <div class="layui-card-header">支付宝授权</div>

                <div class="layui-card-body" style="height:400px;">
                  <div class="layui-btn-container" style="font-size:14px;height: 200px;">
                  <div class="qrcode">
                    <div class="img" id="code"></div>
                  </div> 
                  <div class="desc">
                    <div>方式一：请使用企业支付宝扫描二维码</div>
                    <div>方式二：登录支付宝账号授权</div>
                    <a class="layui-btn login_alipay" lay-href="">登录企业支付宝</a> 
                  </div> 
                  
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
 <input type="hidden" class="js_school">
 <input type="hidden" class="js_grades">
 <input type="hidden" class="js_class">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
    <script>
    var token = localStorage.getItem("token");
    var str=location.search;
    var store_id=str.split('?')[1];

    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form', 'table'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,table = layui.table
            ,form = layui.form;


        // 生成二维码
        $.post("{{url('/api/school/teacher/ali/auth/url')}}",
        {
          token:token,
          store_id:store_id
        },function(res){
          console.log(res);
          url=res.data.url;
          $('#code').qrcode(url);
          $('#code').html('');
          $('#code').qrcode(url);
          $('.login_alipay').attr('lay-href',url);
        },"json");
           
    });

  </script>

</body>
</html>