<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>支付宝isv配置</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">支付宝isv配置</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">isv名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">isv联系电话</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">支付宝开放平台创建应用的appid</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">支付宝开放平台合作伙伴pid</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">软件生成的私钥</label>
                    <div class="layui-input-block">
                        <textarea name="desc" placeholder="" class="layui-textarea private_key"></textarea>
                    </div>
                </div>                
                <div class="layui-form-item">
                    <label class="layui-form-label">支付宝的应用公钥</label>
                    <div class="layui-input-block">
                        <textarea name="desc" placeholder="" class="layui-textarea public_key"></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">回调地址</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">网关</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">花呗自助签约链接</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">当面付自助签约链接</label>
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
    $.post("{{url('/api/user/alipay_isv_config')}}",
    {
        token:token,
        type:'2'

    },function(res){
        console.log(res);
        $('.layui-form .layui-form-item').eq(0).find('input').val(res.data.isv_name);
        $('.layui-form .layui-form-item').eq(1).find('input').val(res.data.isv_phone);
        $('.layui-form .layui-form-item').eq(2).find('input').val(res.data.app_id);
        $('.layui-form .layui-form-item').eq(3).find('input').val(res.data.alipay_pid);
        $('.layui-form .layui-form-item').eq(4).find('textarea').html(res.data.rsa_private_key);
        $('.layui-form .layui-form-item').eq(5).find('textarea').html(res.data.alipay_rsa_public_key);
        $('.layui-form .layui-form-item').eq(6).find('input').val(res.data.callback);
        $('.layui-form .layui-form-item').eq(7).find('input').val(res.data.notify);
        $('.layui-form .layui-form-item').eq(8).find('input').val(res.data.hb_pay_url);
        $('.layui-form .layui-form-item').eq(9).find('input').val(res.data.m_pay_url);
    },"json");
        


    $('.submit').on('click', function(){
        console.log($('.private_key').val());
        $.post("{{url('/api/user/alipay_isv_config')}}",
        {
            token:token,
            type:"1",
            app_id:$('.layui-form .layui-form-item').eq(2).find('input').val(),
            isv_name:$('.layui-form .layui-form-item').eq(0).find('input').val(),
            isv_phone:$('.layui-form .layui-form-item').eq(1).find('input').val(),

            alipay_pid:$('.layui-form .layui-form-item').eq(3).find('input').val(),
            rsa_private_key:$('.layui-form .layui-form-item').eq(4).find('textarea').val(),
            alipay_rsa_public_key:$('.layui-form .layui-form-item').eq(5).find('textarea').val(),
            callback:$('.layui-form .layui-form-item').eq(6).find('input').val(),
            notify:$('.layui-form .layui-form-item').eq(7).find('input').val(),
            hb_pay_url:$('.layui-form .layui-form-item').eq(8).find('input').val(),
            m_pay_url:$('.layui-form .layui-form-item').eq(9).find('input').val()
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
