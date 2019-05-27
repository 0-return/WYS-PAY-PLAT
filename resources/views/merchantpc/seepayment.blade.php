<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>缴费项目详情</title>
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

<div class="layui-form">
  <div class="layui-form-item">
    <label class="layui-form-label">学校：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">年级：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">班级：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">截止时间：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">模板名称：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">总金额：</label>
    <div class="layui-input-block rightbox"></div>
  </div>





</div>

<input type="hidden" class="schooltypeid" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("token");
    var str=location.search;
    var stu_order_batch_no=str.split('?')[1];

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'table','form'], function(){
        var $ = layui.$            
            table = layui.table
            ,form = layui.form;

        
        $.post("{{url('/api/school/teacher/payitem/show')}}",
        {
          token:token,
          stu_order_batch_no:stu_order_batch_no
        }, 
        function(res){
          console.log(res); 
          $('.layui-form .layui-form-item').eq(0).find('.rightbox').html(res.data.school_name);
          $('.layui-form .layui-form-item').eq(1).find('.rightbox').html(res.data.grade_name);
          $('.layui-form .layui-form-item').eq(2).find('.rightbox').html(res.data.class_name);
          $('.layui-form .layui-form-item').eq(3).find('.rightbox').html(res.data.gmt_end);
          $('.layui-form .layui-form-item').eq(4).find('.rightbox').html(res.data.template_name);         

          var money=parseFloat(res.data.amount);
          $('.layui-form .layui-form-item').eq(5).find('.rightbox').html(money.toFixed(2));


         

        }); 

    });
</script>
</body>
</html>
