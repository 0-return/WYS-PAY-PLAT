<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>广告管理</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .cur{color:#009688;}
    .del {background-color: #e85052;}    /*.laytable-cell-1-school_icon{height:100%;}*/
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
                <div class="layui-card-header">广告管理</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <a class="layui-btn layui-btn-primary" lay-href="{{url('/user/addad')}}" style="display: block;width: 122px;">添加广告</a>
                    

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <script type="text/html" id="time">
                    @{{ d.s_time }}--@{{ d.e_time }}
                  </script>
                  <script type="text/html" id="table-content-list">
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs see " lay-event="see">查看</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-event="edit">广告修改</a>
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
  <input type="hidden" class="user_id">
  <input type="hidden" class="status">
<!-- 操作按钮 -->
<!-- 
                    
<a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="detail">查看</a> -->
  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
    <script>
    var token = localStorage.getItem("Usertoken");
    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form', 'table','laydate'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,table = layui.table
            ,form = layui.form
            ,laydate = layui.laydate;

        
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })

        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/ad/ad_lists')}}"
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
                {field:'title', title: '广告标题'}  
                ,{field:'ad_p_desc', title: '位置'}  
                ,{field:'redirect_url',  title: '广告生效时间',templet:'#time'}               
                ,{width:200,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
            layer.confirm('确认删除此消息?',{icon: 2}, function(index){
              $.post("{{url('/api/ad/ad_del')}}",
              {
                 token:token,id:e.id

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
          }else if(layEvent === 'see'){
            layer.open({
              type: 2,
              title: '详细',
              shade: false,
              maxmin: true,
              area: ['90%', '100%'],
              content: "{{url('/user/adsee?')}}"+e.id
            });
          }else if(layEvent === 'edit'){
            localStorage.setItem('ad_p_id', e.ad_p_id);
            localStorage.setItem('user_ids', e.user_ids);
            localStorage.setItem('store_key_ids', e.store_key_ids);
            
            $('.edit').attr('lay-href',"{{url('/user/editad?')}}"+e.id);
          }

          
        });

        
        // 选择学校
        form.on('select(schooltype)', function(data){
          var type = data.value;
          
          //执行重载
          table.reload('test-table-page', {
            where: { 
              type: type
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });
        
       
        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var value = data.field.id;        
          //执行重载
          table.reload('test-table-page', {
            where: { 
              title: value
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });



    });


  </script>

</body>
</html>