<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>流水查询</title>
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
                <div class="layui-card-header">交易流水列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <!-- 选择业务员 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="agent" id="agent" lay-filter="agent" lay-search>
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <!-- 学校 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="schooltype" id="schooltype" lay-filter="schooltype" lay-search>
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <!-- 支付状态 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="status" id="status" lay-filter="status">
                              <option value="">选择支付状态</option>
                              <option value="1">成功</option>
                              <option value="2">等待支付</option>
                              <option value="3">失败</option>
                              <option value="4">关闭</option>
                              <option value="5">退款中</option>
                              <option value="6">已退款</option>
                              <option value="7">有退款</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    <!-- 支付类型 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="type" id="type" lay-filter="type">
                              <option value="">选择支付类型</option>
                              <option value="alipay">支付宝</option>
                              <option value="weixin">微信</option>
                              <option value="jd">京东</option>
                              <option value="unionpay">银联刷卡</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    <!-- 排序 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="sort" id="sort" lay-filter="sort">
                                <option value="">选择排序</option>
                                <option value="desc">金额从大到小</option>
                                <option value="asc">金额从小到大</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    
                  
                    <!-- 缴费时间 -->
                    <div class="layui-form" style="display: inline-block;">                      
                      <div class="layui-form-item">                          
                        <div class="layui-inline">
                          
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item" placeholder="订单开始时间" lay-key="23">
                          </div>
                        </div>
                        <div class="layui-inline">
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input end-item test-item" placeholder="订单结束时间" lay-key="24">
                          </div>
                        </div>
                        
                      </div>
                    </div>
                    <!-- 搜索 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:600px;display: inline-block;">
                      <div class="layui-form-item">
                          
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="tradeno" placeholder="请输入订单号" autocomplete="off" class="layui-input dingdan">
                            </div>
                          </div>    
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="paytradeno" placeholder="支付条码单号" autocomplete="off" class="layui-input tiaoma">
                            </div>
                          </div>                        
                          
                          <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-list" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                          </div>
                          <button class="layui-btn export"  style="margin-bottom: 4px;height:36px;line-height: 36px;">导出</button>   <!-- onclick="exportdata()" -->
                        </div>
                    </div>

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="statusTap">
                    {{#  if(d.pay_status == 1){ }}
                      <span class="cur">{{ d.pay_status_desc }}</span>
                    {{#  } else { }}
                      {{ d.pay_status_desc }}
                    {{#  } }}
                  </script>
                  <!-- 判断状态 -->
                  <script type="text/html" id="paymoney">
                    {{ d.rate }}%
                  </script>
                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    <a class="layui-btn layui-btn-normal layui-btn-xs tongbu" lay-event="tongbu">同步状态</a>
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
  <input type="hidden" class="sort">
  <input type="hidden" class="stu_class_no">

  <input type="hidden" class="stu_order_batch_no">
  <input type="hidden" class="user_id">

  <input type="hidden" class="pay_status">
  <input type="hidden" class="pay_type">

  <script src="<?php echo e(asset('/layuiadmin/layui/layui.js')); ?>"></script> 
  <script src="<?php echo e(asset('/layuiadmin/layui/jquery-2.1.4.js')); ?>"></script>
    <script>
    var token = localStorage.getItem("Publictoken");
    var str=location.search;
    var store_id=str.split('?')[1];

    
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
              window.location.href="<?php echo e(url('/mb/login')); ?>"; 
          }
      })
        // 选择门店
        $.ajax({
            url : "<?php echo e(url('/api/merchant/store_lists')); ?>",
            data : {token:token,l:100},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].store_id + "'>" + data.data[i].store_name + "</option>";

                        // optionStr += "<option value='" + data.data[i].store_id + "' "+((store_id==data.data[i].store_id)?"selected":"")+">" + data.data[i].store_name + "</option>";
                    }    
                    $("#agent").append('<option value="">选择门店</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
 
        
        // 选择收银员
        // $.ajax({
        //     url : "<?php echo e(url('/api/user/get_sub_users')); ?>",
        //     data : {token:token,l:100},
        //     type : 'post',
        //     success : function(data) {
        //         console.log(data);
        //         var optionStr = "";
        //             for(var i=0;i<data.data.length;i++){
        //                 optionStr += "<option value='" + data.data[i].user_id + "'>"
        //                     + data.data[i].user_name + "</option>";
        //             }    
        //             $("#agent").append('<option value="">选择业务员</option>'+optionStr);
        //             layui.form.render('select');
        //     },
        //     error : function(data) {
        //         alert('查找板块报错');
        //     }
        // });

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "<?php echo e(url('/api/merchant/order')); ?>"
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
              {field:'out_trade_no', title: '订单号'}
              ,{field:'store_name', title: '门店'}
              ,{field:'shop_price', title: '订单金额'}
              ,{field:'total_amount',  title: '支付金额'}   
              ,{field:'total_amount',  title: '费率',templet:'#paymoney'}                            
              ,{field:'pay_status_desc', title: '状态',templet:'#statusTap'}
              ,{field:'ways_source_desc',  title: '支付方式'} 
              ,{field:'company',  title: '通道类型'}
              ,{field:'remark',  title: '备注'}
              ,{field:'created_at',  title: '下单时间'}
              ,{field:'pay_time',  title: '支付时间'}              
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

          if(layEvent === 'tongbu'){ //审核
            $.post("<?php echo e(url('/api/basequery/update_order')); ?>",
            {
                token:token,
                store_id:e.store_id,
                out_trade_no:e.out_trade_no
            },function(res){
                console.log(res);
                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 2000
                    });
                }else{
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 2000
                    });
                }
            },"json");
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

        // 选择门店
        form.on('select(agent)', function(data){
          var store_id = data.value;
          $('.store_id').val(store_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_id: $(".store_id").val(),                            
              
            }
          });
          // 选择收银员
          $.ajax({
              url : "<?php echo e(url('/api/merchant/merchant_lists')); ?>",
              data : {token:token,store_id:store_id,l:100},
              type : 'post',
              success : function(data) {
                  console.log(data);
                  var optionStr = "";
                      for(var i=0;i<data.data.length;i++){
                          optionStr += "<option value='" + data.data[i].merchant_id + "'>"
                            + data.data[i].name + "</option>";
                      }    
                      $("#schooltype").html('');
                      $("#schooltype").append('<option value="">选择收银员</option>'+optionStr);
                      layui.form.render('select');
              },
              error : function(data) {
                  alert('查找板块报错');
              }
          });

        });
        
        // 选择收银员
        form.on('select(schooltype)', function(data){
          var user_id = data.value;
          $('.user_id').val(user_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_id: $('.store_id').val(),                            
              merchant_id:$('.user_id').val()
            }
          });
        });
        // 选择排序
        form.on('select(sort)', function(data){
          var sort = data.value;

          $('.sort').val(sort);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              sort:sort
            }
          });
        });
        
        
        // 选择状态
        form.on('select(status)', function(data){
          var pay_status = data.value;
          $('.pay_status').val(pay_status);
          //执行重载
          table.reload('test-table-page', {
            where: {               
              pay_status:pay_status,
            }
          });
        });
        // 选择支付类型
        form.on('select(type)', function(data){
          var pay_type = data.value;
          $('.pay_type').val(pay_type);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              ways_source:pay_type
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
                time_start:value,
                time_end:$('.end-item').val()
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
                time_start:$('.start-item').val(),
                time_end:value
              }
            });
          }
        });

        form.on('submit(LAY-app-contlist-search)', function(data){
          var paytradeno = data.field.paytradeno;  
          var out_trade_no = data.field.tradeno;  
          console.log(data);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              out_trade_no:out_trade_no,
              trade_no:paytradeno
            }
          });
        });

        // 导出
        function exportdata(){
          var store_id=$('.store_id').val();
          var merchant_id=$('.user_id').val();
          var sort=$('.sort').val();          
          var pay_status=$('.pay_status').val();
          var ways_source=$('.pay_type').val();

          var time_start=$('.start-item').val();
          var time_end=$('.end-item').val();
          
          var out_trade_no=$('.danhao').val();
          var trade_no=$('.tiaoma').val();

          window.location.href="<?php echo e(url('/api/export/MerchantOrderExcelDown')); ?>"+"?token="+token+"&store_id="+store_id+"&merchant_id="+merchant_id+"&sort="+sort+"&pay_status="+pay_status+"&ways_source="+ways_source+"&time_start="+time_start+"&time_end="+time_end+"&out_trade_no="+out_trade_no+"&trade_no="+trade_no;     

        }

        $('.export').click(function(){
          var store_id=$('.store_id').val();
          var merchant_id=$('.user_id').val();
          var sort=$('.sort').val();          
          var pay_status=$('.pay_status').val();
          var ways_source=$('.pay_type').val();

          var time_start=$('.start-item').val();
          var time_end=$('.end-item').val();
          
          var out_trade_no=$('.danhao').val();
          var trade_no=$('.tiaoma').val();

          window.location.href="<?php echo e(url('/api/export/MerchantOrderExcelDown')); ?>"+"?token="+token+"&store_id="+store_id+"&merchant_id="+merchant_id+"&sort="+sort+"&pay_status="+pay_status+"&ways_source="+ways_source+"&time_start="+time_start+"&time_end="+time_end+"&out_trade_no="+out_trade_no+"&trade_no="+trade_no;     
        })

    });

  </script>

</body>
</html>





