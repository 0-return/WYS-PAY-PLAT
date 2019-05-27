<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>查看</title>
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
    <label class="layui-form-label">平台订单号：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">第三方订单号：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">缴费名称：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">学生名称：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">所属学校：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">所属班级：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">总金额：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">状态：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">支付类型：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">缴费截止时间：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">家长姓名：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">家长电话：</label>
    <div class="layui-input-block rightbox"></div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label"></label>
    <div class="layui-input-block rightbox"><a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="tongbu" id="tongbu">下单到支付宝</a></div>
  </div>

  




</div>



<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("token");
    var str=location.search;
    var out_trade_no=str.split('?')[1];

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'table','form'], function(){
        var $ = layui.$            
            table = layui.table
            ,form = layui.form;

        
        $.post("{{url('/api/school/teacher/order/show')}}",
        {
          token:token,
          out_trade_no:out_trade_no
        }, 
        function(res){
          console.log(res); 
          $('.layui-form .layui-form-item').eq(0).find('.rightbox').html(res.data.out_trade_no);
          $('.layui-form .layui-form-item').eq(1).find('.rightbox').html(res.data.trade_no);
          $('.layui-form .layui-form-item').eq(2).find('.rightbox').html(res.data.batch_name);
          $('.layui-form .layui-form-item').eq(3).find('.rightbox').html(res.data.student_name);
          $('.layui-form .layui-form-item').eq(4).find('.rightbox').html(res.data.school_name);
          $('.layui-form .layui-form-item').eq(5).find('.rightbox').html(res.data.stu_class_name);  
          var money=parseFloat(res.data.amount);       
          $('.layui-form .layui-form-item').eq(6).find('.rightbox').html(money.toFixed(2));         
          $('.layui-form .layui-form-item').eq(7).find('.rightbox').html(res.data.pay_status_desc);        
          $('.layui-form .layui-form-item').eq(8).find('.rightbox').html(res.data.pay_type_source_desc);
          $('.layui-form .layui-form-item').eq(9).find('.rightbox').html(res.data.gmt_end);                       
          $('.layui-form .layui-form-item').eq(10).find('.rightbox').html(res.data.student_user_name);                       
          $('.layui-form .layui-form-item').eq(11).find('.rightbox').html(res.data.student_user_mobile);                       

        }); 

        $('#tongbu').click(function(){
          $.ajax({
              url : "{{url('/api/school/teacher/order/send/one')}}",
              data : {token:token,out_trade_no:out_trade_no},
              type : 'post',
              success : function(data) {
                  console.log(data);
                  layer.msg(data.message);
                  
              },
              error : function(data) {
                  alert('查找板块报错');
              }
            });
        })

    });
</script>
</body>
</html>
