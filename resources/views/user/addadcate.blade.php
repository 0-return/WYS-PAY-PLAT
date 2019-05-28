<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加广告管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/formSelects-v4.css')}}" media="all">
    <style>
        .icon-close{display: none;}
        #demo1 img{width: 100%;height: 100%;}
        .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
        .img_box{position: relative;width:13%;height:10%;display: inline-block; margin-right: 10px;}
        .img_box span{position: absolute;right:0;top:0;font-size: 30px;background: #fff;cursor: pointer;}
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">广告管理</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group"> 
                <div class="layui-form-item">
                    <label class="layui-form-label">分类标题</label>
                    <div class="layui-input-block">
                        <input type="title" placeholder="请输入分类标题:" class="layui-input title">
                    </div>
                </div>
                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit">确定提交</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                        </div>
                    </div>
                </div>           
            </div>


        </div>
    </div>
   
    
</div>

<input type="hidden" class="user_id" value="">
<input type="hidden" class="position_id" value="">
<input type="hidden" class="position_name" value="">
<input type="hidden" class="store-id" value="">

<input type="hidden" class="classname" value="">
<input type="hidden" class="templateid" value="">
<input type="hidden" class="student_code" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<!-- <script src="{{asset('/layuiadmin/modules/formSelects.js')}}"></script> -->
<script>
    var token = localStorage.getItem("Usertoken");
    
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
        formSelects.render('position');
        formSelects.btns('position', []);
        formSelects.render('range');
        formSelects.btns('range', []);
        formSelects.render('store');
        formSelects.btns('store', []);
        adarr = [];
        $('.submit').on('click', function(){
            
          
            var adarrJson=JSON.stringify(adarr);//转化成json格式
            $.post("{{url('/api/adcate/adcate_add')}}",
            {
                token:token,
                title:$('.title').val(),

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
