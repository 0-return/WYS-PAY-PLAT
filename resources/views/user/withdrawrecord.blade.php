<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>提现记录</title>
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
                <div class="layui-card-header">提现记录</div>

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
                    <!-- 缴费时间 -->
                    <div class="layui-form" style="display: inline-block;">                      
                      <div class="layui-form-item">                          
                        <div class="layui-inline">
                          
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item" placeholder="订单开始时间" lay-key="23">
                          </div>
                        </div>                                                
                      </div>
                    </div>              

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <script type="text/html" id="paymoney">
                    @{{ (d.txn_amt/100).toFixed(2) }}
                  </script>
                  <script type="text/html" id="daozhang">
                    @{{ (d.transfer_amt/100).toFixed(2) }}
                  </script>
                  <script type="text/html" id="shouxufei">
                    @{{ (d.corg_fee/100).toFixed(2) }}
                  </script>
                  <!-- 手续费承担方 -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small">                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="see">查看</a>
                    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="smallitem">缴费小项</a>
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

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
    <script>
    var token = localStorage.getItem("Usertoken");
    // var str=location.search;
    // var store_id=str.split('?')[1];
    var store_id="{{$_GET['store_id']}}";
    var nl_mercId="{{$_GET['nl_mercId']}}";

    
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
            url : "{{url('/api/newland/store_list')}}",
            data : {token:token,l:100,store_id:store_id},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        // optionStr += "<option value='" + data.data[i].nl_mercId + "'>" + data.data[i].store_name + "</option>";

                        optionStr += "<option value='" + data.data[i].nl_mercId + "' "+((nl_mercId==data.data[i].nl_mercId)?"selected":"")+">" + data.data[i].store_name + "</option>";
                    }    
                    $("#agent").append('<option value="">选择新大陆商户号</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
 
        
      

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/newland/da_out_select')}}"
            ,method: 'post'
            ,where:{
              token:token,
              store_id:store_id,
              nl_mercId:nl_mercId     
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
              {field:'merc_id', title: '商户号'}
              ,{field:'merc_nm', title: '商户名称'}
              ,{field:'stl_crp_no', title: '结算卡号'}
              ,{field:'opn_bnk_desc',  title: '银行名称'}                             
              ,{field:'ord_no', title: '提现单号'}
              ,{field:'ord_tm',  title: '提现时间'} 
              ,{field:'upt_tm',  title: '提现成功时间'}
              ,{field:'txn_amt',  title: '提现金额(元)',templet:'#paymoney'}
              ,{field:'transfer_amt',  title: '实际到账金额',templet:'#daozhang'}
              ,{field:'corg_fee',  title: '实际提现手续费',templet:'#shouxufei'}
              ,{field:'sts',  title: '处理状态'}
              // ,{width:150,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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

          if(layEvent === 'see'){ //审核
            layer.open({
              type: 2,
              title: '查看',
              shade: false,
              maxmin: true,
              area: ['70%', '80%'],
              content: "{{url('/user/seewater?')}}"+e.out_trade_no
            });
          }else if(layEvent === 'smallitem'){
            layer.open({
              type: 2,
              title: '缴费小项',
              shade: false,
              maxmin: true,
              area: ['60%', '70%'],
              content: "{{url('/user/payitem?')}}"+e.out_trade_no
            });
          }

          var data = obj.data;
          if(obj.event === 'setSign'){
            layer.open({
              type: 2,
              title: '模板详细',
              shade: false,
              maxmin: true,
              area: ['60%', '70%'],
              content: "{{url('/merchantpc/paydetail?')}}"+e.stu_order_type_no
            });
          }
        });

        // 选择门店
        form.on('select(agent)', function(data){
          var nl_mercId = data.value;
          //执行重载
          table.reload('test-table-page', {
            where: { 
              nl_mercId: nl_mercId, 
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
          elem: '.start-item'
          ,type: 'datetime'
          ,done: function(value){
            //执行重载
            table.reload('test-table-page', {
              where: { 
                ac_dt:value
              }
            });
          }
        });

       

    });

  </script>

</body>
</html>





