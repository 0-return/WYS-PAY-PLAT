<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>缴费模板</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/layui/css/layui.css')); ?>" media="all">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/style/admin.css')); ?>" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .cur{color:#009688;}
    /*.laytable-cell-1-school_icon{height:100%;}*/
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
                <div class="layui-card-header">学校列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <!-- <a class="layui-btn layui-btn-primary" lay-href="<?php echo e(url('/merchantpc/addpaytemplate')); ?>" style="display: block;width: 122px;">添加缴费模板</a> -->
                    
                    <div class="layui-form" lay-filter="component-form-group" style="width:250px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="schooltype" id="schooltype" lay-filter="schooltype">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    
                    <div class="layui-form" lay-filter="component-form-group" style="width:250px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="class" id="class" lay-filter="class">
                                <option value="">选择状态</option>
                                <option value="1">审核成功</option>
                                <option value="2">未审核</option>
                                <option value="3">审核失败</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="id" placeholder="请输入学校名称" autocomplete="off" class="layui-input">
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
                  <script type="text/html" id="statusTap">
                    {{#  if(d.status == 1){ }}
                      <span class="cur">{{ d.status_desc }}</span>
                    {{#  } else { }}
                      {{ d.status_desc }}
                    {{#  } }}
                  </script>
                  <script type="text/html" id="alipaystatus">
                    {{#  if(d.alipay_status == 1){ }}
                      <span class="cur">{{ d.alipay_status_desc }}</span>
                    {{#  } else { }}
                      {{ d.alipay_status_desc }}
                    {{#  } }}
                  </script>
                  <script type="text/html" id="imgTpl">
                    <img style="display: inline-block;height: 100%;" src= {{d.school_icon }}>
                  </script>

                  <script type="text/html" id="table-content-list">
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs shenhe" lay-event="shenhe">审核</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs tongbu" lay-event="tongbu">同步支付宝</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-event="edit" lay-href="<?php echo e(url('/user/editschool')); ?>">学校修改</a>
                    
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
  <script src="<?php echo e(asset('/layuiadmin/layui/layui.js')); ?>"></script> 
    <script>
    var token = localStorage.getItem("token");
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

        // 选择学校
        $.ajax({
            url : "<?php echo e(url('/api/user/get_sub_users')); ?>",
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
            ,url: "<?php echo e(url('/api/school/agent/lst')); ?>"
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
                {field:'school_icon', title: 'logo',templet: '#imgTpl'}
                ,{field:'school_name', title: '名称'}
                ,{field:'store_id',  title: '平台编号id'}   
                ,{field:'school_stdcode',  title: '学校编号'}             
                ,{field:'status',  title: '系统状态',templet: '#statusTap'}                
                ,{field:'alipay_status',  title: '支付宝状态',templet: '#alipaystatus'}
                ,{width:250,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
          localStorage.setItem('stu_order_type_no', e.stu_order_type_no);
          localStorage.setItem('store_id', e.store_id);

          if(layEvent === 'detail'){ //删除
            layer.open({
              type: 2,
              title: '查看',
              shade: false,
              maxmin: true,
              area: ['70%', '80%'],
              content: "<?php echo e(url('/merchantpc/seetemplate?')); ?>"+e.stu_order_type_no
            });
          }else if(layEvent === 'tongbu'){
            $.post("<?php echo e(url('/api/school/agent/sync')); ?>",
            {
              token:token,
              store_id:e.store_id
            }, 
            function(res){
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
            });
          }else if(layEvent === 'shenhe'){
            layer.open({
              type: 2,
              title: '学校审核',
              shade: false,
              maxmin: true,
              area: ['70%', '80%'],
              content: "<?php echo e(url('/user/examineschool?')); ?>"+e.store_id
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
          });
        });



    });


  </script>

</body>
</html>