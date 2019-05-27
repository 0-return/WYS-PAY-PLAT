<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>支付宝红包</title>
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

    .userbox{
      height:200px;
      overflow-y: auto;
      z-index: 999;
      position: absolute;
      left: 0px;
      top: 42px;
      width:298px;
      background-color:#ffffff;
      border: 1px solid #ddd;
    }
    .userbox .list{
      height:38px;line-height: 38px;cursor:pointer;
      padding-left:10px;
    }
    .userbox .list:hover{
      background-color:#eeeeee;
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
                <div class="layui-card-header">支付宝红包</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <a class="layui-btn layui-btn-primary" lay-href="{{url('/user/addalipayred')}}" style="display: block;width: 122px;">添加红包</a>

                    <!-- 选择业务员 -->
                    <!-- <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="agent" id="agent" lay-filter="agent" lay-search>
                                
                            </select>
                        </div>
                      </div>
                    </div> -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                          <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入业务员名称" class="layui-input transfer">

                          <div class="userbox" style='display: none'></div>
                        </div>
                      </div>
                    </div>
                    <!-- 选择门店 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="store" id="store" lay-filter="store" lay-search>
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="id" placeholder="请输入消息关键字" autocomplete="off" class="layui-input">
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
        
 
        
        // 选择业务员
        // $.ajax({
        //     url : "{{url('/api/user/get_sub_users')}}",
        //     data : {token:token,l:100},
        //     type : 'post',
        //     success : function(data) {
        //         console.log(data);
        //         var optionStr = "";
        //             for(var i=0;i<data.data.length;i++){
        //                 optionStr += "<option value='" + data.data[i].id + "' >" + data.data[i].name + "</option>";
        //             }    
        //             $("#agent").append('<option value="">选择业务员</option>'+optionStr);
        //             layui.form.render('select');
        //     },
        //     error : function(data) {
        //         alert('查找板块报错');
        //     }
        // });


        $(".transfer").bind("input propertychange",function(event){
         console.log($(this).val())
          $.post("{{url('/api/user/get_sub_users')}}",
          {
              token:token,
              user_name:$(this).val(),
              self:'1'            

          },function(res){
              console.log(res);
              var html="";
              console.log(res.t)
              if(res.t==0){
                  $('.userbox').html('')
              }else{
                  for(var i=0;i<res.data.length;i++){
                      html+='<div class="list" data='+res.data[i].id+'>'+res.data[i].name+'-'+res.data[i].level_name+'</div>'
                  }
                  $(".userbox").show()
                  $('.userbox').html('')
                  $('.userbox').append(html)
              }
              
          },"json");
        });

        $(".userbox").on("click",".list",function(){
  
          $('.transfer').val($(this).html())
          $('.js_user_id').val($(this).attr('data'))
          $('.userbox').hide()

          table.reload('test-table-page', {
            where: { 
              user_id:$(this).attr('data')
            }
            ,page: {
              curr: 1
            }
          });
          // 选择门店
          $.ajax({
            url : "{{url('/api/user/store_lists')}}",
            data : {token:token,user_id:$(this).attr('data'),l:100},
            type : 'post',
            success : function(data) {
              console.log(data);
              var optionStr = "";
                  for(var i=0;i<data.data.length;i++){
                      optionStr += "<option value='" + data.data[i].store_id + "'>"
                        + data.data[i].store_name + "</option>";
                  }    
                  $("#store").html('');
                  $("#store").append('<option value="">选择门店</option>'+optionStr);
                  layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
          });
        })
        

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/huodong/get_list')}}"
            ,method: 'post'
            ,where:{
              token:token  
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
              {field:'content', title: '口令红包内容'}
              ,{field:'remark', title: '备注'}
              ,{field:'store_name', title: '门店范围'}
              ,{field:'create_user_name', title: '创建者'}
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
            layer.confirm('确认删除此消息?',{icon: 2}, function(index){
              $.post("{{url('/api/huodong/del')}}",
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
          }


        });

        
        
        
        
        // 选择业务员
        form.on('select(agent)', function(data){
          var user_id = data.value;
          $('.user_id').val(user_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              create_user_id: user_id 
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
          // 选择门店
          $.ajax({
              url : "{{url('/api/user/store_lists')}}",
              data : {token:token,user_id:user_id,l:100},
              type : 'post',
              success : function(data) {
                  console.log(data);
                  var optionStr = "";
                      for(var i=0;i<data.data.length;i++){
                          optionStr += "<option value='" + data.data[i].store_id + "'>"
                            + data.data[i].store_name + "</option>";
                      }    
                      $("#store").html('');
                      $("#store").append('<option value="">选择门店</option>'+optionStr);
                      layui.form.render('select');
              },
              error : function(data) {
                  alert('查找板块报错');
              }
          });
        });
        // 选择门店
        form.on('select(store)', function(data){
          var store_id = data.value;
          //执行重载
          table.reload('test-table-page', {
            where: {               
              store_id:store_id
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
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
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });
        
        
    });

  </script>

</body>
</html>





