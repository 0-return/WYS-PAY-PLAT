<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>收费项目列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/layui/css/layui.css')); ?>" media="all">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/style/admin.css')); ?>" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .tongbu{background-color: #4c9ef8;color:#fff;}
    .cur{color:#009688;}
    .del{background-color:#e85052;}
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
                <div class="layui-card-header">缴费项目管理</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <div>
                      <a class="layui-btn layui-btn-primary" lay-href="<?php echo e(url('/merchantpc/paymentitem')); ?>" style="display: inline-block;width: 120px;">添加缴费项目</a>
                      <a class="layui-btn layui-btn-primary" lay-href="<?php echo e(url('/merchantpc/importbill')); ?>" style="display: inline-block;width: 120px;">导入缴费项目</a>
                      
                      
                    </div>

                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="schooltype" id="schooltype" lay-filter="schooltype">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="grade" id="grade" lay-filter="grade">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="class" id="class" lay-filter="class">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="status" id="status" lay-filter="status">
                              <option value="">选择状态</option>
                              <option value="1">审核成功</option>
                              <option value="2">待审核</option>
                              <option value="3">审核失败</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    
                    <div class="layui-form" style="display: block;">                      
                      <div class="layui-form-item">                          
                        <div class="layui-inline">
                          
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item" placeholder="缴费开始时间" lay-key="23">
                          </div>
                        </div>
                        <div class="layui-inline">
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input end-item test-item" placeholder="缴费结束时间" lay-key="24">
                          </div>
                        </div>
                        <!-- <button class="layui-btn" style="margin-bottom: 4px;height:36px;line-height: 36px;">导出</button> -->
                      </div> 

                    </div>

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="statusTap">
                    {{#  if(d.status == 1){ }}
                      <span class="cur">{{ d.status_desc }}</span>
                    {{#  } else { }}
                      {{ d.status_desc }}
                    {{#  } }}
                  </script>
                  <!-- 判断状态 -->

                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    {{#  if(d.status == 1){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs shenhe" lay-event="shenhe" style="display: none">审核</a>
                    {{#  } else { }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs shenhe" lay-event="shenhe">审核</a>
                    {{#  } }}
                    
                    <a class="layui-btn layui-btn-danger layui-btn-xs tongbu" lay-event="tongbu">下单到支付宝</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="see">查看</a>
                    {{#  if(d.batch_create_type == 2){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-event="edit" lay-href="<?php echo e(url('/merchantpc/editpayment')); ?>" style="display: none">项目修改</a>
                    {{#  } else { }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-event="edit" lay-href="<?php echo e(url('/merchantpc/editpayment')); ?>">项目修改</a>
                    {{#  } }}
                    

                    <a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="cuishou">催收</a>
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
  <input type="hidden" class="store_id">
  <input type="hidden" class="stu_grades_no">
  <input type="hidden" class="stu_class_no">
  <input type="hidden" class="status">

  <script src="<?php echo e(asset('/layuiadmin/layui/layui.js')); ?>"></script> 
    <script>
    var token = localStorage.getItem("token");
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

        // 选择学校
        $.ajax({
            url : "<?php echo e(url('/api/school/teacher/lst')); ?>",
            data : {token:token},
            type : 'post',
            success : function(data) {
                // console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].store_id + "'>"
                            + data.data[i].school_name + "</option>";
                    }    
                    $("#schooltype").append('<option value="">选择学校</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
        // 选择年级
        $.ajax({
            url : "<?php echo e(url('/api/school/teacher/grade/lst')); ?>",
            data : {token:token},
            type : 'post',
            success : function(data) {
                // console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].stu_grades_no + "'>"
                            + data.data[i].stu_grades_name + "</option>";
                    }    
                    $("#grade").append('<option value="">选择年级</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
        // 选择班级
        $.ajax({
            url : "<?php echo e(url('/api/school/teacher/class/lst')); ?>",
            data : {token:token},
            type : 'post',
            success : function(data) {
                // console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].stu_class_no + "'>"
                            + data.data[i].stu_class_name + "</option>";
                    }    
                    $("#class").append('<option value="">选择班级</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });


        table.render({
            elem: '#test-table-page'
            ,url: "<?php echo e(url('/api/school/teacher/payitem/lst')); ?>"
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
                {field:'batch_name', title: '缴费名称'}
                ,{field:'school_name', title: '所属学校',event: 'setSign', style:'cursor: pointer;'}
                // ,{field:'grade_name', title: '所属年级'}
                // ,{field:'class_name',  title: '缴费班级'}
                // ,{field:'amount',  title: '总金额'}
                ,{field:'gmt_end',  title: '截止缴费时间'}
                ,{field:'status_desc', title: '审核状态',templet:'#statusTap'}
                ,{width:370,align:'left', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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

          if(layEvent === 'shenhe'){ //审核
            layer.open({
              type: 2,
              title: '审核',
              shade: false,
              maxmin: true,
              area: ['70%', '80%'],
              content: "<?php echo e(url('/merchantpc/examinepayment?')); ?>"+e.stu_order_batch_no
            });
          }else if(layEvent === 'see'){
            layer.open({
              type: 2,
              title: '查看',
              shade: false,
              maxmin: true,
              area: ['60%', '70%'],
              content: "<?php echo e(url('/merchantpc/seepayment?')); ?>"+e.stu_order_batch_no
            });
          }else if(layEvent === 'tongbu'){

            $.ajax({
              url : "<?php echo e(url('/api/school/teacher/order/send')); ?>",
              data : {token:token,stu_order_batch_no:e.stu_order_batch_no},
              type : 'post',
              success : function(data) {
                  console.log(data);
                  layer.msg(data.message);
                  
              },
              error : function(data) {
                  alert('查找板块报错');
              }
            });
          }else if(layEvent === 'edit'){
            localStorage.setItem('jf_store_id', e.store_id);
            localStorage.setItem('jf_stu_order_batch_no', e.stu_order_batch_no);
            localStorage.setItem('jf_stu_grades_no', e.stu_grades_no);
            localStorage.setItem('jf_stu_class_no', e.stu_class_no);
            localStorage.setItem('jf_stu_order_type_no', e.stu_order_type_no);
            localStorage.setItem('jf_stu_class_no', e.stu_class_no);
            localStorage.setItem('jf_remove_student_no', e.remove_student_no);

          }else if(layEvent === 'cuishou'){
            $.ajax({
              url : "<?php echo e(url('/api/school/teacher/payitem/remind')); ?>",
              data : {token:token,stu_order_batch_no:e.stu_order_batch_no},
              type : 'post',
              success : function(data) {
                  console.log(data);
                  layer.msg(data.message, {
                    offset: '15px'
                    ,icon: 1
                    ,time: 3000
                  });
                  
              },
              error : function(data) {
                  layer.msg(data.message, {
                    offset: '15px'
                    ,icon: 2
                    ,time: 3000
                  });
              }
            });
          }else if(layEvent === 'del'){
            layer.confirm('删除项目后无法恢复，请确认后在操作。',{icon: 2}, function(index){
              
              $.ajax({
                url : "<?php echo e(url('/api/school/teacher/payitem/del')); ?>",
                data : {token:token,stu_order_batch_no:e.stu_order_batch_no},
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

          var data = obj.data;
          if(obj.event === 'setSign'){
            layer.open({
              type: 2,
              title: '模板详细',
              shade: false,
              maxmin: true,
              area: ['60%', '70%'],
              content: "<?php echo e(url('/merchantpc/paydetail?')); ?>"+e.stu_order_type_no
            });
          }
        });

        
        // 选择学校
        form.on('select(schooltype)', function(data){
          var store_id = data.value;
          $('.store_id').val(store_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_id: store_id,
              start_time:$('.start-item').val(),
              end_time:$('.end-item').val(),              
              stu_grades_no:$('.stu_grades_no').val(),
              stu_class_no:$('.stu_class_no').val(),
              status:$(".status").val(),
            }
          });
        });
        // 选择年级
        form.on('select(grade)', function(data){
          var stu_grades_no = data.value;
          $('.stu_grades_no').val(stu_grades_no);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              start_time:$('.start-item').val(),
              end_time:$('.end-item').val(),
              store_id:$(".store_id").val(),              
              stu_grades_no:stu_grades_no,
              stu_class_no:$('.stu_class_no').val(),
              status:$(".status").val(),
            }
          });
        });
        // 选择班级
        form.on('select(class)', function(data){
          var stu_class_no = data.value;
          $('.stu_class_no').val(stu_class_no);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              start_time:$('.start-item').val(),
              end_time:$('.end-item').val(),
              store_id:$(".store_id").val(),              
              stu_grades_no:$('.stu_grades_no').val(),
              stu_class_no:stu_class_no,
              status:$(".status").val(),
            }
          });
        });
        // 选择状态
        form.on('select(status)', function(data){
          var status = data.value;
          $('.status').val(status);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              status: status,
              start_time: $('.start-item').val(),
              end_time:$('.end-item').val(),
              store_id:$(".store_id").val(),              
              stu_grades_no:$('.stu_grades_no').val(),
              stu_class_no:$('.stu_class_no').val()
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
                start_time: value,
                end_time:$('.end-item').val(),
                store_id:$(".store_id").val(),              
                stu_grades_no:$('.stu_grades_no').val(),
                stu_class_no:$('.stu_class_no').val(),
                status:$(".status").val(),
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
                start_time: $('.start-item').val(),
                end_time:value,
                store_id:$(".store_id").val(),              
                stu_grades_no:$('.stu_grades_no').val(),
                stu_class_no:$('.stu_class_no').val(),
                status:$(".status").val(),
              }
            });
          }
        });

        

        


    });

  </script>

</body>
</html>