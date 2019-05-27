<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>分配列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">

        <div class="layui-fluid">
          <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
              <div class="layui-card"> 
                <div class="layui-card-header">分配列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <a class="layui-btn layui-btn-primary assign" style="display: block;width: 100px;" name="">分配教师</a>

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.type == 1){ }}
                      班主任
                    @{{#  } else { }}
                      老师
                    @{{#  } }}
                  </script>
                  <!-- 判断状态 -->
                  <script type="text/html" id="table-content-list">
                    <a class="layui-btn layui-btn-normal layui-btn-xs del" lay-event="del">删除</a>
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
    
    function GetQueryString(name)
    {
         var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
         var r = window.location.search.substr(1).match(reg);
         if(r!=null)return  unescape(r[2]); return null;
    }
    var store_id=GetQueryString("store_id");
    var merchant_id=GetQueryString("merchant_id");
    var stu_class_no=GetQueryString("stu_class_no");



    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form', 'table'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,table = layui.table
            ,form = layui.form;


        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/school/teacher/ter/lst')}}"
            ,method: 'post'
            ,where:{
              token:token, 
              store_id:store_id,
              stu_class_no:stu_class_no             
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cols: [[
                {field:'name', width:250, title: '教师名称'}
                ,{field:'type_name', width:250, title: '职位',templet:'#statusTap'}
                ,{field:'phone', width:250, title: '手机号'}
                ,{align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
              
              // $('.assign').attr('name',res.data[0].name)
                
            }

        });



        table.on('tool(test-table-page)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
          var e = obj.data; //获得当前行数据
          var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
          var tr = obj.tr; //获得当前行 tr 的DOM对象
          console.log(e);
          localStorage.setItem('stu_grades_no', e.stu_grades_no);
          localStorage.setItem('school_name', e.school_name);

          if(layEvent === 'del'){ //删除
            layer.confirm('确定删除此条信息吗?', function(index){
              obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
              layer.close(index);
              $.ajax({
                url : "{{url('/api/school/teacher/ter/class/unbind')}}",
                data : {token:token,store_id:e.store_id,merchant_id:merchant_id,stu_class_no:stu_class_no,type:e.type},
                type : 'post',
                success : function(data) {
                  console.log(data);
                  layer.msg(data.message, {
                    offset: '15px'
                    ,icon: 1
                    ,time: 3000
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
        $('.assign').click(function(){
          layer.open({
              type: 2,
              title: '分配教师',
              shade: false,
              maxmin: true,
              area: ['70%', '70%'],
              content: "{{url('/merchantpc/assigntercalss?store_id=')}}"+store_id+"&merchant_id="+merchant_id+"&stu_class_no="+stu_class_no+"&name="+$('.assign').attr('name')
            });
        })

        
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
        


        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var value = data.field.id;        
          //执行重载
          table.reload('test-table-page', {
            where: { 
              student_name: value
            }
          });
        });

    });

  </script>

</body>
</html>