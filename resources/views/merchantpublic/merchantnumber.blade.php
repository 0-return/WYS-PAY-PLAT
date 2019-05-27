<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>商户号</title>
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
    .manage{background-color:#6c8ff5;}
    .water{background-color:#5fb878;}
    .branchshop{background-color: #11d0be}
    .storecode{background-color: #00963a;}
    #code{width: 200px;height: 200px;margin: 20px auto;}
    #code canvas{width: 100%;}
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
                <div class="layui-card-header">商户号列表</div>

                <div class="layui-card-body">
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.status == 1){ }}
                      <span class="cur">@{{ d.status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.status_desc }}
                    @{{#  } }}
                  </script>
                  <!-- 判断状态 -->
               
                  <!-- 入驻地址 -->
                  <!-- <a class="layui-btn layui-btn-normal layui-btn-xs open" lay-event="open">开启/关闭D0</a> -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small">                    
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs withdrawcash" lay-event="withdrawcash">提现</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs withdrawrecord" lay-event="withdrawrecord">提现记录</a>
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>


<div id="edit_tixian" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">

        <div class="layui-form" lay-filter="component-form-group" style="width:100%;display: inline-block;">
          <div class="layui-form-item">   
            <label class="layui-form-label" style="width:90px;text-align: left;">新大陆商户号:</label>                       
            <div class="layui-input-block" style="margin-left:0;display: inline-block;">
                <select name="payitem" id="payitem" lay-filter="payitem">
                    
                </select>
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label" style="width:90px;text-align: left;">新大陆商户号:</label>
            <div class="layui-input-block item1" style="text-align: left;line-height: 36px;"></div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label" style="width:90px;text-align: left;">余额:</label>
            <div class="layui-input-block item2" style="text-align: left;line-height: 36px;"></div>
          </div>
          <!-- <div class="layui-form-item">
            <label class="layui-form-label" style="width:90px;text-align: left;">到账银行卡:</label>
            <div class="layui-input-block item3" style="text-align: left;line-height: 36px;"></div>
          </div> -->
          <div class="layui-form-item">
            <label class="layui-form-label" style="width:90px;text-align: left;">提现手续费率:</label>
            <div class="layui-input-block item4" style="text-align: left;line-height: 36px;"></div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label" style="width:90px;text-align: left;">提现金额:</label>
            <div class="layui-input-block item5">
              <input type="tel" placeholder="请输入需要提现的金额" class="layui-input" id="jine" style="width:60%;float:left;">
              <div class="layui-form-mid layui-word-aux" style="display: inline-block;padding-left:10px !important;">预估手续费:<span class="sxf"></span></div>
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label" style="width:90px;text-align: left;">到账金额:</label>
            <div class="layui-input-block item6" style="text-align: left;line-height: 36px;"></div>
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
  </div>
</div>

<input type="hidden" class="js_rate">
<input type="hidden" class="js_storeid">
<input type="hidden" class="js_nl_mercId">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
    <script>
    var token = localStorage.getItem("Publictoken");
    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form','table'], function(){
      var $ = layui.$
          ,admin = layui.admin
          ,form = layui.form
          ,table = layui.table;

      // 未登录,跳转登录页面
      $(document).ready(function(){        
          if(token==null){
              window.location.href="{{url('/mb/login')}}"; 
          }
      })    
      
      form.on('select(payitem)', function(data){
        var nl_mercId = data.value;
        $.post("{{url('/api/newland/get_da_info')}}",
          {
              token:token,
              store_id:$('.js_storeid').val(),
              nl_mercId:nl_mercId
          },function(res){
              console.log(res);
              if(res.status==1){
                $('.item1').html(res.data[0].merc_id)
                $('.item2').html(res.data[0].balance)
                $('.item3').html(res.data[0].stl_oac+'/'+res.data[0].opn_bnk_desc)
                $('.item4').html(res.data[0].service_fee+'%')
                $('.js_rate').val(res.data[0].service_fee)
              }else{
                layer.msg(res.message, {
                    offset: '15px'
                    ,icon: 2
                    ,time: 2000
                });
              }
          },"json");        
      });   

      
      // 渲染表格
      table.render({
          elem: '#test-table-page'
          ,url: "{{url('/api/newland/store_list')}}"
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
            {field:'store_id', title: '平台ID'}
            ,{field:'store_name', title: '门店名称'}
            ,{field:'nl_mercId', title: '商户号'}
            ,{field:'nl_key',  title: '商户密钥'}
            ,{field:'trmNo', title: '终端号'}              
            ,{field:'settlement_type',  title: '结算类型'}
            ,{width:180,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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

        if(layEvent === 'open'){ //开启
          $.post("{{url('/api/newland/open_da')}}",
          {
              token:token,
              store_id:e.store_id,
              nl_mercId:e.nl_mercId
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
        
        }else if(layEvent === 'withdrawcash'){//提现
          $('.js_storeid').val(e.store_id)
          $('.js_nl_mercId').val(e.nl_mercId)
          layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: '516px',
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: $('#edit_tixian')
          });
          $.ajax({
            url : "{{url('/api/newland/store_list')}}",
            data : {token:token,l:100},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].nl_mercId + "' "+((e.nl_mercId==data.data[i].nl_mercId)?"selected":"")+">" + data.data[i].store_name + "</option>";
                    }    
                    $("#payitem").append('<option value="">选择新大陆商户号</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
          }); 
          $.post("{{url('/api/newland/get_da_info')}}",
          {
              token:token,
              store_id:e.store_id,
              nl_mercId:e.nl_mercId
          },function(res){
              console.log(res);
              if(res.status==1){
                $('.item1').html(res.data[0].merc_id)
                $('.item2').html(((res.data[0].balance)/100).toFixed(2))
                $('.item3').html(res.data[0].stl_oac+'/'+res.data[0].opn_bnk_desc)
                $('.item4').html(res.data[0].service_fee+'%')
                $('.js_rate').val(res.data[0].service_fee)

              }else{
                layer.msg(res.message, {
                    offset: '15px'
                    ,icon: 2
                    ,time: 2000
                });
              }
          },"json");
        }else if(layEvent === 'withdrawrecord'){//提现记录
          localStorage.setItem('store_store_name', e.store_name);
          $('.withdrawrecord').attr('lay-href',"{{url('/mb/withdrawrecord?store_id=')}}"+e.store_id+"&nl_mercId="+e.nl_mercId);
        }
      });


      $("#jine").bind("input propertychange",function(){        
        var money=$(this).val();
        var rate=$('.js_rate').val()
        var sum=(money*(rate/100)).toFixed(2)//手续费
        $('.sxf').html(sum)
        $('.item6').html((money-sum).toFixed(2))//到账金额
        console.log(sum)
        
      });

      $('.submit').click(function(){
        if($('#jine').val()>$('.item2').html() || $('#jine').val() == 0){
          layer.msg('提现金额不能大于余额或者等于零', {
              offset: '15px'
              ,icon: 2
              ,time: 3000
          });
        }else{
          var amt=$('#jine').val()*100
          $.post("{{url('/api/newland/get_da')}}",
          {
              token:token,
              store_id:$('.js_storeid').val(),
              nl_mercId:$('.js_nl_mercId').val(),
              txn_amt:amt
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
        
      })

      

    });

  </script>

</body>
</html>





