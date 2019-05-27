<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>未绑定</title>
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
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }
    input[type="number"]{
        -moz-appearance: textfield;
    }
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
                <div class="layui-card-header">未绑定明细</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <!-- 搜索 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:600px;display: inline-block;">
                      <div class="layui-form-item">                          
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="tradeno" placeholder="请输入二维码编号" autocomplete="off" class="layui-input">
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
                  
                 

                  <script type="text/html" id="table-content-list">
                    <a class="layui-btn layui-btn-normal layui-btn-xs untying" lay-event="untying">绑定</a>
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>





<div id="edit_store_id" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      
      <div class="layui-form-item">
        <label class="layui-form-label">请输入门店ID:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入门店ID" class="layui-input js_store_id">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn submitbind">确定绑定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<input type="hidden" class="js_code_number">
<input type="hidden" class="js_cno">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
    <script>
    var token = localStorage.getItem("Usertoken");
    var str=location.search;
    var cno=str.split('?')[1];
    console.log(cno)

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
      if(cno == undefined){
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/user/QrListinfos')}}"
            ,method: 'post'
            ,where:{
              token:token, 
              code_type:'0',
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
                {field:'code_number', title: '码编号'}              
                ,{field:'cno',  title: '批次'}                
                ,{field:'updated_at',  title: '生成时间'}                
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
      }else{
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/user/QrListinfos')}}"
            ,method: 'post'
            ,where:{
              token:token,   
              cno:cno,
              code_type:'0',
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
                {field:'code_number', title: '码编号'}              
                ,{field:'cno',  title: '批次'}                
                ,{field:'updated_at',  title: '生成时间'}                
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
      }
        



         table.on('tool(test-table-page)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
          var e = obj.data; //获得当前行数据
          var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
          var tr = obj.tr; //获得当前行 tr 的DOM对象
          console.log(e);
          

          if(layEvent === 'untying'){
            $('.js_code_number').val(e.code_number)
            $('.js_cno').val(e.cno)
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#edit_store_id')
            });

            
          }

        });

        form.on('submit(LAY-app-contlist-search)', function(data){
         
          var code_no = data.field.tradeno;//二维码
          console.log(data);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              code_number:code_no
            }
          });
        });


        $('.submitbind').click(function(){          
          
          $.post("{{url('/api/user/bindQr')}}",
            {
                token:token,
                cno:$('.js_cno').val(),
                code_number:$('.js_code_number').val(),
                store_id:$('.js_store_id').val()
            },
            function(res){
                console.log(res);                
                if(res.status==1){
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 2000
                  },function(){
                    window.location.reload();
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