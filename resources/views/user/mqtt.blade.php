<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>mqtt推送</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
        .img{width:130px;height:90px;overflow: hidden;}
        .img img{width:100%;height:100%;}
        .layui-layer-nobg{width: none !important;}
        /*.layui-layer-content{width:600px;height:550px;}*/
        .layui-card-header{width:200px;text-align: left;float:left;}
        .layui-card-body{margin-left:28px;}
        .layui-upload-img{width: 120px; height: 120px; /*margin: 0 10px 10px 0;*/}

        .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;width: auto !important;font-size: 10px !important;text-align: center !important;}
        .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
        .layui-upload-list{width: 120px;height:120px;overflow: hidden;margin: 10px auto;}
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
        
    </style>
</head>
<body>

<div class="layui-fluid">
    
    <div class="layui-card">
      <div class="layui-card-header">MQTT推送</div>
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-form">
            
            <div class="layui-form-item">
                <label class="layui-form-label">阿里云AccessKey ID</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="阿里云AccessKey ID" class="layui-input templatename">
                </div>
            </div>            
            <div class="layui-form-item">
                <label class="layui-form-label">阿里云Access Key Secret</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="阿里云Access Key Secret" class="layui-input templatedesc">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">公网接入点地址</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="公网接入点地址" class="layui-input templatedesc">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">端口号</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="端口号" class="layui-input templatedesc">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">MQTT实例 ID</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="MQTT实例 ID" class="layui-input templatedesc">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">Group ID</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="Group ID" class="layui-input templatedesc">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">主题Topic</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="主题Topic" class="layui-input templatedesc">
                </div>
            </div>
            
            
            

            <div class="layui-form-item layui-layout-admin">
                <div class="layui-input-block">
                    <div class="layui-footer" style="left: 0;">
                        <button class="layui-btn submit">确定提交</button>
                    </div>
                </div>
            </div>
        </div>        
      </div>
    </div>
   
</div>

<input type="hidden" class="schooltypeid" value="">
<input type="hidden" class="schooltypename" value="">


<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'table','form','upload'], function(){
        var $ = layui.$   
            admin = layui.admin         
            ,table = layui.table
            ,element = layui.element
            ,upload = layui.upload
            ,form = layui.form;

            element.render();
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })
        


        $.post("{{url('/api/user/mqtt_config')}}",
        {
            token:token,
        },function(res){
            console.log(res);
            $('.layui-form .layui-form-item').eq(0).find('input').val(res.data.access_key_id);
            $('.layui-form .layui-form-item').eq(1).find('input').val(res.data.access_key_secret);
            $('.layui-form .layui-form-item').eq(2).find('input').val(res.data.server);
            $('.layui-form .layui-form-item').eq(3).find('input').val(res.data.port);
            $('.layui-form .layui-form-item').eq(4).find('input').val(res.data.instance_id);
            $('.layui-form .layui-form-item').eq(5).find('input').val(res.data.group_id);
            $('.layui-form .layui-form-item').eq(6).find('input').val(res.data.topic);
        },"json");

        


        

    

        

        

        $('.submit').on('click', function(){

            $.post("{{url('/api/user/mqtt_config')}}",
            {
                token:token,
                access_key_id:$('.layui-form .layui-form-item').eq(0).find('input').val(),
                access_key_secret:$('.layui-form .layui-form-item').eq(1).find('input').val(),
                server:$('.layui-form .layui-form-item').eq(2).find('input').val(),
                port:$('.layui-form .layui-form-item').eq(3).find('input').val(),
                instance_id:$('.layui-form .layui-form-item').eq(4).find('input').val(),
                group_id:$('.layui-form .layui-form-item').eq(5).find('input').val(),
                topic:$('.layui-form .layui-form-item').eq(6).find('input').val()

            },function(res){
                console.log(res);
                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 3000
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


    });
</script>
</body>
</html>
