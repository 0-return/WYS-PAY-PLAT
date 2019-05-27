<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>banner</title>
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
    .del {background-color: #e85052;}
    /*.laytable-cell-1-school_icon{height:100%;}*/
    /*.img{width:800px;height:auto;}
    .img img{width:100%;height:auto;}*/
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
                <div class="layui-card-header">banner列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <a class="layui-btn layui-btn-primary" lay-href="{{url('/user/addbanner')}}" style="display: block;width: 122px;">添加banner</a>
                    
                    <div class="layui-form" style="display: inline-block;">                      
                      <div class="layui-form-item">                          
                        <div class="layui-inline">
                          
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item" placeholder="开始时间" lay-key="23">
                          </div>
                        </div>
                        <div class="layui-inline">
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input end-item test-item" placeholder="结束时间" lay-key="24">
                          </div>
                        </div>
                        <!-- <button class="layui-btn" style="margin-bottom: 4px;height:36px;line-height: 36px;">导出</button> -->
                      </div> 

                    </div>

                  
                    
                    <div class="layui-form" lay-filter="component-form-group" style="width:250px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="class" id="class" lay-filter="class">
                                <option value="">选择状态</option>
                                <option value="1">正常</option>
                                <option value="2">禁用</option>
                            </select>
                        </div>
                      </div>
                    </div>
                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.status == 1){ }}
                      正常
                    @{{#  } else { }}
                      禁用
                    @{{#  } }}
                  </script>
                  
                  <script type="text/html" id="imgTpl">
                    <img style="display: inline-block;height: 100%;" src= @{{d.img_url }}>
                  </script>

                  <script type="text/html" id="table-content-list">
                    
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
  <div id="img" class="hide" style="display: none"><img style="width:100%;height:100%" src=""></div>  
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
        // 选择学校
        $.ajax({
            url : "{{url('/api/user/get_sub_users')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].user_id + "'>"
                            + data.data[i].user_name + "</option>";
                    }    
                    $("#schooltype").append('<option value="">选择业务员</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
       
        

        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/user/banners')}}"
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
                {field:'title', title: '标题'}
                ,{field:'img_url', title: 'banner',event: 'setSign',templet: '#imgTpl'}                
                ,{field:'action_url',  title: '跳转链接'} 
                ,{field:'status',  title: '状态',templet: '#statusTap'}  
                ,{field:'banner_time_s', title: '开始时间'}              
                ,{field:'banner_time_e', title: '结束时间'}
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
          var tr = obj.tr;

          $('#img img').attr('src',e.img_url);

          if(obj.event === 'setSign'){
            console.log(e);
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#img')
            });
          }

          if(layEvent === 'del'){
            layer.confirm('确认删除banner信息?',{icon: 2}, function(index){
              
              $.ajax({
                url : "{{url('/api/user/del_banners')}}",
                data : {token:token,id:e.id},
                type : 'post',
                success : function(data) {
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
                    
                },
              });

            });
          }

        });

        
        // 选择学校
        form.on('select(schooltype)', function(data){
          var user_id = data.value;
          $('.user_id').val(user_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              user_id: user_id,
              status:$('.status').val()
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });
        
        // 选择状态
        form.on('select(class)', function(data){
          var status = data.value;
          $('.status').val(status);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              status: status,
              user_id:$('.user_id').val()
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
              school_name: value
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });

        laydate.render({
          elem: '.start-item'
          ,type: 'datetime'
          ,done: function(value){
            //执行重载
            table.reload('test-table-page', {
              where: { 
                banner_time_s: value,
                banner_time_e:$('.end-item').val()
              }
              ,page: {
                curr: 1 //重新从第 1 页开始
              }
            });
          }
        });
        laydate.render({
          elem: '.end-item'
          ,type: 'datetime'
          ,done: function(value){
            //执行重载
            table.reload('test-table-page', {
              where: { 
                banner_time_s: $('.start-item').val(),
                banner_time_e:value                
              }
              ,page: {
                curr: 1 //重新从第 1 页开始
              }
            });
          }
        });





    });


  </script>

</body>
</html>