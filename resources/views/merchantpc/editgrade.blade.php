<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>年级修改</title>
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

<div class="layui-form">
    <div class="layui-card">
        <div class="layui-card-body" style="margin:0">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item" id="school">
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
                      <!--   </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>

<input type="hidden" class="schooltypeid" value="">
<input type="hidden" class="schooltypename" value="">
<input type="hidden" class="stu_grades_no" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("token");
    var stu_grades_no = localStorage.getItem("stu_grades_no");
    var store_id = localStorage.getItem("store_id");
   

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

        $('.schooltypeid').val(store_id);
        getBoards();

        form.on('select(schooltype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.schooltypeid').val(category);
            $('.schooltypename').val(categoryName);           
        });
       
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

                            optionStr += "<option value='" + data.data[i].store_id + "' "+((store_id==data.data[i].store_id)?"selected":"")+">"
                                + data.data[i].school_name + "</option>";
                        }    
                        $("#schooltype").append('<option value="">选择学校</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });  


            // 显示修改的内容
            $.post("{{url('/api/school/teacher/grade/show')}}",
            {
                token:token,
                stu_grades_no:stu_grades_no

            },function(res){
                console.log(res);
                $('.name').val(res.data.stu_grades_name);
                $('.gradedesc').val(res.data.stu_grades_desc);  
                $('#school .layui-input-block .layui-form-select .layui-select-title input').val(res.data.school_name);
                $('.stu_grades_no').val(res.data.stu_grades_no);
            },"json");          
            
        }

        




        $('.submit').on('click', function(){
            $.post("{{url('/api/school/teacher/grade/save')}}",
            {
                token:token,
                stu_grades_no:$('.stu_grades_no').val(),
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
