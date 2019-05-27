<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>银行卡</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>   
    .layui-upload-img{width: 92px; height: 92px; margin: 0 10px 10px 0;}

    .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;/*width: 92px !important;*/font-size: 10px !important;text-align: center !important;}
    .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
    .layui-upload-list{width: 100px;height:96px;overflow: hidden;}
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
    .bind{height:38px;line-height: 38px;text-align: right;}
    .cur{color:#009688;font-size:50px;}
    </style>
</head>
<body>

<div class="layui-fluid" style="padding: 0">
    <div class="layui-card">
        <!-- <div class="layui-card-header">证照信息</div> -->
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="component-form-group" style="padding: 50px 0;text-align: center;">
                <i class="layui-icon layui-icon-ok-circle cur"></i>
                <p style="padding:20px;">资料已提交,等待审核</p>
                <button class="layui-btn submit site-demo-active" data-type="tabChange">确认</button>
            </div>
        </div>
    </div>
   
</div>





<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("rz_token");
    

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects','laydate'], function(){
        var $ = layui.$ 
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form
            ,upload = layui.upload
            ,formSelects = layui.formSelects;

        

      

        $('.submit').on('click', function(){
     
           // window.location.href="{{url('/phone/identsecond')}}";   
             
        }); 

    });
</script>

</body>
</html>
