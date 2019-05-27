<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>角色分配</title>
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
        
        <div class="layui-card">
          <div class="layui-card-body layui-row layui-col-space10">
            <div class="layui-col-md12" id="box">
              




            </div>  

            <div class="layui-form-item">
              <div class="layui-input-block" style="margin-left:0;text-align: center;margin-top:50px;">
                <button class="layui-btn confirm" lay-submit="" lay-filter="component-form-element">确认</button>
              </div>
            </div>          
          </div>
        </div>
        
        
      </div>
      
    </div>
  </div>

  
  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
  var token = localStorage.getItem("Usertoken");
  var str=location.search;
  var customer_id=str.split('?')[1];
  console.log(customer_id)
  layui.config({
    base: '../../../layuiadmin/' //静态资源所在路径
  }).extend({
    index: 'lib/index' //主入口模块
  }).use(['index', 'form'], function(){
    var $ = layui.$
    ,admin = layui.admin
    ,element = layui.element
    ,form = layui.form;
    var permission_idArr=[];
    
    // 角色列表---------------------------------------------------------------------------
    $.post("{{url('/api/role_permission/role_list')}}",
    {
      token:token
    }, 
    function(res){
      console.log(res); 
      var html="";
      for(var i=0;i<res.data.length;i++){
        html+='<div class="two" data-id="'+res.data[i].role_id+'">';
          html+='<input type="checkbox" name="" class="checkbox" value="">';
          html+='<span class="two-name" data-id="'+res.data[i].role_id+'">'+res.data[i].display_name+'</span>';
        html+='</div>';
      }

      $('#box').append(html);

      //被选中---------------------------------------------------------------------------
      $.post("{{url('/api/role_permission/user_role_list')}}",
      {
        token:token,
        customer_id:customer_id
      }, 
      function(res){
        console.log(res); 
       
        if(res.status==1){
          for(var i =0;i < res.data.length;i++){
              permission_idArr.push(res.data[i].role_id)
          }
          // console.log(permission_idArr)
          for( var m=0;m<permission_idArr.length;m++){

            $('#box .two').find('input').each(function(index,item){
                if(permission_idArr[m]==$(item).parent().attr('data-id')){
                    $(item).attr('checked',true);                                    
                }
            })
            
          }
        }

      },"json");

      

    },"json");
    

    
    $('.confirm').click(function(){
      var iDarr = [];
      $("#box").find("input[type='checkbox']").each(function(i,e){
          
          if($(e).is(":checked")){
              iDarr.push($(e).parent().attr("data-id"))
              iDarr.join();
              console.log(iDarr.join());
          }
      });
      $.post("{{url('/api/role_permission/assign_role')}}",
      {
        token:token,
        customer_id:customer_id,
        role_id:iDarr.join()
      }, 
      function(res){
        console.log(res); 
        
        if(res.status==1){                                        
            layer.msg(res.message, {
                offset: '15px'
                ,icon: 1
                ,time: 2000
            });
        }else{
            layer.msg(res.message, {
                offset: '15px'
                ,icon: 2
                ,time: 3000
            });
        }


      },"json");


      
    })

  });
  </script>
</body>
</html>