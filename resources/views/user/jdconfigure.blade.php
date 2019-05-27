<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>京东金融配置</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">京东金融配置</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">服务商商户号</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">md5加签密钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">3des加密密钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">系统名称ID</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">支付宝app ID</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input"> 
                    </div>
                </div>                
                <div class="layui-form-item">
                    <label class="layui-form-label">微信app ID</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input"> 
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">微信密钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>



                

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">保存</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects','element'], function(){
        var $ = layui.$ 
            ,admin = layui.admin
            ,element = layui.element
            ,form = layui.form
    // 未登录,跳转登录页面
    $(document).ready(function(){        
        if(token==null){
            window.location.href="{{url('/user/login')}}"; 
        }
    })
    $.post("{{url('/api/user/jd_config')}}",
    {
        token:token,
        type:'2'

    },function(res){
        console.log(res);
        $('.layui-form .layui-form-item').eq(0).find('input').val(res.data.agentNo);
        $('.layui-form .layui-form-item').eq(1).find('input').val(res.data.store_md_key);
        $('.layui-form .layui-form-item').eq(2).find('input').val(res.data.store_des_key);
        $('.layui-form .layui-form-item').eq(3).find('input').val(res.data.systemId);
        $('.layui-form .layui-form-item').eq(4).find('input').val(res.data.ali_appid);
        $('.layui-form .layui-form-item').eq(5).find('input').val(res.data.wx_appid);
        $('.layui-form .layui-form-item').eq(6).find('input').val(res.data.wx_secret);
    },"json");
        


    $('.submit').on('click', function(){
        console.log($('.private_key').val());
        $.post("{{url('/api/user/jd_config')}}",
        {
            token:token,
            type:"1",
            agentNo:$('.layui-form .layui-form-item').eq(0).find('input').val(),
            store_md_key:$('.layui-form .layui-form-item').eq(1).find('input').val(),
            store_des_key:$('.layui-form .layui-form-item').eq(2).find('input').val(),
            systemId:$('.layui-form .layui-form-item').eq(3).find('input').val(),
            ali_appid:$('.layui-form .layui-form-item').eq(4).find('input').val(),
            wx_appid:$('.layui-form .layui-form-item').eq(5).find('input').val(),
            wx_secret:$('.layui-form .layui-form-item').eq(6).find('input').val(),
        },function(res){
            console.log(res);

            if(res.status==1){
                layer.msg(res.message, {
                    offset: '15px'
                    ,icon: 1
                    ,time: 3000
                });
            }else{
                layer.alert(res.message, {icon: 2});
            }

        },"json");

    });

    });
</script>

</body>
</html>
