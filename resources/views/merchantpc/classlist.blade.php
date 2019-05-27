<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>班级列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
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
                <div class="layui-card-header">班级列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <a class="layui-btn layui-btn-primary" lay-href="{{url('/merchantpc/addclass')}}" style="margin-bottom:0">添加班级</a>
                    
                    <div class="layui-form" lay-filter="component-form-group" style="width:250px;display: inline-block;">
                      <div class="layui-form-item">                        
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="schooltype" id="schooltype" lay-filter="schooltype">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:250px;display: inline-block;">
                      <div class="layui-form-item">                        
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="grade" id="grade" lay-filter="grade">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <!-- <button class="layui-btn layuiadmin-btn-list" lay-submit lay-filter="LAY-app-contlist-search">
                      <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button> -->

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>

                  <script type="text/html" id="table-content-list">
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="export">excel导入学生资料</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-event="edit" lay-href="{{url('/merchantpc/editclass')}}">班级修改</a>
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs t_list" lay-event="teacherlist" lay-href="{{url('/user/teacherlist?')}}">班级教师列表</a>
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
        // 选择年级       
        $.ajax({
            url : "{{url('/api/school/teacher/grade/lst')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                console.log(data);
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


        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/school/teacher/class/lst')}}"
            ,method: 'post'
            ,where:{
              token:token,              
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
                {field:'school_name', title: '学校名称'}
                ,{field:'grade_no', title: '年级名称'}
                ,{field:'stu_class_name',  title: '班级名称'}
                ,{field:'stu_class_desc',  title: '班级描述'}
                ,{align:'center',width:300, fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
          localStorage.setItem('stu_class_no', e.stu_class_no);
          localStorage.setItem('stu_grades_no', e.stu_grades_no);
          localStorage.setItem('school_name', e.school_name);
          localStorage.setItem('grade_no', e.grade_no);
          localStorage.setItem('store_id', e.store_id);

          if(layEvent === 'export'){
            layer.open({
              type: 2,
              title: e.stu_class_name+'--导入学生资料',
              shade: false,
              maxmin: true,
              area: ['60%', '30%'],
              content: "{{url('/merchantpc/exportstudata?')}}"+e.stu_class_no
            });
          }else if(layEvent === 'teacherlist'){
            $('.t_list').attr('lay-href',"{{url('/merchantpc/assignteacher?store_id=')}}"+e.store_id+"&merchant_id="+e.merchant_id+"&stu_class_no="+e.stu_class_no);
            
          }
        });

        //监听搜索
        
        form.on('select(schooltype)', function(data){
          var store_id = data.value;
          console.log(store_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_id: store_id
            }
          });
        });
        form.on('select(grade)', function(data){
          var stu_grades_no = data.value;
          console.log(stu_grades_no);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              stu_grades_no: stu_grades_no
            }
          });
        });

    });

  </script>

</body>
</html>