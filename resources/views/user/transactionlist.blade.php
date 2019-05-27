<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>交易榜</title>
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
                <div class="layui-card-header">交易榜</div>
                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;"> 
                                      
                    <!-- 缴费时间 -->
                    <div class="layui-form" style="width:700px;display: inline-block;">                      
                      <div class="layui-form-item" style="width:300px;display: inline-block;">                          
                        <div class="layui-inline">                          
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item" placeholder="订单开始时间" lay-key="23">
                          </div>
                        </div> 
                      </div>
                      <div class="layui-form-item" style="width:300px;display: inline-block;">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="agent" id="agent" lay-filter="agent" lay-search>
                              <option value="">排序</option>
                              <option value="1" selected="selected">交易笔数由大到小</option>
                              <option value="2">交易笔数由小到大</option>
                              <option value="3">交易金额由大到小</option>
                              <option value="4">交易金额由小到大</option>
                            </select>
                        </div>
                      </div>
                    </div>
                   
                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.pay_status == 1){ }}
                      <span class="cur">@{{ d.pay_status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.pay_status_desc }}
                    @{{#  } }}
                  </script>
                  <!-- 判断状态 -->
                  
                  <script type="text/html" id="paymoney">
                    @{{ d.rate }}%
                  </script>
                  <script type="text/html" id="table-content-list" class="layui-btn-small">                    
                    <!-- <a class="layui-btn layui-btn-normal layui-btn-xs tongbu" lay-event="tongbu">查看</a> -->
                  </script>
                </div>
              </div>
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

        $('.user_id').val(store_id)
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })

        var myDate = new Date();
        var year=myDate.getFullYear()
        var month = ("0" + (myDate.getMonth() + 1)).slice(-2);
        var months = year+''+month
        $('.start-item').val(year+'-'+month)
        

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/user/ranking')}}"
            ,method: 'post'
            ,where:{
              token:token,
              month:months,     
              type:'1'  
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
              {field:'store_name', title: '门店'}
              ,{field:'total_count', title: '总交易笔数'}
              ,{field:'total_amount', title: '总交易金额'}
              ,{field:'alipay_count',  title: '支付宝笔数'}                             
              ,{field:'alipay_amount',  title: '支付宝交易金额'}                             
              ,{field:'weixin_count', title: '微信交易笔数'}
              ,{field:'weixin_amount',  title: '微信交易金额'}                        
              // ,{width:100,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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

            $.post("{{url('/api/basequery/update_order')}}",
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
              content: "{{url('/merchantpc/paydetail?')}}"+e.stu_order_type_no
            });
          }
        });

        // 选择业务员
        form.on('select(agent)', function(data){
          var type = data.value;
          
          //执行重载
          table.reload('test-table-page', {
            where: { 
              type: type
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
         
        });


        laydate.render({
          elem: '.start-item'
          ,type: 'month'
          ,done: function(value){
            
            console.log(value)
            var a=value.replace(/-/g,"")
            console.log(a)
            //执行重载
            table.reload('test-table-page', {
              where: {
                month:a,
              }
              ,page: {
                curr: 1 //重新从第 1 页开始
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
                curr: 1 //重新从第 1 页开始
              }
            });
          }
        });

        
       
        // $('.export').click(function(){
        //   var store_id=$('.store_id').val();
        //   var user_id=$('.user_id').val();
        //   var sort=$('.sort').val();          
        //   var pay_status=$('.pay_status').val();
        //   var ways_source=$('.pay_type').val();
        //   var company=$('.company_id').val();

        //   var time_start=$('.start-item').val();
        //   var time_end=$('.end-item').val();
          
        //   var out_trade_no=$('.danhao').val();
        //   var trade_no=$('.tiaoma').val();

        //   window.location.href="{{url('/api/export/UserOrderExcelDown')}}"+"?token="+token+"&store_id="+store_id+"&user_id="+user_id+"&sort="+sort+"&pay_status="+pay_status+"&ways_source="+ways_source+"&company="+company+"&time_start="+time_start+"&time_end="+time_end+"&out_trade_no="+out_trade_no+"&trade_no="+trade_no;     
        // })

    });

  </script>

</body>
</html>





