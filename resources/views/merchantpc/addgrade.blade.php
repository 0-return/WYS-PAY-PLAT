<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加年级</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">

</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">添加年级</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item school">
                    <label class="layui-form-label">选择学校</label>
                    <div class="layui-input-block">
                        <select name="schooltype" id="schooltype" lay-filter="schooltype">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">年级名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入年级名称" class="layui-input name">
                    </div>
                </div>
       
                <div class="layui-form-item">
                    <label class="layui-form-label">年级描述</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" lay-verify="title" autocomplete="off" placeholder="请输入年级描述" class="layui-input gradedesc">

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

<input type="hidden" class="schooltypeid" value="">
<input type="hidden" class="schooltypename" value="">

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

 
        getBoards();
       
        function getBoards(){ 
            // 选择学校
            $.ajax({
                url : "{{url('/api/school/teacher/lst')}}",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].store_id + "'>"
                                + data.data[i].school_name + "</option>";
                        }    
                        $("#schooltype").append('<option value="">选择学校</option>'+optionStr);
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
            $('.schooltypeid').val(category);
            $('.schooltypename').val(categoryName);           
        });




        $('.submit').on('click', function(){
            $.post("{{url('/api/school/teacher/grade/add')}}",
            {
                token:token,
                store_id:$('.schooltypeid').val(),
                stu_grades_name:$('.name').val(),
                stu_grades_desc:$('.gradedesc').val()

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
