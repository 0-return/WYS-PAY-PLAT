<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>提现设置</title>
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
    #code{
      width:160px;
      height:160px;
    }
    #code canvas{
      width:100%;
      height:100%;
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
                <div class="layui-card-header">提现设置</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">                    

                    <!-- 选择业务员 -->
                    <div class="layui-form" lay-filter="component-form-group" style="">
                      <div class="layui-form-item">                       
                        <label class="layui-form-label" style="float:left;">平台对象</label>     
                        <div class="layui-input-block" style="margin-left:0;width:500px;float:left;">
                          <select name="platform" id="platform" lay-filter="platform" lay-search>
                            <option value="1">服务商</option>
                            <option value="2">商户</option>
                          </select>                            
                        </div>
                      </div>
                    </div>
                    

                    <div class="layui-form-item">
                      <label class="layui-form-label">单笔提现最小金额</label>
                      <div class="layui-input-block"  style="margin-left:0;width:500px;float:left;">
                        <input type="text" placeholder="请输入单笔提现最小金额" autocomplete="off" class="layui-input item1">
                      </div>
                    </div>
                    <div class="layui-form-item">
                      <label class="layui-form-label">单笔提现最大金额</label>
                      <div class="layui-input-block"  style="margin-left:0;width:500px;float:left;">
                        <input type="text" placeholder="请输入单笔提现最大金额" autocomplete="off" class="layui-input item2">
                      </div>
                    </div>
                    <div class="layui-form-item">
                      <label class="layui-form-label">单笔提现手续费</label>
                      <div class="layui-input-block"  style="margin-left:0;width:500px;float:left;">
                        <input type="text" placeholder="请输入单笔提现手续费" autocomplete="off" class="layui-input item3">
                      </div>
                    </div>
                    <div class="layui-form-item">
                      <label class="layui-form-label">提现备注</label>
                      <div class="layui-input-block"  style="margin-left:0;width:500px;float:left;">
                        <input type="text" placeholder="请输入提现备注" autocomplete="off" class="layui-input item4">
                      </div>
                    </div>

                    <div class="layui-form" lay-filter="component-form-group" style="">
                      <div class="layui-form-item">                       
                        <label class="layui-form-label" style="float:left;">转出账户</label>     
                        <div class="layui-input-block" style="margin-left:0;width:500px;float:left;">
                          <select name="account" id="account" lay-filter="account" lay-search>
                              <option value="1">支付宝</option>
                              <!-- <option value="2">微信</option> -->
                          </select>                            
                        </div>
                      </div>
                    </div>

                    <div class="layui-form-item">
                      <label class="layui-form-label"></label>
                      <div class="layui-input-block" style="margin-left:0;width:500px;float:left;">
                        <div id="code"></div>
                        <div class="layui-form-mid layui-word-aux">备注：请使用转出支付宝账户扫码支付宝授权</div>
                      </div>
                    </div>



                    <div class="layui-form-item layui-layout-admin">
                      <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                          <button class="layui-btn submit site-demo-active" data-type="tabChange">保存修改</button>
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


  <input type="hidden" class="platform_id" value="1">
  <input type="hidden" class="account_id" value="1">



  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
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
                window.location.href="{{url('/user/login')}}"; 
            }
        })

        $.post("{{url('/api/wallet/settlement_configs')}}",
        {
          token:token,
          dx:'1',

        },function(res){
          console.log(res);
          $('#code').html('');
          $('#code').qrcode(res.data.alipay_out_qr_url);

          $('.item1').val(res.data.s_amount);
          $('.item2').val(res.data.e_amount);
          $('.item3').val(res.data.sxf_amount);
          $('.item4').val(res.data.tx_remark);
        },"json");
       

    

        
        // 选择赏金来源
        form.on('select(platform)', function(data){
          var platform_id = data.value;
          $('.platform_id').val(platform_id);  
          $.post("{{url('/api/wallet/settlement_configs')}}",
          {
            token:token,
            dx:platform_id,

          },function(res){
            console.log(res);
            $('#code').html('');
            $('#code').qrcode(res.data.alipay_out_qr_url);

            $('.item1').val(res.data.s_amount);
            $('.item2').val(res.data.e_amount);
            $('.item3').val(res.data.sxf_amount);
            $('.item4').val(res.data.tx_remark);
          },"json");        
        });
        
        
        // 选择业务员
        form.on('select(account)', function(data){
          var account_id = data.value;
          $('.account_id').val(account_id);          
        });
      

        $('.submit').click(function(){
         
          $.post("{{url('/api/wallet/settlement_configs')}}",
            {
              token:token,
              dx:$('.platform_id').val(),
              s_amount:$('.item1').val(),
              e_amount:$('.item2').val(),
              sxf_amount:$('.item3').val(),
              tx_remark:$('.item4').val(),
              out_type:$('.account_id').val()

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





