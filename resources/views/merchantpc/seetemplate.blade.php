<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>表单元素</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
</head>
<body>

  <div class="layui-fluid" style="background-color: #fff;">
    <div class="layui-row">
      <div class="layui-form">

        <div class="layui-form-item">
          <div class="layui-inline">
            <label class="layui-form-label">学校名称</label>
            <div class="layui-form-mid layui-word-aux"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-inline">
            <label class="layui-form-label">模板名称</label>
            <div class="layui-form-mid layui-word-aux"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-inline">
            <label class="layui-form-label">模板描述</label>
            <div class="layui-form-mid layui-word-aux"></div>
          </div>
        </div>        
        
        <table class="layui-table">
          <colgroup>
            <col width="150">
            <col width="150">
            <col width="200">
            <col>
          </colgroup>
          <thead>
            <tr>
              <th>缴费名称</th>
              <th>缴费金额</th>
              <th>数量</th>
              <th>是否必交(Y或N)</th>
            </tr> 
          </thead>
          <tbody>            
            
          </tbody>
        </table>
        <div class="layui-form-item">
          <div class="layui-inline">
            <label class="layui-form-label">总金额:</label>
            <div class="layui-form-mid layui-word-aux"></div>
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
  }).use(['index', 'form','table'], function(){
    var $ = layui.$
    ,admin = layui.admin
    ,element = layui.element
    ,table = layui.table
    ,form = layui.form;
    
    $.post("{{url('/api/school/teacher/template/show')}}",
    {
      token:token,
      stu_order_type_no:stu_order_type_no
    }, 
    function(res){
      console.log(res); 
      $('.layui-form .layui-form-item').eq(0).find('.layui-word-aux').html(res.data.school_name);
      $('.layui-form .layui-form-item').eq(1).find('.layui-word-aux').html(res.data.charge_name);
      $('.layui-form .layui-form-item').eq(2).find('.layui-word-aux').html(res.data.charge_desc);
      var money=parseFloat(res.data.amount);

      $('.layui-form .layui-form-item').eq(3).find('.layui-word-aux').html(money.toFixed(2));


      var data=res.data.charge_item;
      // var data=JSON.parse(str) ;  //返回一个新对象
      console.log(data);
      var str="";
      for(var i=0;i<data.length;i++){
        str+='<tr>';
          str+='<td>'+data[i].item_name+'</td>';
          str+='<td>'+data[i].item_price+'</td>';
          str+='<td>'+data[i].item_number+'</td>';
          str+='<td>'+data[i].item_mandatory+'</td>';
        str+='</tr>';
      }
      $('tbody').append(str);

    }); 

    
    


    $('.confirm').click(function(){
      $.post("{{url('/api/school/teacher/check')}}",
      {
        token:token,
        store_id:store_id,
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
          
        }
      });      
    })

  });
  </script>
</body>
</html>