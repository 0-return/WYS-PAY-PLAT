<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>成本费率管理</title>
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
    .agent{height:38px;line-height: 38px;}
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
                    @{{#  if(d.store_all_rate == ''){ }}
                      未设置
                    @{{#  } else { }}
                      @{{ d.store_all_rate }}%
                    @{{#  } }}
                  </script>
                  <!-- 判断状态 -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small">                    
                    
                    @{{#  if(d.ways_type == 8005 || d.ways_type == 6005){ }}
                      <a class="layui-btn layui-btn-danger layui-btn-xs shua" lay-event="shua">修改商户默认费率</a>
                    @{{#  } else if(d.ways_type == 8004 || d.ways_type == 6004){ }}
                      <a class="layui-btn layui-btn-danger layui-btn-xs sao" lay-event="sao">修改商户默认费率</a>
                    @{{#  } else { }}
                      <a class="layui-btn layui-btn-danger layui-btn-xs edit" lay-event="edit">修改商户默认费率</a>
                    @{{#  } }}
                  </script>
                </div>
              </div>
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
        <label class="layui-form-label">代理商:</label>
        <div class="layui-input-block">
            <div class="agent"></div>
        </div>
      </div>
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
<input type="hidden" class="type">
</div>



<div id="edit_shua" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">代理商:</label>
        <div class="layui-input-block">
            <div class="agent"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">通道:</label>
        <div class="layui-input-block">
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

<div id="edit_sao" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <label class="layui-form-label">代理商:</label>
        <div class="layui-input-block">
            <div class="agent"></div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">通道:</label>
        <div class="layui-input-block">
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

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
    <script>
    var token = localStorage.getItem("Usertoken");
    var agentName = localStorage.getItem("agentName");
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
      });
      $('.agent').html(agentName);
      $('.layui-card-header').html(agentName);
        

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/user/user_ways_default')}}"
            ,method: 'post'
            ,where:{
              token:token,   
              user_id:user_id           
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            // ,page: true
            ,cellMinWidth: 150
            ,cols: [[
              {field:'ways_desc', title: '通道名称'}
              ,{field:'store_all_rate', title: '默认费率',templet:'#rate'}
              ,{width:150,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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

          if(layEvent === 'edit'){ //审核
            $('.way').html(e.ways_desc);
            $('.type').val(e.ways_type);
            $('.rate').val(e.store_all_rate);
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
            $('.rate1').val(e.store_all_rate_e);
            $('.rate2').val(e.store_all_rate_f);
            $('.rate3').val(e.store_all_rate_f_top);

            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#edit_shua')
            });
          }else if(layEvent === 'sao'){
            $('.ways').html(e.ways_desc);
            $('.types').val(e.ways_type);
            $('.rates1').val(e.store_all_rate_a);
            $('.rates2').val(e.store_all_rate_b);
            $('.rates3').val(e.store_all_rate_b_top);
            $('.rates4').val(e.store_all_rate_c);
            $('.rates5').val(e.store_all_rate_d);
            $('.rates6').val(e.store_all_rate_d_top);

            layer.open({
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

        $('.submit').click(function(){
          $.post("{{url('/api/user/edit_user_store_all_rate')}}",
          {
              token:token,
              user_id:user_id,
              ways_type:$('.type').val(),
              store_all_rate:$('.rate').val()
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
        $('.submits').click(function(){
          $.post("{{url('/api/user/edit_user_un_store_all_rate')}}",
          {
              token:token,
              user_id:user_id,
              ways_type:$('.types').val(),
              store_all_rate_e:$('.rate1').val(),
              store_all_rate_f:$('.rate2').val(),
              store_all_rate_f_top:$('.rate3').val(),
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

         $('.submitsao').click(function(){
          $.post("{{url('/api/user/edit_user_unqr_store_all_rate')}}",
          {
              token:token,
              user_id:user_id,
              ways_type:$('.types').val(),
              store_all_rate_a:$('.rates1').val(),
              store_all_rate_b:$('.rates2').val(),
              store_all_rate_b_top:$('.rates3').val(),

              store_all_rate_c:$('.rates4').val(),
              store_all_rate_d:$('.rates5').val(),
              store_all_rate_d_top:$('.rates6').val(),
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

    });

  </script>

</body>
</html>





