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
                <div class="layui-card-header">提现记录</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <!-- 选择业务员 -->
                    <!-- <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="agent" id="agent" lay-filter="agent" lay-search>
                                
                            </select>
                        </div>
                      </div>
                    </div> -->

                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                          <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入业务员名称" class="layui-input transfer">

                          <div class="userbox" style='display: none'></div>
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
                          <!-- <button class="layui-btn" style="margin-bottom: 4px;height:36px;line-height: 36px;">导出</button> -->
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

        var user_name=localStorage.getItem('agentName');

        if(user_id == undefined){

        }else{
          $('.transfer').val(user_name)
        }
        // 选择门店
        $.ajax({
            url : "{{url('/api/wallet/source_type')}}",
            data : {token:token,l:100},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].source_type + "' > " + data.data[i].source_desc + "</option>";
                    }    
                    $("#schooltype").append('<option value="">选择赏金来源</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
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
          
        })
        
        // 选择业务员
        // $.ajax({
        //     url : "{{url('/api/user/get_sub_users')}}",
        //     data : {token:token,l:100},
        //     type : 'post',
        //     success : function(data) {
        //         console.log(data);
        //         var optionStr = "";
        //             for(var i=0;i<data.data.length;i++){
        //                 optionStr += "<option value='" + data.data[i].id + "' "+((user_id==data.data[i].id)?"selected":"")+">"
        //                     + data.data[i].name + "</option>";
        //             }    
        //             $("#agent").append('<option value="">选择业务员</option>'+optionStr);
        //             layui.form.render('select');
        //     },
        //     error : function(data) {
        //         alert('查找板块报错');
        //     }
        // });

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/wallet/out_wallet_list')}}"
            ,method: 'post'
            ,where:{
              token:token,
              user_id:user_id,
              return_type:'2'       
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
              {field:'out_trade_no', title: '订单号'}
              ,{field:'amount', title: '提现金额'}
              ,{field:'account_amount', title: '账户余额'}
              ,{field:'status_desc',  title: '提现状态'}     
              ,{field:'created_at',  title: '创建时间'} 
              ,{field:'settlement_time',  title: '结算时间'}
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

        
        // 选择赏金来源
        form.on('select(schooltype)', function(data){
          var source_type = data.value;
          $('.store_id').val(source_type);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              source_type: source_type                            
              
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });
        
        
        // 选择业务员
        form.on('select(agent)', function(data){
          var user_id = data.value;
          $('.user_id').val(user_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              user_id: $(".user_id").val(),                            
              
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });
        // 选择赏金状态
        form.on('select(status)', function(data){
          var pay_status = data.value;
          $('.pay_status').val(pay_status);
          //执行重载
          table.reload('test-table-page', {
            where: {               
              settlement:pay_status,
            }
          });
        });
        // 选择支付类型
        form.on('select(type)', function(data){
          var pay_type = data.value;
          $('.pay_type').val(pay_type);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              ways_source:pay_type
            }
            ,page: {
              curr: 1 //重新从第 1 页开始
            }
          });
        });
    // 时间++++++++++++++++++++++++++++++++++++++++++++++++
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

        
    });

  </script>

</body>
</html>





