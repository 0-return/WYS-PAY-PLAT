<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加代理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">

</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">添加代理</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">上级代理商</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid agentname"></div>
                    </div>
                </div> 
                <div class="layui-form-item">
                    <label class="layui-form-label">代理商姓名</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入代理商姓名" class="layui-input name">
                    </div>
                </div>       
                <div class="layui-form-item">
                    <label class="layui-form-label">代理商手机号</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" lay-verify="title" autocomplete="off" placeholder="请输入代理商手机号" class="layui-input phone">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">登陆密码</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" lay-verify="title" autocomplete="off" placeholder="请输入登陆密码" class="layui-input passward">
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
    var s_code = localStorage.getItem("s_code");
    var user_id = localStorage.getItem("dataid");
    var agentname = localStorage.getItem("agentname");

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,element = layui.element
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })
        $('.agentname').html(agentname);

        $('.submit').on('click', function(){
            $.post("{{url('/api/user/add_sub_user')}}",
            {
                token:token,
                user_name:$('.name').val(),
                phone:$('.phone').val(),
                password:$('.passward').val(),
                user_id:user_id
            },function(res){
                console.log(res);
                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 1000
                    });
                }else{
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 1000
                    });
                }
            },"json");

        });

    });
</script>
</body>
</html>
