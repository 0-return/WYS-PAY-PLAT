<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>审核</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md6">
        
        <div class="layui-card layui-form" lay-filter="component-form-element">
          <div class="layui-card-header">是否通过</div>
          <div class="layui-card-body layui-row layui-col-space10">
            <div class="layui-col-md12">
              <input type="checkbox" name="zzz" lay-skin="switch" id="tongguo" lay-text="通过|不通过" checked>
            </div>
          </div>
        </div>
        <div class="layui-card">
          <div class="layui-card-header">状态说明</div>
          <div class="layui-card-body layui-row layui-col-space10">
            <div class="layui-col-md12">
              <input type="text" name="title" placeholder="请输入状态说明" autocomplete="off" class="layui-input desc">
            </div>            
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-input-block" style="margin-left:0;text-align: center;margin-top:50px;">
            <button class="layui-btn confirm" lay-submit="" lay-filter="component-form-element">确认</button>
          </div>
        </div>
        
      </div>
      
    </div>
  </div>

  
  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
  var token = localStorage.getItem("token");
  var str=location.search;
  var stu_order_type_no=str.split('?')[1];

  layui.config({
    base: '../../../layuiadmin/' //静态资源所在路径
  }).extend({
    index: 'lib/index' //主入口模块
  }).use(['index', 'form'], function(){
    var $ = layui.$
    ,admin = layui.admin
    ,element = layui.element
    ,form = layui.form;
    
    


    $('.confirm').click(function(){
      if($('#tongguo').is(':checked')) {
        
        $.post("{{url('/api/school/teacher/template/check')}}",
        {
          token:token,
          stu_order_type_no:stu_order_type_no,
          status:'1',
          status_desc:$('.desc').val()
        }, 
        function(res){
          console.log(res); 
          if(res.status==1){
            layer.msg(res.message, {
              offset: '15px'
              ,icon: 1
              ,time: 1000
            },function(){
              var index=parent.layer.getFrameIndex(window.name);
              parent.layer.close(index);
              window.parent.location.reload();
            });
            
          }else{
            layer.msg(res.message, {
              offset: '15px'
              ,icon: 2
              ,time: 1000
            });
          }
        });

      }else{
        $.post("{{url('/api/school/teacher/template/check')}}",
        {
          token:token,
          stu_order_type_no:stu_order_type_no,
          status:'3',
          status_desc:$('.desc').val()
        }, 
        function(res){
          if(res.status==1){
            layer.msg(res.message, {
              offset: '15px'
              ,icon: 1
              ,time: 1000
            },function(){
              var index=parent.layer.getFrameIndex(window.name);
              parent.layer.close(index);
              window.parent.location.reload();
            });
            
          }else{
            layer.msg(res.message, {
              offset: '15px'
              ,icon: 2
              ,time: 1000
            });
          } 
        });
      }



      
    })

  });
  </script>
</body>
</html>