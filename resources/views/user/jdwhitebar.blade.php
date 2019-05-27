<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>白条数据录入</title>
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
                <div class="layui-card-header">白条数据录入</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">

                    <!-- 缴费时间 -->
                    <div class="layui-form" style="display: inline-block;">                      
                      <div class="layui-form-item"> 
                        <label class="layui-form-label" style="display: inline-block;float:left;">选择时间</label>                         
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
                    
                    <div class="layui-form-item">
                      <label class="layui-form-label">推广者手机号</label>
                      <div class="layui-input-block">
                        <input type="text" placeholder="请输入推广者手机号" autocomplete="off" class="layui-input input1">
                      </div>
                    </div>
                    <div class="layui-form-item">
                      <label class="layui-form-label">推广者人数</label>
                      <div class="layui-input-block">
                        <input type="text" placeholder="请输入推广者人数" autocomplete="off" class="layui-input input2">
                      </div>
                    </div>
                    <div class="layui-form-item">
                      <label class="layui-form-label">推广金额</label>
                      <div class="layui-input-block">
                        <input type="text" placeholder="请输入推广金额" autocomplete="off" class="layui-input input3">
                      </div>
                    </div>
                    <div class="layui-form-item">
                      <label class="layui-form-label">支付密码</label>
                      <div class="layui-input-block">
                        <input type="text" placeholder="请输入支付密码" autocomplete="off" class="layui-input input4">
                      </div>
                    </div>
                    
                    
                    <div class="layui-form-item layui-layout-admin">
                      <div class="layui-input-block">
                          <div class="layui-footer" style="left: 0;">
                              <button class="layui-btn submit site-demo-active" data-type="tabChange">确认结算</button>
                          </div>
                      </div>
                    </div>

                  </div>
                  
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  
  <div id="open_passway" class="hide" style="display: none;background-color: #fff;">
    <div class="layui-card-body" style="padding: 15px;">
      <div class="layui-form">
        <div class="layui-form-item">
          <label class="layui-form-label">通道:</label>
          <div class="layui-input-block">
              <div class="way"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">商户号:</label>
          <div class="layui-input-block">
              <input type="number" placeholder="请输入商户号" class="layui-input wx_sub_merchant_id">
          </div>
        </div>
        <div class="layui-form-item" style="text-align: center;">
          <div class="layui-input-block" >
              <div class="layui-footer" style="left: 0;">
                  <button class="layui-btn open_way">确定</button>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="edit_rate" class="hide" style="display: none;background-color: #fff;">
    <div class="layui-card-body" style="padding: 15px;">
      <div class="layui-form">
        <div class="layui-form-item" style='text-align: center;'>
          <label>请再次确认信息填写是否有误</label>
          
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">推广开始时间:</label>
          <div class="layui-input-block" style='padding: 9px 15px;'>
              <div class="way1"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">推广结束时间:</label>
          <div class="layui-input-block" style='padding: 9px 15px;'>
              <div class="way2"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">推广者手机号:</label>
          <div class="layui-input-block" style='padding: 9px 15px;'>
              <div class="way3"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">推广者人数:</label>
          <div class="layui-input-block" style='padding: 9px 15px;'>
              <div class="way4"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">推广金额:</label>
          <div class="layui-input-block" style='padding: 9px 15px;'>
              <div class="way5"></div>
          </div>
        </div>
        <div class="layui-form-item">
          <label class="layui-form-label">支付密码:</label>
          <div class="layui-input-block" style='padding: 9px 15px;'>
              <div class="way6"></div>
          </div>
        </div>
        
        <div class="layui-form-item">
          <div class="layui-input-block">
              <div class="layui-footer" style="left: 0;">
                  <button class="layui-btn submits">确定</button>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>

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

       
    // 时间++++++++++++++++++++++++++++++++++++++++++++++++
        laydate.render({
          elem: '.start-item'
          ,type: 'datetime'
          ,done: function(value){
            
          }
        });

        laydate.render({
          elem: '.end-item'
          ,type: 'datetime'
          ,done: function(value){
           
        
          }
        });

        
        $('.submit').click(function(){ 
          $('.way1').html($('.start-item').val())
          $('.way2').html($('.end-item').val())
          $('.way3').html($('.input1').val())
          $('.way4').html($('.input2').val())
          $('.way5').html($('.input3').val())
          $('.way6').html($('.input4').val())

          layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: '516px',
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: $('#edit_rate')
          });        
          
        })

        
        $('.submits').click(function(){
          $.post("{{url('/api/huodong/jdbt')}}",
          {
            token:token,
            phone:$('.input1').val(),
            money:$('.input2').val(),
            number:$('.input3').val(),
            time_start:$('.start-item').val(),
            time_end:$('.end-item').val(),
            pay_password:$('.input4').val()

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
        })
        
    });

  </script>

</body>
</html>





