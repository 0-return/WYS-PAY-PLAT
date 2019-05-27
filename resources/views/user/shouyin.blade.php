<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>收银插件</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .tongbu{background-color: #4c9ef8;color:#fff;}
    .cur{color:#009688;}
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
                <div class="layui-card-header">收银插件</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <a class="layui-btn layui-btn-primary addshouyin" lay-href="" style="display: block;width: 122px;">添加插件激活码</a>

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.pay_status == 1){ }}
                      <span class="cur">@{{ d.pay_status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.pay_status_desc }}
                    @{{#  } }}
                  </script>
                  <!-- 判断状态 -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    
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
    var token = localStorage.getItem("Usertoken");
    // var str=location.search;
    // var user_id=str.split('?')[1];
    var store_id="{{$_GET['store_id']}}";
    var store_name="{{$_GET['store_name']}}";

    
    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form','table','laydate'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,form = layui.form
            ,table = layui.table
            ,laydate = layui.laydate;
      // 未登录,跳转登录页面
      $(document).ready(function(){        
          if(token==null){
              window.location.href="{{url('/user/login')}}"; 
          }
      })
        
 
      $('.addshouyin').attr('lay-href',"{{url('/user/addshouyin?store_id=')}}"+store_id+"&store_name="+store_name);  
        
        

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/qwx/code_list')}}"
            ,method: 'post'
            ,where:{
              token:token,
              store_id:store_id
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
              {field:'secret_key', title: '设备激活码'}
              ,{field:'merchant_name', title: '收银员'}
              ,{width:100,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
          // localStorage.setItem('s_store_id', e.store_id);

          if(layEvent === 'del'){

            console.log(e)
            layer.confirm('确认删除此消息?',{icon: 2}, function(index){
              $.post("{{url('/api/qwx/del_code')}}",
              {
                 token:token,store_id:e.store_id,merchant_id:e.merchant_id,device_id:e.device_id

              },function(res){
                  console.log(res);
                  if(res.status==1){
                    obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                    layer.close(index);
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
              
            });
          }


        });

        
        
        
        
        
        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var content = data.field.id;    
          console.log(data);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              content:content
            }
          });
        });
        
        
    });

  </script>

</body>
</html>





