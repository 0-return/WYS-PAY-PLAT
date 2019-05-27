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
                   
                  </div>  
                </div>
              </div>
            </div>
          </div>

          <!-- 统计数据------------------------------------------------------------ -->
                 
          <div class="layui-row layui-col-space15">
            <div class="layui-col-sm6 layui-col-md4">
              <div class="layui-card">
                <div class="layui-card-header">
                  消费流水
                  <span class="layui-badge layui-bg-blue layuiadmin-badge">元</span>
                </div>
                <div class="layui-card-body layuiadmin-card-list">
                  <p class="layuiadmin-big-font one"></p>            
                </div>
              </div>
            </div>
            <div class="layui-col-sm6 layui-col-md4">
              <div class="layui-card">
                <div class="layui-card-header">
                  押金流水
                  <span class="layui-badge layui-bg-black layuiadmin-badge">元</span>
                </div>
                <div class="layui-card-body layuiadmin-card-list">
                  <p class="layuiadmin-big-font two"></p>            
                </div>
              </div>
            </div>
            <div class="layui-col-sm6 layui-col-md4">
              <div class="layui-card">
                <div class="layui-card-header">
                  退款流水
                  <span class="layui-badge layui-bg-orange layuiadmin-badge">元</span>
                </div>
                <div class="layui-card-body layuiadmin-card-list">
                  <p class="layuiadmin-big-font three"></p>            
                </div>
              </div>
            </div>
                 
          </div>
        </div>
      </div>
    </div>
  </div>



  <input type="hidden" class="store_id">
  <input type="hidden" class="merchant_id">
  <input type="hidden" class="user_id">
  <input type="hidden" class="ways_source">
  <input type="hidden" class="start_time">
  <input type="hidden" class="end_time">

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
            search()
        })

        function search(){
          $.post("{{url('/api/deposit/pay_order_count')}}",
          {
            token:token, 
            user_id:$('.user_id').val(),
            store_id:$('.store_id').val(),
            merchant_id:$('.merchant_id').val(),
            ways_source:$('.ways_source').val(),
            time_start:$('.start_time').val(),
            time_end:$('.end_time').val(),

          },function(res){
            console.log(res);
            $('.one').html(res.data.deposit_pay_amount)
            $('.two').html(res.data.deposit_all_amount)
            $('.three').html(res.data.deposit_refund_amount)
              
          },"json");
        }

        


        // 选择门店
        $.ajax({
            url : "{{url('/api/user/store_lists')}}",
            data : {token:token,l:100},
            type : 'post',
            success : function(data) {
                // console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                      optionStr += "<option value='" + data.data[i].store_id + "'>" + data.data[i].store_name + "</option>";
                    }    
                    $("#agent").append('<option value="">选择门店</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
 
     

       

        // 选择门店
        form.on('select(agent)', function(data){
          var store_id = data.value;
          $('.store_id').val(store_id);
          search()
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
          var merchant_id = data.value;
          $('.merchant_id').val(merchant_id);
          search()          
        });
        
        
        
        
        // 选择支付类型
        form.on('select(type)', function(data){
          var ways_source = data.value;
          $('.ways_source').val(ways_source);
          search()
        });



        laydate.render({
          elem: '.start-item'
          ,type: 'datetime'
          ,done: function(value){
            $('.start_time').val(value)
            search()
          }
        });

        laydate.render({
          elem: '.end-item'
          ,type: 'datetime'
          ,done: function(value){
            $('.end_time').val(value)
            search()
          }
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
          $('.user_id').val($(this).attr('data'))
          $('.userbox').hide()

          search()

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
                      $("#agent").html('');
                      $("#agent").append('<option value="">选择门店</option>'+optionStr);
                      layui.form.render('select');
              },
              error : function(data) {
                  alert('查找板块报错');
              }
          });
        })

        

      

    });

  </script>

</body>
</html>





