<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>设备列表</title>
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
                <div class="layui-card-header">设备列表</div>
                  
                <div class="layui-card-body">

                  <div class="layui-btn-container" style="font-size:14px;">
                    <a class="layui-btn layui-btn-primary" lay-href="{{url('/user/adddevice')}}" id="agent" data-id="1" style="margin-bottom:0;">添加设备</a>
                    
                    <!-- 审核状态 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="status" id="status" lay-filter="status">
                              <option value="">选择类型</option>
                              <option value="p">打印设备</option>
                              <option value="s">扫码盒子</option>
                              <option value="v">语音设备</option>
                            </select>
                        </div>
                      </div>
                    </div>

                  </div>
                  

                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="type">
                    @{{#  if(d.type == "p"){ }}
                      <span>打印设备</span>
                    @{{#  } else if(d.type == "s") { }}
                      <span>扫码盒子</span>
                    @{{#  } else { }}
                      <span>语音设备</span>
                    @{{#  } }}
                  </script>
                  <!-- 判断状态 -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    <a class="layui-btn layui-btn-danger layui-btn-xs edit" lay-event="edit" lay-href="{{url('/user/editdevice')}}">设备修改</a>
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
    var store_id = localStorage.getItem("store_store_id");


    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form','table'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,form = layui.form
            ,table = layui.table;

       // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })
        
        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/device/lists')}}"
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
              {field:'type', title: '设备归类',templet:'#type'}
              ,{field:'device_name', title: '设备名称'}
              // ,{field:'device_type', title: '设备类型'}
              ,{field:'device_no',  title: '设备编号'}
              ,{field:'device_key', title: '设备秘钥'} 
              ,{field:'merchant_name', title: '绑定收银员'} 
              ,{width:150,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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

          if(layEvent === 'del'){ //审核
            layer.confirm('确定删除此条信息吗?', function(index){
              obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
              layer.close(index);

              $.ajax({
                url : "{{url('/api/device/del')}}",
                data : {token:token,id:e.id},
                type : 'post',
                dataType:"json",
                success : function(data) {
                  console.log(data);
                  if(data.status==1){
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
          }else if(layEvent === 'edit'){
            localStorage.setItem('store_id', e.id);
          }


        });

       
        // 选择审核状态
        form.on('select(status)', function(data){
          var type = data.value;
          
          //执行重载
          table.reload('test-table-page', {
            where: { 
              type:type
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });
       


        
        var active = {
          batchdel: function(){
            var checkStatus = table.checkStatus('test-table-page')
            ,checkData = checkStatus.data; //得到选中的数据
            console.log(checkData);

            if(checkData.length === 0){
              return layer.msg('请选择数据');
            }
          
            layer.confirm('确定删除吗？', function(index) {
              
              //执行 Ajax 后重载
              /*
              admin.req({
                url: 'xxx'
                //,……
              });
              */
              table.reload('test-table-page');
              layer.msg('已删除');
            });
          }
        }

        $('.layui-btn.layuiadmin-btn-forum-list').on('click', function(){
          var type = $(this).data('type');
          active[type] ? active[type].call(this) : '';
        });

    });

  </script>

</body>
</html>





