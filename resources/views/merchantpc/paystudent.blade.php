<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>查看缴费详情</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
      .rightbox{height:36px;line-height: 36px;}
      .layui-form{background-color: #fff;}
    </style>
</head>
<body>

<div class="layui-row">
  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>


</div>

<input type="hidden" class="schooltypeid" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("token");
    var s_student_name = localStorage.getItem("s_student_name");
    var s_store_id = localStorage.getItem("s_store_id");
    var s_stu_grades_no = localStorage.getItem("s_stu_grades_no");
    var s_stu_class_no = localStorage.getItem("s_stu_class_no");


    var str=location.search;
    var student_no=str.split('?')[1];

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'table','form'], function(){
        var $ = layui.$            
            table = layui.table
            ,form = layui.form;

        
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/school/teacher/order/lst')}}"
            ,method: 'post'
            ,where:{
              token:token,
              store_id:s_store_id,
              stu_grades_no:s_stu_grades_no,
              stu_class_no:s_stu_class_no,          
              student_no:student_no              
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cols: [[
                {field:'out_trade_no', width:100, title: '订单号'}
                ,{field:'batch_name', width:100, title: '缴费名称'}
                ,{field:'student_name', width:100, title: '学生名称'}
                ,{field:'school_name', width:100, title: '所属学校'}
                // ,{field:'stu_grades_name', width:100, title: '所属年级'}
                ,{field:'stu_class_name', width:100, title: '所属班级'}
                ,{field:'amount', width:150, title: '总金额'}
                ,{field:'pay_status_desc', width:150, title: '状态'}
                ,{field:'pay_type_desc', width:150, title: '支付类型'}
                ,{field:'pay_time', width:150, title: '缴费时间'}
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
        
       

    });
</script>
</body>
</html>
