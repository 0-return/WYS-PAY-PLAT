<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>押金流水</title>
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
    .way{height:38px;line-height: 38px;}

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }
    input[type="number"]{
        -moz-appearance: textfield;
    }

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
                <div class="layui-card-header">交易流水列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                          <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入业务员名称" class="layui-input transfer">

                          <div class="userbox" style='display: none'></div>
                        </div>
                      </div>
                    </div>
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
                              <option value="">选择订单状态</option>
                              <option value="1-2">押金冻结中</option>
                              <option value="1-1">押金支付完成 </option>
                              <option value="3-2">押金冻结失败</option>
                              <option value="1-4">已退款</option>
                              <option value="4-2">已撤销</option>
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
                              <option value="unionpay">银联刷卡</option>
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
                              <input type="text" name="tradenos" placeholder="请输入订单号"  class="layui-input dingdan">
                            </div>
                          </div>    
                                               
                          
                          <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-list" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                          </div>
                          <!-- <button class="layui-btn export"  style="margin-bottom: 4px;height:36px;line-height: 36px;">导出</button> -->   <!-- onclick="exportdata()" -->
                        </div>
                    </div>

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="payTap">
                    @{{#  if(d.pay_status == 1){ }}
                      <span class="cur">@{{ d.pay_status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.pay_status_desc }}
                    @{{#  } }}
                  </script>
                  
                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    <a class="layui-btn layui-btn-normal layui-btn-xs success" lay-event="success">押金完成</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs cancel" lay-event="cancel">押金撤销</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs refund" lay-event="refund">退款</a>
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- 押金完成 -->
  <div id="deposit_success" class="hide" style="display: none;background-color: #fff;">
    <div class="layui-card-body" style="padding: 15px;">
      <div class="layui-form">
        <div class="layui-form-item">
          <label class="layui-form-label">押金完成</label>          
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">押金金额:</label>
          <div class="layui-input-block">
              <div class="way deposit_money"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">支付消费金额:</label>
          <div class="layui-input-block">
              <input type="number" placeholder="请输入支付消费金额" class="layui-input one">
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-input-block">
              <div class="layui-footer" style="left: 0;">
                  <button class="layui-btn submit1">确定</button>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- 押金撤销 -->
  <div id="deposit_revoke" class="hide" style="display: none;background-color: #fff;">
    <div class="layui-card-body" style="padding: 15px;">
      <div class="layui-form">
        <div class="layui-form-item">
          <label class="layui-form-label">押金撤销</label>          
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">押金金额:</label>
          <div class="layui-input-block">
              <div class="way deposit_money"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">支付密码:</label>
          <div class="layui-input-block">
              <input type="password" placeholder="请输入支付密码" class="layui-input two">
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-input-block">
              <div class="layui-footer" style="left: 0;">
                  <button class="layui-btn submit2">确定</button>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- 退款 -->
  <div id="deposit_refund" class="hide" style="display: none;background-color: #fff;">
    <div class="layui-card-body" style="padding: 15px;">
      <div class="layui-form">
        <div class="layui-form-item">
          <label class="layui-form-label">押金退款</label>          
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">支付金额:</label>
          <div class="layui-input-block">
              <div class="way pay_money"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">退款金额:</label>
          <div class="layui-input-block">
              <input type="number" placeholder="请输入退款金额" class="layui-input three">
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">退款金额:</label>
          <div class="layui-input-block">
              <div class="way deposit_money_t"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">支付密码:</label>
          <div class="layui-input-block">
              <input type="password" placeholder="请输入支付密码" class="layui-input four">
          </div>
        </div>
        <div class="layui-form-item">
          <div class="layui-input-block">
              <div class="layui-footer" style="left: 0;">
                  <button class="layui-btn submit3">确定</button>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>


  <input type="hidden" class="store_id">
  <input type="hidden" class="user_id">
  <input type="hidden" class="order_status">
  <input type="hidden" class="ways_source">

  <input type="hidden" class="js_store_id">
  <input type="hidden" class="js_out_order_no">
  <input type="hidden" class="js_out_trade_no">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
    <script>
    var token = localStorage.getItem("Usertoken");
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
              window.location.href="{{url('/user/login')}}"; 
          }
      })
        // 选择门店
        $.ajax({
            url : "{{url('/api/user/store_lists')}}",
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
 
     

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/deposit/pay_order_list')}}"
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
              ,{field:'amount', title: '押金金额'}
              ,{field:'pay_amount',  title: '支付金额'}   
              ,{field:'refund_amount',  title: '退款金额'}                            
              ,{field:'deposit_status_desc', title: '押金状态'}
              ,{field:'pay_status_desc',  title: '消费状态'} 
              ,{field:'ways_source_desc',  title: '支付类型'}
              ,{field:'updated_at',  title: '订单更新时间'}           
              // ,{width:220,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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



       

        // 选择门店
        form.on('select(agent)', function(data){
          var store_id = data.value;
          $('.store_id').val(store_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_id: $(".store_id").val(),  
            }
            ,page: {
              curr: 1
            }
          });
          // 选择收银员
          $.ajax({
              url : "{{url('/api/basequery/merchant_lists')}}",
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
            ,page: {
              curr: 1
            }
          });
        });
        
        
        
        // 选择状态
        form.on('select(status)', function(data){
          var order_status = data.value;
          $('.order_status').val(order_status);
          //执行重载
          table.reload('test-table-page', {
            where: {               
              order_status:order_status,
            }
            ,page: {
              curr: 1
            }
          });
        });
        // 选择支付类型
        form.on('select(type)', function(data){
          var ways_source = data.value;
          $('.ways_source').val(ways_source);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              ways_source:ways_source
            }
            ,page: {
              curr: 1
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
              ,page: {
                curr: 1
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
              ,page: {
                curr: 1
              }
            });
          }
        });

        form.on('submit(LAY-app-contlist-search)', function(data){        
          var out_trade_no = data.field.tradenos;  
          console.log(data);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              out_trade_no:out_trade_no,
            }
            ,page: {
              curr: 1
            }
          });
        });

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
                      $("#schooltype").html('');
                      $("#schooltype").append('<option value="">选择门店</option>'+optionStr);
                      layui.form.render('select');
              },
              error : function(data) {
                  alert('查找板块报错');
              }
          });
        })

        

        // 导出
        function exportdata(){
          var store_id=$('.store_id').val();
          var merchant_id=$('.user_id').val();
          var sort=$('.sort').val();          
          var order_status=$('.order_status').val();
          var ways_source=$('.ways_source').val();

          var time_start=$('.start-item').val();
          var time_end=$('.end-item').val();
          
          var out_trade_no=$('.danhao').val();
          var trade_no=$('.tiaoma').val();

          window.location.href="{{url('/api/export/MerchantOrderExcelDown')}}"+"?token="+token+"&store_id="+store_id+"&merchant_id="+merchant_id+"&order_status="+order_status+"&ways_source="+ways_source+"&time_start="+time_start+"&time_end="+time_end+"&out_trade_no="+out_trade_no;     

        }

        $('.export').click(function(){
          var store_id=$('.store_id').val();
          var merchant_id=$('.user_id').val();        
          var order_status=$('.order_status').val();
          var ways_source=$('.ways_source').val();

          var time_start=$('.start-item').val();
          var time_end=$('.end-item').val();
          
          var out_trade_no=$('.dingdan').val();

          window.location.href="{{url('/api/export/MerchantOrderExcelDown')}}"+"?token="+token+"&store_id="+store_id+"&merchant_id="+merchant_id+"&order_status="+order_status+"&ways_source="+ways_source+"&time_start="+time_start+"&time_end="+time_end+"&out_trade_no="+out_trade_no;     
        })

    });

  </script>

</body>
</html>





