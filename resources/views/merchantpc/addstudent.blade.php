<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加学生</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">班级信息</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">                
                <div class="layui-form-item school">
                    <label class="layui-form-label">选择学校</label>
                    <div class="layui-input-block">
                        <select name="schooltype" id="schooltype" lay-filter="schooltype">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item grade">
                    <label class="layui-form-label">选择年级</label>
                    <div class="layui-input-block">
                        <select name="grade" id="grade" lay-filter="grade">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item grade">
                    <label class="layui-form-label">选择班级</label>
                    <div class="layui-input-block">
                        <select name="class" id="class" lay-filter="class">
                            
                        </select>
                    </div>
                </div>                
            </div>

        </div>
    </div>
    <div class="layui-card">
      <div class="layui-card-header">学生信息</div>
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label">学生姓名</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入学生姓名" class="layui-input studentname">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">学生身份证号</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入学生身份证号" class="layui-input studentid" maxlength="18">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">学生学号</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入学生学号" class="layui-input studentnum">
                </div>
            </div>
            <div class="layui-form-item grade">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-block">
                    <select name="status" id="status" lay-filter="status">
                        <option value="">选择状态</option>
                        <option value="1">在校</option>
                        <option value="2">转校</option>
                    </select>
                </div>
            </div>
        </div>        
      </div>
    </div>
    <div class="layui-card">
      <div class="layui-card-header">家长信息</div>
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label">家长姓名</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入家长姓名" class="layui-input parentname">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">家长电话</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入家长电话" class="layui-input parentphone" maxlength="11">
                </div>
            </div>            
            <div class="layui-form-item grade">
                <label class="layui-form-label">与学生的关系</label>
                <div class="layui-input-block">
                    <select name="relationship" id="relationship" lay-filter="relationship">
                        <option value="">选择关系</option>
                        <option value="1">爸爸</option>
                        <option value="2">妈妈</option>
                        <option value="3">爷爷</option>
                        <option value="4">奶奶</option>
                        <option value="5">外公</option>
                        <option value="6">外婆</option>
                        <option value="7">家长</option>
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

<input type="hidden" class="schooltypeid" value="">
<input type="hidden" class="gradeid" value="">
<input type="hidden" class="classid" value="">
<input type="hidden" class="statusid" value="">
<input type="hidden" class="relationshipid" value="">

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
            // 选择年级
            $.ajax({
                url : "{{url('/api/school/teacher/grade/lst')}}",
                data : {token:token,store_id:$('.schooltypeid').val()},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].stu_grades_no + "'>"
                                + data.data[i].stu_grades_name + "</option>";
                        }   
                        $("#grade").html('') ;
                        $("#grade").append('<option value="">选择年级</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });         
        });
        form.on('select(grade)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.gradeid').val(category);  
            // 选择班级
            $.ajax({
                url : "{{url('/api/school/teacher/class/lst')}}",
                data : {token:token,store_id:$('.schooltypeid').val(),stu_grades_no:$('.gradeid').val()},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].stu_class_no + "'>"
                                + data.data[i].stu_class_name + "</option>";
                        }    
                        $("#class").html('');
                        $("#class").append('<option value="">选择班级</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });     
        });
        form.on('select(class)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.classid').val(category);        
        });
        form.on('select(status)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.statusid').val(category);          
        });
        form.on('select(relationship)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.relationshipid').val(category);          
        });
        




        $('.submit').on('click', function(){
            
            $.post("{{url('/api/school/teacher/stu/add')}}",
            {
                token:token,
                store_id:$('.schooltypeid').val(),
                stu_grades_no:$('.gradeid').val(), 
                stu_class_no:$('.classid').val(),

                student_name:$('.studentname').val(),
                student_no:$('.studentnum').val(),
                student_identify:$('.studentid').val(),
                status:$('.statusid').val(),

                student_user_name:$('.parentname').val(),
                student_user_mobile:$('.parentphone').val(),
                student_user_relation:$('.relationshipid').val()


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
