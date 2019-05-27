<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>收银员添加</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
    .layui-card-header{width:80px;text-align: right;float:left;}
    .layui-card-body{margin-left:28px;}
    .layui-upload-img{width: 92px; height: 92px; margin: 0 10px 10px 0;}

    .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;width: 92px !important;font-size: 10px !important;text-align: center !important;}
    .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
    .layui-upload-list{width: 100px;height:96px;overflow: hidden;}
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
    
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header" style="width:auto !important">添加收银员&nbsp;&nbsp;&nbsp;<span class="zong_school_name"></span></div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item school">
                    <label class="layui-form-label">员工角色</label>
                    <div class="layui-input-block">
                        <select name="agent" id="agent" lay-filter="agent">
                            <option value="">选择员工角色</option>
                            <option value="1">店长</option>
                            <option value="2">收银员</option>
                        </select>
                    </div>
                </div>
               
                <div class="layui-form-item">
                    <label class="layui-form-label">员工姓名</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入员工姓名" class="layui-input name">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">手机号</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入员工手机号" class="layui-input phone">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">密码</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入密码" class="layui-input password">
                    </div>
                </div>



                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">确定提交</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<input type="hidden" class="agentid">
<input type="hidden" class="agentname">
<input type="hidden" class="pass" value="000000">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Publictoken");
    var str=location.search;
    var store_id=str.split('?')[1];


    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects','element'], function(){
        var $ = layui.$ 
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form
            ,upload = layui.upload
            ,formSelects = layui.formSelects;
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/mb/login')}}"; 
            }
        }) 
        form.on('select(agent)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.agentid').val(category);
            $('.agentname').val(categoryName);           
        });
        
        $('.submit').on('click', function(){
            if($('.password').val()==''){
                $.post("{{url('/api/merchant/add_merchant')}}",
                {
                    token:token,
                    store_id:store_id,
                    name:$('.name').val(),
                    phone:$('.phone').val(),
                    password:$('.pass').val(),
                    type:$('.agentid').val()

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
            }else{
                $.post("{{url('/api/merchant/add_merchant')}}",
                {
                    token:token,
                    store_id:store_id,
                    name:$('.name').val(),
                    phone:$('.phone').val(),
                    password:$('.password').val(),
                    type:$('.agentid').val()

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

            }
            
        });

    });
</script>

</body>
</html>
