<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>设备管理</title>
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
                <div class="layui-card-header">设备管理</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    
                    <!-- 搜索 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:600px;display: inline-block;">
                      <div class="layui-form-item">
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="deviceid" placeholder="请输入设备ID" autocomplete="off" class="layui-input">
                            </div>
                          </div>                       
                          
                          <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-list" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                          </div>
                          
                        </div>
                    </div>
                    <button class="layui-btn import" style="margin-bottom: 4px;height:36px;line-height: 36px;">导入设备</button>
                  

                                      

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
                  <!-- <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">设备修改</a> -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small"> 
                    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">设备删除</a>
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

<div id="open_import" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <!-- <a href="{{url('/student-add.xlsx')}}">模板下载</a> -->
      <div class="layui-form-item" style="display: inline-block;">
        <form id= "uploadForm" style="display: inline-block;padding-left:50px;">            
          <div class="layui-btn name" style="margin-right:20px;">选择所需上传文件</div>                  
          <input type="file" name="file" id="file" style="display: none;"/> 
          <input type="button" value="确定上传" id="Upload" class="layui-btn"/>  
        </form>
      </div>
    </div>
  </div>
</div>


  <input type="hidden" class="store_id">
  <input type="hidden" class="sort">
  <input type="hidden" class="stu_class_no">

  <input type="hidden" class="stu_order_batch_no">
  <input type="hidden" class="user_id">

  <input type="hidden" class="pay_status">
  <input type="hidden" class="pay_type">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
    <script>
    var token = localStorage.getItem("Usertoken");
    var str=location.search;
    var user_id=str.split('?')[1];

    
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
      

    // 渲染表格
    table.render({
      elem: '#test-table-page'
      ,url: "{{url('/api/device/device_oem_lists')}}"
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
        {field:'id', title: 'id'}
        ,{field:'user_id', title: 'user_id'}
        ,{field:'device_id', title: 'device_id'}
        ,{field:'device_type', title: 'device_type'}
        ,{field:'ScanPay',  title: 'ScanPay'}                             
        ,{field:'QrPay', title: 'QrPay'}
        ,{field:'QrAuthPay', title: 'QrAuthPay'}
        ,{field:'PayWays', title: 'PayWays'}
        ,{field:'OrderQuery', title: 'OrderQuery'}
        ,{field:'Order', title: 'Order'}
        ,{field:'OrderList', title: 'OrderList'}
        ,{field:'Refund', title: 'Refund'}
        ,{field:'Request', title: 'Request'}
        ,{field:'created_at', title: 'created_at'}
        ,{field:'updated_at', title: 'updated_at'}
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

      if(layEvent === 'del'){ //审核
        layer.confirm('确认删除此设备?',{icon: 2}, function(index){
          $.post("{{url('/api/device/device_oem_del')}}",
          {
             token:token,device_id:e.device_id,device_type:e.device_type

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
      }else if(layEvent === 'smallitem'){
        layer.open({
          type: 2,
          title: '缴费小项',
          shade: false,
          maxmin: true,
          area: ['60%', '70%'],
          content: "{{url('/user/payitem?')}}"+e.out_trade_no
        });
      }

      var data = obj.data;
      if(obj.event === 'setSign'){
        layer.open({
          type: 2,
          title: '模板详细',
          shade: false,
          maxmin: true,
          area: ['60%', '70%'],
          content: "{{url('/merchantpc/paydetail?')}}"+e.stu_order_type_no
        });
      }
    });

       
    //监听搜索
    form.on('submit(LAY-app-contlist-search)', function(data){
      var obj = data.field
      // console.log(obj)
      var device_id = data.field.deviceid;    
      console.log(data);
      //执行重载
      table.reload('test-table-page', {
        where: { 
          device_id: device_id,     
        }
        ,page: {
          l: 1 //重新从第 1 页开始
        }
      });
    });
      
   

    $('.import').click(function(){
      layer.open({
        type: 1,
        title: false,
        closeBtn: 0,
        area: '516px',
        skin: 'layui-layer-nobg', //没有背景色
        shadeClose: true,
        content: $('#open_import')
      });
    });

    // 导入商户
    $('.name').click(function(){
      console.log('00')
      $("#file").click();
    })
    // 获取文件名
    var file = $('#file');
    file.on('change', function( e ){
        //e.currentTarget.files 是一个数组，如果支持多个文件，则需要遍历
        var name = e.currentTarget.files[0].name;
        console.log( name );
        $('.name').html(name);
    });
    // excel文件导入
    $('#Upload').click(function(){
      var formData = new FormData($( "#uploadForm" )[0]);  
      console.log(formData);
      $.ajax({   
        url: "{{url('/api/device/device_oem_import?token=')}}"+token,  
        type: 'POST',  
        data: formData,  
        async: false,  
        cache: false,  
        contentType: false,  
        processData: false,  
        success: function (res) {  
          console.log(res);
          
          if(res.status==1){
            layer.msg(res.message, {
              offset: '15px'
              ,icon: 1
              ,time: 3000
            });
          }else{
            layer.alert(res.message, {icon: 2});
          }
         
        },  
        error: function (res) {  
          layer.msg(res.message, {
            offset: '15px'
            ,icon: 2
            ,time: 3000
          });
            
        }  
     });

    });
    });

  </script>

</body>
</html>





