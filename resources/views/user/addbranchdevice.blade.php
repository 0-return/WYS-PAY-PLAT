<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加分店</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">

</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">添加分店</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                
       
                <div class="layui-form-item">
                    <label class="layui-form-label">分店名称</label>
                    <div class="layui-input-block">
                        <input type="text" lay-verify="title" autocomplete="off" placeholder="请输入分店名称" class="layui-input store_name">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">所在地</label>
                    <div class="layui-input-block">
                        <div class="layui-inline">
                            <select name="province" lay-filter="filterProvince" id="province">
                                
                            </select>
                        </div>
                        <div class="layui-inline">
                            <select name="city" lay-filter="filterCity" id="city">
                                
                            </select>
                        </div>
                        <div class="layui-inline">
                            <select name="area" lay-filter="filterArea" id="area">
                                
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">详细地址</label>
                    <div class="layui-input-block">
                        <input type="text" lay-verify="title" autocomplete="off" placeholder="请输入详细地址" class="layui-input store_address">
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

<input type="hidden" class="provincecode" value="">
<input type="hidden" class="provincename" value="">
<input type="hidden" class="citycode" value="">
<input type="hidden" class="cityname" value="">
<input type="hidden" class="areacode" value="">
<input type="hidden" class="areaname" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    var store_id = localStorage.getItem("store_store_id");
    var store_name = localStorage.getItem("store_store_name");
    var id="{{$_GET['pid']}}";

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form
            ,upload = layui.upload
            ,formSelects = layui.formSelects;

            var src=$('#demo1').attr('src');

        $('.name').val(store_name);
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })
        getBoards();
       
        function getBoards(){ 
            
            // 省
            $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:'1'},
                type : 'get',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "'>"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#province").append('<option value="">请选择省</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            
            
        }

        form.on('select(filterProvince)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.provincecode').val(category);
            $('.provincename').val(categoryName);
            $("#city").html('');
            $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:category},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "'>"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#city").append('<option value="">请选择市</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
        });
        form.on('select(filterCity)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.citycode').val(category);
            $('.cityname').val(categoryName);
            $("#area").html('');
            $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:category},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "'>"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#area").append('<option value="">请选择县/区</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
        });
        form.on('select(filterArea)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.areacode').val(category);
            $('.areaname').val(categoryName);           
        });

       




        $('.submit').on('click', function(){
            $.post("{{url('/api/user/add_sub_store')}}",
            {
                token:token,
                pid:id,
                store_name:$('.store_name').val(),
                store_address:$('.store_address').val(),

                province_code:$('.provincecode').val(),
                city_code:$('.citycode').val(),
                area_code:$('.areacode').val()

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
