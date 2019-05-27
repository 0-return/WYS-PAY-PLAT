<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>学生列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .jf{background-color: #429488;}
  </style>
</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">

        <div class="layui-fluid">
          <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
              <div class="layui-card"> 
                <div class="layui-card-header">学生列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <div>
                      <a class="layui-btn layui-btn-primary" lay-href="{{url('/merchantpc/addstudent')}}" style="display: inline-block;width: 100px;">添加学生</a>
                      
                      <!-- <form id= "uploadForm" style="display: inline-block;padding-left:50px;">            
                        <div class="layui-btn name" style="margin-right:20px;">选择所需上传文件</div>                  
                        <input type="file" name="file" id="file" style="display: none;"/> 
                        <input type="button" value="确定上传" id="Upload" class="layui-btn"/>  
                      </form> -->

                    </div>

                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="schooltype" id="schooltype" lay-filter="schooltype">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="grade" id="grade" lay-filter="grade">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="class" id="class" lay-filter="class">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="status" id="status" lay-filter="status">
                              <option value="">选择状态</option>
                              <option value="1">在校</option>
                              <option value="2">转校</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:500px;display: inline-block;">
                      <div class="layui-form-item">
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="id" placeholder="请输入学生姓名" autocomplete="off" class="layui-input">
                            </div>
                          </div>                          
                          
                          <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-list" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                          </div>
                        </div>
                    </div>

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>

                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    <a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-event="edit" lay-href="{{url('/merchantpc/editstudent')}}">学生修改</a>
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs jf" lay-event="pay">缴费记录</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="del">删除</a>
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
    <script>
    var token = localStorage.getItem("token");
    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form', 'table'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,table = layui.table
            ,form = layui.form;

        // 选择学校
        $.ajax({
            url : "{{url('/api/school/teacher/lst')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                // console.log(data);
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
        // 选择年级
        $.ajax({
            url : "{{url('/api/school/teacher/grade/lst')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                // console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].stu_grades_no + "'>"
                            + data.data[i].stu_grades_name + "</option>";
                    }    
                    $("#grade").append('<option value="">选择年级</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
        // 选择班级
        $.ajax({
            url : "{{url('/api/school/teacher/class/lst')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                // console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].stu_class_no + "'>"
                            + data.data[i].stu_class_name + "</option>";
                    }    
                    $("#class").append('<option value="">选择班级</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });



        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/school/teacher/stu/lst')}}"
            ,method: 'post'
            ,where:{
              token:token,              
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 160
            ,cols: [[
                {field:'school_name', title: '学校名称'}
                ,{field:'grade_name', title: '年级名称'}
                ,{field:'class_name', title: '班级名称'}
                ,{field:'student_name', title: '学生姓名'}
                ,{field:'student_no', title: '学生学号'}
                ,{field:'student_user_name', title: '家长姓名'}
                ,{field:'student_user_mobile', title: '家长联系方式'}
                ,{align:'center',width:210, fixed: 'right', toolbar: '#table-content-list',title: '操作'}
            ]]
            ,response: {
                statusName: 'status' //数据状态的字段名称，默认：code
                ,statusCode: 1 //成功的状态码，默认：0
                ,msgName: 'message' //状态信息的字段名称，默认：msg
                ,countName: 't' //数据总数的字段名称，默认：count
                ,dataName: 'data' //数据列表的字段名称，默认：data
              } 
            ,done: function(res, curr, count){              
              console.log(res); 
            }

        });



        table.on('tool(test-table-page)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
          var e = obj.data; //获得当前行数据
          var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
          var tr = obj.tr; //获得当前行 tr 的DOM对象
          console.log(e);
          localStorage.setItem('s_store_id', e.store_id);
          localStorage.setItem('s_stu_grades_no', e.stu_grades_no);
          localStorage.setItem('s_stu_class_no', e.stu_class_no);
          localStorage.setItem('s_status', e.status);
          localStorage.setItem('s_student_user_relation', e.student_user_relation);

          localStorage.setItem('s_student_no', e.student_no);
          localStorage.setItem('s_student_identify', e.student_identify);
          localStorage.setItem('s_student_name', e.student_name);



          if(layEvent === 'pay'){
            layer.open({
              type: 2,
              title: e.student_name+'--缴费记录',
              shade: false,
              maxmin: true,
              area: ['90%', '90%'],
              content: "{{url('/merchantpc/paystudent?')}}"+e.student_no
            });
          }else if(layEvent === 'del'){
            layer.confirm('确定删除此条信息吗?', function(index){
              obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
              layer.close(index);
              $.ajax({
                url : "{{url('/api/school/teacher/stu/del')}}",
                data : {token:token,id:e.id},
                type : 'post',
                success : function(data) {
                  console.log(data);
                  layer.msg(data.message, {
                    offset: '15px'
                    ,icon: 1
                    ,time: 1000
                  });  
                },
                error : function(data) {
                  layer.msg(data.message, {
                    offset: '15px'
                    ,icon: 2
                    ,time: 3000
                  });
                }
            });

            });
          }
        });

        
        // 选择学校
        form.on('select(schooltype)', function(data){
          var store_id = data.value;
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_id: store_id
            }
          });
        });
        // 选择年级
        form.on('select(grade)', function(data){
          var stu_grades_no = data.value;
          //执行重载
          table.reload('test-table-page', {
            where: { 
              stu_grades_no: stu_grades_no
            }
          });
        });
        // 选择班级
        form.on('select(class)', function(data){
          var stu_class_no = data.value;
          //执行重载
          table.reload('test-table-page', {
            where: { 
              stu_class_no: stu_class_no
            }
          });
        });
        // 选择状态
        form.on('select(status)', function(data){
          var status = data.value;
          //执行重载
          table.reload('test-table-page', {
            where: { 
              status: status
            }
          });
        });


        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var value = data.field.id;
          console.log(value);          
          //执行重载
          table.reload('test-table-page', {
            where: { 
              student_name: value
            }
          });
        });


        $('.name').click(function(){
          $("#file").click();
        })
        // 获取文件名
        var file = $('#file');
        file.on('change', function( e ){
            //e.currentTarget.files 是一个数组，如果支持多个文件，则需要遍历
            var name = e.currentTarget.files[0].name;
            console.log( name );
            $('.name').html(name);
        });
        // excel文件导入
        $('#Upload').click(function(){
          var formData = new FormData($( "#uploadForm" )[0]);  
          console.log(formData);
          $.ajax({   
            url: "{{url('/api/school/teacher/stu/import?token=')}}"+token,  
            type: 'POST',  
            data: formData,  
            async: false,  
            cache: false,  
            contentType: false,  
            processData: false,  
            success: function (res) {  
                console.log(res);
              if(res.status==1){
                layer.msg(res.message, {
                  offset: '15px'
                  ,icon: 1
                  ,time: 1000
                },function(){
                  window.location.reload()
                });
              }else{
                layer.msg(res.message, {
                  offset: '15px'
                  ,icon: 2
                  ,time: 1000
                });
              }
                
            },  
            error: function (res) {  
              
                
            }  
         });

        });


    });

  </script>

</body>
</html>