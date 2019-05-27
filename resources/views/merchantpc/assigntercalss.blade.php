<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加班级</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">添加班级</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item school">
                    <label class="layui-form-label">教师职务</label>
                    <div class="layui-input-block">
                        <select name="schooltype" id="schooltype" lay-filter="schooltype">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item grade">
                    <label class="layui-form-label">选择老师</label>
                    <div class="layui-input-block">
                        <select name="grade" id="grade" lay-filter="grade">
                            
                        </select>
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

<input type="hidden" class="zhiwu" value="">
<input type="hidden" class="teacher" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("token");
    

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
        var store_id="{{$_GET['store_id']}}";
        var merchant_id="{{$_GET['merchant_id']}}";
        var stu_class_no="{{$_GET['stu_class_no']}}";
        var name="{{$_GET['name']}}";
 
        getBoards();
       
        function getBoards(){ 
            // 选择职务
            $.ajax({
                url : "{{url('/api/school/teacher/ter/typelst')}}",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].type + "'>"
                                + data.data[i].name + "</option>";
                        }    
                        $("#schooltype").append('<option value="">选择职务</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 选择教师
            $.ajax({
                url : "{{url('/api/merchant/merchant_lists')}}",
                data : {token:token,store_id:store_id},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].merchant_id + "'>"
                                + data.data[i].name + "</option>";
                        }    
                        $("#grade").append('<option value="">选择教师</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            
        }

        form.on('select(schooltype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.zhiwu').val(category);          
        });
        form.on('select(grade)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.teacher').val(category);          
        });




        $('.submit').on('click', function(){
            $.post("{{url('/api/school/teacher/ter/relate')}}",
            {
                token:token,
                store_id:store_id,
                merchant_id:$('.teacher').val(), 
                stu_class_no:stu_class_no,
                type:$('.zhiwu').val()

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
