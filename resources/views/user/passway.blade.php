<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>通道管理</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shua{background-color: #ed9c3a;}
    .sao{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .tongbu{background-color: #4c9ef8;color:#fff;}
    .cur{color:#009688;}
    .way{height:38px;line-height: 38px;}
    .open{}
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
    }
    input[type="number"]{
        -moz-appearance: textfield;
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
                <div class="layui-card-header">通道管理列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="rate">
                    @{{ d.rate }}%
                  </script>
                  <!-- 判断状态 -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small" id="passway">   
                    @{{#  if(d.ways_type == 8005 || d.ways_type == 6005){ }}
                      <a class="layui-btn layui-btn-danger layui-btn-xs shua" lay-event="shua">修改费率</a>
                    @{{#  } else if(d.ways_type == 8004 || d.ways_type == 6004){ }}
                      <a class="layui-btn layui-btn-danger layui-btn-xs sao" lay-event="sao">修改费率</a>
                    @{{#  } else { }}
                      <a class="layui-btn layui-btn-danger layui-btn-xs edit" lay-event="edit">修改费率</a>
                    @{{#  } }}                 
                    


                    @{{#  if(d.company == "jdjr"){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs pay" lay-event="pay">商户号</a>
                    @{{#  } else if(d.company == "alipay"){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs alipay" lay-event="alipay">商户号</a>
                    @{{#  } else if(d.company == "newland"){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs newland" lay-event="newland">商户号</a>
                    @{{#  } else if(d.company == "herongtong"){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs hrt" lay-event="hrt">商户号</a>
                    @{{#  } else if(d.company == "ltf"){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs ltf" lay-event="ltf">商户号</a>
                    @{{#  } else { }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs open" lay-event="open">商户号</a>
                    @{{#  } }}


                    @{{#  if(d.company == "mybank" || d.company == "jdjr" || d.company == "newland" || d.company == "herongtong" || d.company == "fuiou"){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs js" lay-event="js">开通通道</a>
                    @{{#  } else if(d.company == "alipay"){ }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs alipaycode" lay-event="alipaycode">开通通道</a>
                    @{{#  } else { }}
                      <a class="layui-btn layui-btn-normal layui-btn-xs open" lay-event="open">开通通道</a>
                    @{{#  } }}
                                       
                  </script>


                  
                </div>                
              </div>
              <a class="layui-btn layui-btn-normal layui-btn-xs sort" lay-event="sort" lay-href='' style="margin:20px;">修改收款顺序</a>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
<div id="edit_rate" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">通道:</label>
        <div class="layui-input-block">
            <div class="way"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">费率:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入修改费率" class="layui-input rate">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn submit">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="edit_shua" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">门店名称:</label>
        <div class="layui-input-block" style="line-height: 36px;">
            <div class="agent"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">通道:</label>
        <div class="layui-input-block" style="line-height: 36px;">
            <div class="ways"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">贷记卡费率:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入贷记卡费率" class="layui-input rate1">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">借记卡费率:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入借记卡费率" class="layui-input rate2">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">借记卡封顶:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入借记卡封顶" class="layui-input rate3">
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


  <input type="hidden" class="types">
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
            <input type="text" placeholder="请输入商户号" class="layui-input wx_sub_merchant_id">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn open_way">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="newland" class="hide" style="display: none;background-color: #fff;">
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
            <input type="number" placeholder="请输入商户号" class="layui-input wxmerchantda">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">商户密钥:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入商户密钥" class="layui-input wxmerchantdb">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">设备号:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入设备号" class="layui-input wxmerchantdc">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn save_newland">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div id="open_alipay" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;text-align: center;">
    <div style="padding-bottom:10px;">支付宝授权</div>
    <div class="img" id="code"><img src=""></div>
  </div>
</div>
<div id="open_js" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      
      <div class="layui-form-item xingzhi" style="margin:20px 0 30px 0">
        <label class="layui-form-label">结算类型</label>
        <div class="layui-input-block">
            <select name="open" id="open" lay-filter="open">
                
            </select>
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn open_jiesuan">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="edit_sao" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">门店名称:</label>
        <div class="layui-input-block" style="line-height: 36px;">
            <div class="agent"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">通道:</label>
        <div class="layui-input-block" style="line-height: 36px;">
            <div class="ways"></div>
        </div>
      </div>

      <div style="padding: 20px;">金额 0-1000</div>
      <div class="layui-form-item">
        <label class="layui-form-label">贷记卡费率:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入贷记卡费率" class="layui-input rates1">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">借记卡费率:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入借记卡费率" class="layui-input rates2">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">借记卡封顶:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入借记卡封顶" class="layui-input rates3">
        </div>
      </div>

      <div style="padding: 20px;">金额 大于1000</div>
      <div class="layui-form-item">
        <label class="layui-form-label">贷记卡费率:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入贷记卡费率" class="layui-input rates4">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">借记卡费率:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入借记卡费率" class="layui-input rates5">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">借记卡封顶:</label>
        <div class="layui-input-block">
            <input type="number" placeholder="请输入借记卡封顶" class="layui-input rates6">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn submitsao">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>


<div id="open_passways" class="hide" style="display: none;background-color: #fff;">
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
            <input type="text" placeholder="请输入商户号" class="layui-input wx_sub_hrt">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn open_ways">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="open_ltf" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">通道:</label>
        <div class="layui-input-block">
            <div class="way"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">merchantCode:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入merchantCode" class="layui-input ltf1">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">md_key:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入md_key" class="layui-input ltf2">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">appId:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入appId" class="layui-input ltf3">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn open_ltf_save">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" class="ltf_type">
</div>

<div id="alipay_store_id" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">通道:</label>
        <div class="layui-input-block" style="line-height: 36px;">
            <div class="alipayway"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">口碑外部ID:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入口碑外部ID" class="layui-input wx_sub_alipay_wai">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">口碑门店ID:</label>
        <div class="layui-input-block">
            <input type="text" placeholder="请输入口碑门店ID" class="layui-input wx_sub_alipay">
        </div>
      </div>
      <div class="layui-form-item">
        <div class="layui-input-block">
            <div class="layui-footer" style="left: 0;">
                <button class="layui-btn open_alipay_store_id">确定</button>
            </div>
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" class="alipaytype">
</div>
<input type="hidden" class="type">
<input type="hidden" class="types">
<input type="hidden" class="js">


  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
    <script>
    var token = localStorage.getItem("Usertoken");
    var store_name = localStorage.getItem("store_store_name");
    var agentName = localStorage.getItem("agentName");
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
      });

      $('.agent').html(store_name);
      // $('.layui-card-header').html(agentName);
      $('.layui-card-header').html(store_name);
        console.log(store_id)

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/user/pay_ways_all')}}"
            ,method: 'post'
            ,where:{
              token:token,   
              store_id:store_id           
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            // ,page: true
            ,cellMinWidth: 150
            ,cols: [[
              {field:'ways_desc', title: '通道名称'}
              ,{field:'rate', title: '结算费率',templet:'#rate'}
              ,{field:'settlement_type', title: '到账时间'}          
              ,{field:'status_desc', title: '是否开通'}
              ,{field:'status',width:230,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作',templet:'#passway'}
             
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

          if(layEvent === 'edit'){ //修改费率
            $('.way').html(e.ways_desc);
            $('.type').val(e.ways_type);
            $('.rate').val(e.rate);
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#edit_rate')
            });
          }else if(layEvent === 'shua'){
            $('.ways').html(e.ways_desc);
            $('.types').val(e.ways_type);
            $('.rate1').val(e.rate_e);
            $('.rate2').val(e.rate_f);
            $('.rate3').val(e.rate_f_top);
            var openshua=layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#edit_shua')
            });

          }else if(layEvent === 'newland'){
            $('.way').html(e.ways_desc);
            $('.type').val(e.ways_type);
            $.post("{{url('/api/user/open_ways_type')}}",
            {
                token:token,
                store_id:store_id,
                ways_type:e.ways_type,
                nl_mercId:''
            },function(res){
                console.log(res);
                console.log(res.data.nl_key);
                if(res.status==1){                  
                  $('.wxmerchantda').val(res.data.nl_mercId);
                  $('.wxmerchantdb').val(res.data.nl_key);
                  $('.wxmerchantdc').val(res.data.trmNo);
                  
                }
                  
                
            },"json");
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#newland')
            });

          }else if(layEvent === 'open'){
            $('.way').html(e.ways_desc);
            $('.type').val(e.ways_type)

            if(e.ways_type == 2000 ){
              
              $.post("{{url('/api/user/open_ways_type')}}",
              {
                  token:token,
                  store_id:store_id,
                  ways_type:e.ways_type,
                  wx_sub_merchant_id:''
              },function(res){
                  console.log(res);
                  if(res.status==1){                  
                    $('.wx_sub_merchant_id').val(res.data.wx_sub_merchant_id);                    
                  }                   
                  
              },"json");
              layer.open({
                type: 1,
                title: false,
                closeBtn: 0,
                area: '516px',
                skin: 'layui-layer-nobg', //没有背景色
                shadeClose: true,
                content: $('#open_passway')
              });
            }else{
              $.post("{{url('/api/user/open_ways_type')}}",
              {
                  token:token,
                  store_id:store_id,
                  ways_type:e.ways_type,
                  MerchantId:''
              },function(res){
                  console.log(res);
                  if(res.status==1){                  
                    $('.wx_sub_merchant_id').val(res.data.MerchantId);
                  }
                    
                  
              },"json");
              layer.open({
                type: 1,
                title: false,
                closeBtn: 0,
                area: '516px',
                skin: 'layui-layer-nobg', //没有背景色
                shadeClose: true,
                content: $('#open_passway')
              });
            }

 
          }else if(layEvent === 'hrt'){
            $('.way').html(e.ways_desc);
            $('.type').val(e.ways_type);
            
            $.post("{{url('/api/user/open_ways_type')}}",
            {
                token:token,
                store_id:store_id,
                ways_type:e.ways_type,
                h_mid:''
            },function(res){
                console.log(res);
                if(res.status==1){                  
                  $('.wx_sub_hrt').val(res.data.h_mid);                    
                }                   
                
            },"json");
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#open_passways')
            });

          }else if(layEvent === "ltf"){   
            $('.way').html(e.ways_desc);         
            $('.ltf_type').val(e.ways_type);            
            $.post("{{url('/api/user/open_ways_type')}}",
            {
                token:token,
                store_id:store_id,
                ways_type:e.ways_type,
                merchantCode:''
            },function(res){
                console.log(res);
                if(res.status==1){                  
                  $('.ltf1').val(res.data.merchantCode);                    
                  $('.ltf2').val(res.data.md_key);                    
                  $('.ltf3').val(res.data.appId);                    
                }                   
                
            },"json");
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#open_ltf')
            });

          }else if(layEvent === 'alipay'){
            $('.alipayway').html(e.ways_desc);
            $('.alipaytype').val(e.ways_type);
            
            $.post("{{url('/api/user/open_ways_type')}}",
            {
                token:token,
                store_id:store_id,
                ways_type:e.ways_type,
                alipay_store_id:''
            },function(res){
                console.log(res);
                if(res.status==1){                  
                  $('.wx_sub_alipay').val(res.data.alipay_store_id);     
                  $('.wx_sub_alipay_wai').val(res.data.out_store_id)
                }                   
                
            },"json");
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#alipay_store_id')
            });

          }else if(layEvent === 'pay'){
            $('.pay').attr('lay-href',"{{url('/user/openpassway?store_id=')}}"+store_id+"&ways_type="+e.ways_type);
          }else if(layEvent === 'alipaycode'){
            $.post("{{url('/api/user/alipay_auth')}}",
            {
                token:token,
                store_id:store_id,
            },function(res){
                console.log(res);
                $('#code').html('')
                $('#code').qrcode(res.data.qr_url);
                
            },"json");

            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#open_alipay')
            });
          }else if(layEvent === 'js'){
            $('.type').val(e.ways_type)
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#open_js')
            });
            $("#open").html('')
            // 结算类型查询
            $.ajax({
                url : "{{url('/api/basequery/settle_mode_type')}}",
                data : {token:token,ways_type:e.ways_type},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){

                            optionStr += "<option value='" + data.data[i].settle_mode_type + "' >"
                            + data.data[i].settle_mode_type_desc + "</option>";
                        }    
                        $("#open").append('<option value="">请选择结算方式</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
          }else if(layEvent === 'sao'){
            $('.agent').html()
            $('.ways').html(e.ways_desc);
            $('.types').val(e.ways_type);
            $('.rates1').val(e.rate_a);
            $('.rates2').val(e.rate_b);
            $('.rates3').val(e.rate_b_top);
            $('.rates4').val(e.rate_c);
            $('.rates5').val(e.rate_d);
            $('.rates6').val(e.rate_d_top);
            var openshua=layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#edit_sao')
            });

          }

          
        });
        form.on('select(open)', function(data){         
          category = data.value;  
          categoryName = data.elem[data.elem.selectedIndex].text; 
          $('.js').val(category);  
        });

        // 修改费率
        $('.submit').click(function(){
          $.post("{{url('/api/user/edit_store_rate')}}",
          {
              token:token,
              store_id:store_id,
              ways_type:$('.type').val(),
              rate:$('.rate').val()
          },function(res){
              console.log(res);
              if(res.status==1){
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 2000
                  },function(){
                    window.location.reload();
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
        $('.submits').click(function(){
          $.post("{{url('/api/user/edit_store_un_rate')}}",
          {
              token:token,
              store_id:store_id,
              ways_type:$('.types').val(),
              rate_e:$('.rate1').val(),
              rate_f:$('.rate2').val(),
              rate_f_top:$('.rate3').val(),
          },function(res){
              console.log(res);
              if(res.status==1){
                
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 1000
                  },function(){
                    window.location.reload();
                  });
              }else{
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 1000
                  });
              }
          },"json");
          
        });
        $('.submitsao').click(function(){
          $.post("{{url('/api/user/edit_store_unqr_rate')}}",
          {
              token:token,
              store_id:store_id,
              ways_type:$('.types').val(),
              rate_a:$('.rates1').val(),
              rate_b:$('.rates2').val(),
              rate_b_top:$('.rates3').val(),

              rate_c:$('.rates4').val(),
              rate_d:$('.rates5').val(),
              rate_d_top:$('.rates6').val(),
          },function(res){
              console.log(res);
              if(res.status==1){
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 1000
                  },function(){
                    window.location.reload();
                  });
              }else{
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 1000
                  });
              }
          },"json");
          
        })
        $('.save_newland').click(function(){
          $.post("{{url('/api/user/open_ways_type')}}",
          {
              token:token,
              store_id:store_id,
              ways_type:$('.type').val(),
              nl_mercId:$('.wxmerchantda').val(),
              nl_key:$('.wxmerchantdb').val(),
              trmNo:$('.wxmerchantdc').val()
          },function(res){
              console.log(res);
              if(res.status==1){
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 2000
                  }/*,function(){
                    window.location.reload();
                  }*/);
              }else{
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 3000
                  });
              }
          },"json");
        })
        // 开通通道
        $('.open_way').click(function(){
          if($('.type').val() == 2000){
            $.post("{{url('/api/user/open_ways_type')}}",
            {
                token:token,
                store_id:store_id,
                ways_type:$('.type').val(),
                wx_sub_merchant_id:$('.wx_sub_merchant_id').val()
            },function(res){
                console.log(res);
                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 1000
                    },function(){
                      window.location.reload();
                    });
                }else{
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                    });
                }
            },"json");
          }else{
            $.post("{{url('/api/user/open_ways_type')}}",
            {
                token:token,
                store_id:store_id,
                ways_type:$('.type').val(),
                MerchantId:$('.wx_sub_merchant_id').val()
            },function(res){
                console.log(res);
                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 1000
                    },function(){
                      window.location.reload();
                    });
                }else{
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                    });
                }
            },"json");
          }
          
        });
        $('.open_jiesuan').click(function(){
          //loading层
          var index = layer.load(1, {
            shade: [0.1,'#fff'] //0.1透明度的白色背景
          });

          $.post("{{url('/api/basequery/openways')}}",
          {
              token:token,
              store_id:store_id,
              ways_type:$('.type').val(),
              settle_mode_type:$('.js').val()
          },function(res){
              console.log(res);
              layer.close(index)
                
              if(res.status==1){
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 1000
                  },function(){
                    window.location.reload();
                  });
              }else{
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 3000
                  });
              }
          },"json");
        })
        $('.open_ways').click(function(){
          $.post("{{url('/api/user/open_ways_type')}}",
            {
                token:token,
                store_id:store_id,
                ways_type:$('.type').val(),
                h_mid:$('.wx_sub_hrt').val()
            },function(res){
                console.log(res);
                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 1000
                    },function(){
                      window.location.reload();
                    });
                }else{
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                    });
                }
            },"json");
        })
        $('.open_alipay_store_id').click(function(){
          $.post("{{url('/api/user/open_ways_type')}}",
          {
              token:token,
              store_id:store_id,
              ways_type:$('.alipaytype').val(),
              alipay_store_id:$('.wx_sub_alipay').val(),
              out_store_id:$('.wx_sub_alipay_wai').val()
          },function(res){
              console.log(res);
              if(res.status==1){
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 1000
                  },function(){
                    window.location.reload();
                  });
              }else{
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 3000
                  });
              }
          },"json");
        })

        $('.open_ltf_save').click(function(){
          $.post("{{url('/api/user/open_ways_type')}}",
          {
              token:token,
              store_id:store_id,
              ways_type:$('.ltf_type').val(),

              merchantCode:$('.ltf1').val(),
              md_key:$('.ltf2').val(),
              appId:$('.ltf3').val()
          },function(res){
              console.log(res);
              if(res.status==1){
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 1000
                  },function(){
                    window.location.reload();
                  });
              }else{
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 3000
                  });
              }
          },"json");
        })

       
        $('.sort').click(function(){
          $(this).attr('lay-href',"{{url('/user/passwaysort?')}}"+store_id);
        })
        

        
        

    });

  </script>

</body>
</html>





