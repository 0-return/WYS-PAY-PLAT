<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>修改设备</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">

</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">添加设备</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">门店名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入年级名称" class="layui-input name" disabled="">
                    </div>
                </div>
                <div class="layui-form-item people">
                    <label class="layui-form-label">选择员工</label>
                    <div class="layui-input-block">
                        <select name="schooltype" id="schooltype" lay-filter="schooltype">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item device">
                    <label class="layui-form-label">设备归类</label>
                    <div class="layui-input-block">
                        <select name="classify" id="classify" lay-filter="classify">
                            <option value="">选择归类</option>
                            <option value="p">打印设备</option>
                            <option value="s">扫码盒子</option>
                            <option value="v">语音设备</option>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item device">
                    <label class="layui-form-label">设备类型</label>
                    <div class="layui-input-block">
                        <select name="type" id="type" lay-filter="type">
                            
                        </select>
                    </div>
                </div>


                <div class="layui-form-item">
                    <label class="layui-form-label">设备型号</label>
                    <div class="layui-input-block">
                        <input type="text" lay-verify="schoolname" autocomplete="off" placeholder="请输入设备型号" class="layui-input devicename">
                    </div>
                </div>
       
                <div class="layui-form-item">
                    <label class="layui-form-label">设备秘钥</label>
                    <div class="layui-input-block">
                        <input type="text" lay-verify="title" autocomplete="off" placeholder="请输入设备秘钥" class="layui-input device_key">

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
<input type="hidden" class="typeid" value="">
<input type="hidden" class="typename" value="">
<input type="hidden" class="classifyid" value="">
<input type="hidden" class="classifyname" value="">



<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    var id = localStorage.getItem("store_id");
    var store_id = localStorage.getItem("store_store_id");
    var store_name = localStorage.getItem("store_store_name");

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
            $.ajax({
                url : "{{url('/api/device/select')}}",
                data : {token:token,id:id},
                type : 'post',
                success : function(res) {
                    console.log(res);
                    $('.devicename').val(res.data.device_no);
                    $('.device_key').val(res.data.device_key);

                    $('.schooltypeid').val(res.data.merchant_id);
                    $('.typeid').val(res.data.device_type);
                    $('.typename').val(res.data.device_name);
                    $('.classifyid').val(res.data.type);

                    // 设备归类---显示
                    $('#classify option').each(function(){                        
                        if(res.data.type==$(this).val()){
                            $(this).attr('selected','selected');
                        }
                    });
                    // 选择员工---显示
                    $.ajax({
                        url : "{{url('/api/basequery/merchant_lists')}}",
                        data : {token:token,store_id:store_id},
                        type : 'post',
                        success : function(data) {
                            console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){

                                    optionStr += "<option value='" + data.data[i].merchant_id + "' "+((res.data.merchant_id==data.data[i].merchant_id)?"selected":"")+">"
                                + data.data[i].name + "</option>";                     
                                }    
                                $("#schooltype").append('<option value="">选择员工</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            layer.msg(res.message, {
                                offset: '15px'
                                ,icon: 2
                                ,time: 1000
                            });
                        }
                    });
                    // 选择设备类型---显示
                    $.ajax({
                        url : "{{url('/api/device/device_type')}}",
                        data : {token:token,type:res.data.type},
                        type : 'post',
                        success : function(data) {
                            console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){
                                    optionStr += "<option value='" + data.data[i].device_type + "' "+((res.data.device_type==data.data[i].device_type)?"selected":"")+">"
                                + data.data[i].device_name + "</option>";  
                                }    
                                $("#type").html('');
                                $("#type").append('<option value="">选择设备类型</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            layer.msg(res.message, {
                                offset: '15px'
                                ,icon: 2
                                ,time: 1000
                            });
                        }
                    });  


                },
                error : function(data) {
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 1000
                    });
                }
            });
            
            
            
        }

        form.on('select(schooltype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.schooltypeid').val(category);
            $('.schooltypename').val(categoryName);           
        });
        form.on('select(classify)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.classifyid').val(category);
            $('.classifyname').val(categoryName); 

            // 选择设备类型
            $.ajax({
                url : "{{url('/api/device/device_type')}}",
                data : {token:token,type:$('.classifyid').val()},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].device_type + "'>"
                                + data.data[i].device_name + "</option>";
                        }    
                        $("#type").html('');
                        $("#type").append('<option value="">选择设备类型</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 1000
                    });
                }
            });          
        });
        form.on('select(type)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.typeid').val(category);
            $('.typename').val(categoryName);           
        });




        $('.submit').on('click', function(){
            $.post("{{url('/api/device/up')}}",
            {
                token:token,
                id:id,
                store_id:store_id,
                type:$('.classifyid').val(),
                device_type:$('.typeid').val(),
                device_name:$('.typename').val(),
                device_no:$('.devicename').val(),
                device_key:$('.device_key').val(),
                merchant_id:$('.schooltypeid').val()

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
