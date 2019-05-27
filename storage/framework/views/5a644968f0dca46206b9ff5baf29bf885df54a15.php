<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>打款查询</title>
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
    #code{
      width:160px;
      height:160px;
    }
    #code canvas{
      width:100%;
      height:100%;
    }
    .lineheight{
      line-height: 36px;
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
                <div class="layui-card-header">打款查询</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">                    

                    <!-- 选择业务员 -->
                    <div class="layui-form" lay-filter="component-form-group" style="">
                      <div class="layui-form-item">                       
                        <label class="layui-form-label" style="float:left;">选择通道</label>     
                        <div class="layui-input-block" style="margin-left:0;width:500px;float:left;">
                          <select name="passway" id="passway" lay-filter="passway">
                            <option value="mybank">网商银行</option>
                          </select>                            
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" style="display: inline-block;">                      
                      <div class="layui-form-item">              
                        <label class="layui-form-label">查询时间</label>           
                        <div class="layui-input-block" style="margin-left:0;width:500px;float:left;">                       
                          <input type="text" class="layui-input start-item test-item" placeholder="查询时间" lay-key="23">
                        </div>                                                
                      </div>
                    </div>
                    

                    <div class="layui-form-item">
                      <label class="layui-form-label">门店ID</label>
                      <div class="layui-input-block"  style="margin-left:0;width:500px;float:left;">
                        <input type="text" placeholder="请输入门店ID" autocomplete="off" class="layui-input item1">
                      </div>
                    </div>
                    

                    <div class="layui-form-item layui-layout-admin">
                      <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                          <button class="layui-btn submit site-demo-active" data-type="tabChange">查询打款</button>
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


  <input type="hidden" class="passway_id" value="">

<div id="edit_rate" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">查询时间:</label>
        <div class="layui-input-block">
            <div class="lineheight item11"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">门店名称:</label>
        <div class="layui-input-block">
            <div class="lineheight item12"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">打款金额:</label>
        <div class="layui-input-block">
            <div class="lineheight item13"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">打款状态:</label>
        <div class="layui-input-block">
            <div class="lineheight item14"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">打款时间:</label>
        <div class="layui-input-block">
            <div class="lineheight item15"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block" style=''>
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn submits">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

  <script src="<?php echo e(asset('/layuiadmin/layui/layui.js')); ?>"></script> 
  <script src="<?php echo e(asset('/layuiadmin/layui/jquery-2.1.4.js')); ?>"></script>
  <script src="<?php echo e(asset('/layuiadmin/layui/jquery.qrcode.min.js')); ?>"></script>
    <script>
    var token = localStorage.getItem("Usertoken");
   

    
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
                window.location.href="<?php echo e(url('/user/login')); ?>"; 
            }
        })
        
        
        // 选择业务员
        form.on('select(passway)', function(data){
          var passway_id = data.value;
          $('.passway_id').val(passway_id);          
        });

        laydate.render({
          elem: '.start-item'
          ,type: 'datetime'
          ,done: function(value){
           
          }
        });
      
        var index
        $('.submit').click(function(){
          
         
          $.post("<?php echo e(url('/api/user/dk_select')); ?>",
            {
              token:token,
              store_id:$('.item1').val(),
              time:$('.start-item').val(),
              company:'mybank',

            },function(res){
                console.log(res);
                if(res.status==1){
                  // layer.msg(res.message, {
                  //     offset: '15px'
                  //     ,icon: 1
                  //     ,time: 2000
                  // });

                  $('.item11').html(res.data.time)
                  $('.item12').html(res.data.store_name)
                  $('.item13').html(res.data.total_amount)
                  $('.item14').html(res.data.dk_desc)
                  $('.item15').html(res.data.dk_time)

                  index=layer.open({
                    type: 1,
                    title: false,
                    closeBtn: 0,
                    area: '516px',
                    skin: 'layui-layer-nobg', //没有背景色
                    shadeClose: true,
                    content: $('#edit_rate')
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

        $('.submits').click(function(){
          console.log('关闭')
          layer.close(index)
        })

        
    });

  </script>

</body>
</html>





