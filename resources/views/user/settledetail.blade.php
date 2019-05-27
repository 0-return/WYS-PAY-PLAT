<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>结算明细</title>
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
                <div class="layui-card-header">结算明细</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="id" placeholder="请输入用户名或者手机号" autocomplete="off" class="layui-input">
                            </div>
                          </div>                          
                          
                          <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-list" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                          </div>
                        </div>
                    </div>
                    
                    

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  
                  <script type="text/html" id="jiesuan">
                    @{{#  if(d.dx == 1){ }}
                      服务商
                    @{{#  } else { }}
                      商户
                    @{{#  } }}
                  </script>
                  <script type="text/html" id="istrue">
                    @{{#  if(d.is_true == 0){ }}
                      <span style="color:#e85052">未确认</span>
                    @{{#  } else { }}
                      <span style="color:#00963a">确认</span>
                    @{{#  } }}
                  </script>
                  <script type="text/html" id="rate">
                    @{{ d.rate }}%
                  </script>

                  <script type="text/html" id="table-content-list">                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs settlement" lay-event="settlement">确认结算</a>
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
    var agentName = localStorage.getItem("agentName");
    // var str=location.search;
    // var id=str.split('?')[1];
    function GetQueryString(name)
    {
      var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
      var r = window.location.search.substr(1).match(reg);
      if(r!=null)return  unescape(r[2]); return null;
    }
    var s_id=GetQueryString("id");
    var user_id=GetQueryString("user_id");
    console.log(s_id)
    console.log(user_id)

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
        
        if(user_id == null){
          table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/wallet/settlement_list_infos')}}"
            ,method: 'post'
            ,where:{
              token:token,settlement_list_id:s_id            
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[                
                {field:'id', title: 'id'}
                ,{field:'s_time',  title: '开始时间'}             
                ,{field:'e_time',  title: '结束时间'}
                ,{field:'user_name', title: '姓名'}
                ,{field:'phone', title: '手机号'}
                ,{field:'dx', title: '对象',templet:'#jiesuan'}
                ,{field:'source_type_desc', title: '返佣来源'}
                ,{field:'total_amount',  title: '需结算金额'} 
                ,{field:'rate',  title: '税点',templet:'#rate'}                
                ,{field:'get_amount',  title: '扣税结算金额'}                
                ,{field:'is_true',  title: '是否确认',templet:'#istrue'}  
                ,{field:'created_at',  title: '创建时间'} 
                ,{field:'updated_at',  title: '确认时间'}                 
                ,{width:120,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
        }else if(s_id == null){
          $('.layui-card-header').html(agentName);
          table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/wallet/settlement_list_infos')}}"
            ,method: 'post'
            ,where:{
              token:token,user_id:user_id             
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[                
                {field:'id', title: 'id'}
                ,{field:'s_time',  title: '开始时间'}             
                ,{field:'e_time',  title: '结束时间'}
                ,{field:'user_name', title: '姓名'}
                ,{field:'phone', title: '手机号'}
                ,{field:'dx', title: '对象',templet:'#jiesuan'}
                ,{field:'source_type_desc', title: '返佣来源'}
                ,{field:'total_amount',  title: '需结算金额'} 
                ,{field:'rate',  title: '税点',templet:'#rate'}                
                ,{field:'get_amount',  title: '扣税结算金额'}                
                ,{field:'is_true',  title: '是否确认',templet:'#istrue'}  
                ,{field:'created_at',  title: '创建时间'} 
                ,{field:'updated_at',  title: '确认时间'}                 
                ,{width:120,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
        }
        



         table.on('tool(test-table-page)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
          var e = obj.data; //获得当前行数据
          var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
          var tr = obj.tr; //获得当前行 tr 的DOM对象
          console.log(e);
          // localStorage.setItem('s_store_id', e.store_id);

          if(layEvent === 'del'){
            layer.confirm('确认删除此消息?',{icon: 2}, function(index){
              $.post("{{url('/api/wallet/settlement_list_del')}}",
              {
                  token:token,settlement_list_id:e.id

              },function(data){
                  console.log(data);
                  if(data.status==1){
                    obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                    layer.close(index);
                    layer.msg(data.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 1000
                    });
                  }else{
                    layer.msg(data.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 3000
                    });
                  }
              },"json");
              
            });
          }else if(layEvent === 'settlement'){
            layer.confirm('确认结算后无法回退请知晓',{icon: 1}, function(index){  
              // layer.close(index);
              layer.msg('加载中', {
                icon: 16
                ,shade: 0.01
              });

              $.post("{{url('/api/wallet/settlement_list_true')}}",
              {
                  token:token,settlement_list_id:e.settlement_list_id,list_info_id:e.id
              },function(data){
                  console.log(data);
                  if(data.status==1){  
                    // layer.close(load);                  
                    layer.msg(data.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 1000
                    });
                  }else{
                    layer.msg(data.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 3000
                    });
                  }
              },"json");

             
            });
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
          });
        });
        
       
        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var value = data.field.id;        
          //执行重载
          table.reload('test-table-page', {
            where: { 
              user_name: value
            }
          });
        });



    });


  </script>

</body>
</html>