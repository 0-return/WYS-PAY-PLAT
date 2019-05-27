<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>缴费情况统计</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">

        <div class="layui-fluid">
          <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
              <div class="layui-card"> 
                <div class="layui-card-header">缴费情况统计</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
<!--                     <div>
                      <a class="layui-btn layui-btn-primary" lay-href="{{url('/merchantpc/paymentitem')}}" style="display: inline-block;width: 120px;">收费项目列表</a>
                    </div> -->

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
                            <select name="template" id="template" lay-filter="template">
                                
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
                        <button class="layui-btn" style="margin-bottom: 4px;height:36px;line-height: 36px;" id="export">导出</button>
                      </div> 

                    </div>

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 金额取值 -->
                  <script type="text/html" id="money">
                    @{{ d.not_pay_amount }}
                  </script>
                  <!-- 金额取值 -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="export">导出明细</a>
                    
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
  <input type="hidden" class="stu_order_batch_no">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
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
            url : "{{url('/api/school/teacher/lst')}}",
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
            url : "{{url('/api/school/teacher/grade/lst')}}",
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
            url : "{{url('/api/school/teacher/class/lst')}}",
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
        // 选择缴费项目
        $.ajax({
            url : "{{url('/api/school/teacher/payitem/lst')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].stu_order_batch_no + "'>"
                            + data.data[i].template_name + "</option>";
                    }    
                    $("#template").append('<option value="">选择缴费项目</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });


        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/school/teacher/stat/pay')}}"
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
                {field:'charge_name', title: '缴费项目'}
                ,{field:'have_pay_amount',  title: '已缴金额'}
                ,{field:'have_pay_rs',  title: '已缴人数'}
                ,{field:'not_pay_rs',  title: '未缴人数'}
                ,{field:'not_pay_amount',  title: '未缴金额',templet:'#money'}
                ,{field:'tot_should_payer',  title: '应缴人数'}
                ,{field:'tot_should_pay', title: '应缴金额'}
                ,{width:100,align:'left', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
              content: "{{url('/merchantpc/examinepayment?')}}"+e.stu_order_batch_no
            });
          }else if(layEvent === 'see'){
            layer.open({
              type: 2,
              title: '审核',
              shade: false,
              maxmin: true,
              area: ['60%', '70%'],
              content: "{{url('/merchantpc/seepayment?')}}"+e.stu_order_batch_no
            });
          }else if(layEvent === 'export'){//导出
            window.location.href="{{url('/api/school/teacher/order/lst')}}"+"?token="+token+"&stu_order_batch_no="+e.stu_order_batch_no+"&excel="+"1"+"&file_name="+e.charge_name+"-明细";
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
              pay_start_time:$('.start-item').val(),
              pay_end_time:$('.end-item').val(),              
              stu_grades_no:$('.stu_grades_no').val(),
              stu_class_no:$('.stu_class_no').val(),
              stu_order_batch_no:$(".stu_order_batch_no").val()
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
              store_id: $(".store_id").val(),      
              pay_start_time:$('.start-item').val(),
              pay_end_time:$('.end-item').val(),              
              stu_grades_no:stu_grades_no,
              stu_class_no:$('.stu_class_no').val(),
              stu_order_batch_no:$(".stu_order_batch_no").val()
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
              store_id: $(".store_id").val(),      
              pay_start_time:$('.start-item').val(),
              pay_end_time:$('.end-item').val(),              
              stu_grades_no:$('.stu_grades_no').val(),
              stu_class_no:stu_class_no,
              stu_order_batch_no:$(".stu_order_batch_no").val()
            }
          });
        });
        
        form.on('select(template)', function(data){            
          stu_order_batch_no = data.value;  
          categoryName = data.elem[data.elem.selectedIndex].text; 
          $('.stu_order_batch_no').val(stu_order_batch_no); 
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_id: $(".store_id").val(),
              pay_start_time:$('.start-item').val(),
              pay_end_time:$('.end-item').val(),              
              stu_grades_no:$('.stu_grades_no').val(),
              stu_class_no:$('.stu_class_no').val(),
              stu_order_batch_no:stu_order_batch_no
            }
          });
        });


        // 时间-------------------------------------------------------------------------------

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
        // 导出++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $('#export').click(function(){
        var store_id=$('.store_id').val();
        var stu_grades_no=$('.stu_grades_no').val();
        var stu_class_no=$('.stu_class_no').val();
        var stu_order_batch_no=$('.stu_order_batch_no').val();
        var pay_start_time=$('.start-item').val();
        var pay_end_time=$('.end-item').val();

        window.location.href="{{url('/api/school/teacher/stat/pay')}}"+"?token="+token+"&store_id="+store_id+"&stu_grades_no="+stu_grades_no+"&stu_class_no="+stu_class_no+"&stu_order_batch_no="+stu_order_batch_no+"&pay_start_time="+pay_start_time+"&pay_end_time="+pay_end_time+"&export="+"1"+"&export_name="+"缴费记录管理列表";
        })
        
    });

  </script>

</body>
</html>