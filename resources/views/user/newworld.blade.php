<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>新大陆配置</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">新大陆配置</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">机构号</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">机构密钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">微信公众号apppid</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">微信公众号密钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
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
    $.post("{{url('/api/user/new_land_config')}}",
    {
        token:token,
        type:'2'

    },function(res){
        console.log(res);
        $('.layui-form .layui-form-item').eq(0).find('input').val(res.data.org_no);
        $('.layui-form .layui-form-item').eq(1).find('input').val(res.data.nl_key);
        $('.layui-form .layui-form-item').eq(2).find('input').val(res.data.wx_appid);
        $('.layui-form .layui-form-item').eq(3).find('input').val(res.data.wx_secret);
    },"json");
        


    $('.submit').on('click', function(){
        console.log($('.private_key').val());
        $.post("{{url('/api/user/new_land_config')}}",
        {
            token:token,
            type:"1",
            org_no:$('.layui-form .layui-form-item').eq(0).find('input').val(),
            nl_key:$('.layui-form .layui-form-item').eq(1).find('input').val(),            
            wx_appid:$('.layui-form .layui-form-item').eq(2).find('input').val(),            
            wx_secret:$('.layui-form .layui-form-item').eq(3).find('input').val(),            
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
