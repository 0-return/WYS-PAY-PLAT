<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>银盈通</title>
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
                <div class="layui-card-header">银盈通</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">

                    <button class="layui-btn import" style="margin-bottom: 4px;height:36px;line-height: 36px;">创建代付</button>               

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
                  <script type="text/html" id="table-content-list" class="layui-btn-small"> 
                    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">设备删除</a>
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>

<div id="open_import" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <!-- <a href="{{url('/student-add.xlsx')}}">模板下载</a> -->
      <div class="layui-form-item" style="display: inline-block;">
        <form id= "uploadForm" style="display: inline-block;padding-left:50px;">  

          <div class="layui-form-item">
            <label class="layui-form-label">请上传excel</label>
            <div class="layui-input-block">
              <div class="layui-btn name" style="margin-right:20px;">选择所需上传文件</div>                  
              <input type="file" name="file" id="file" style="display: none;"/> 
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label">请输入支付密码</label>
            <div class="layui-input-block">
              <input type="password" placeholder="请输入支付密码" class="layui-input passward">
            </div>
          </div>
          <div class="layui-form-item">
            <label class="layui-form-label"></label>
            <div class="layui-input-block">
              <input type="button" value="确定上传" id="Upload" class="layui-btn"/>
            </div>
          </div>

        </form>
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
      

    // 渲染表格
    table.render({
      elem: '#test-table-page'
      ,url: "{{url('/api/dfpay/order_list')}}"
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
        {field:'order_number', title: '商家原始订单号'}
        ,{field:'in_order_id', title: '转入订单号'}
        ,{field:'merchant_number', title: '商户号'}
        ,{field:'amount', title: '代付金额/元'}
        ,{field:'customer_name',  title: '收款客户姓名'}                             
        ,{field:'account_number', title: '收款人银行卡'}
        ,{field:'pay_status', title: '状态',templet:'#statusTap'}
        ,{field:'created_at', title: '创建时间'}
        ,{field:'deal_time', title: '交易日期'}
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



    

  
   

    $('.import').click(function(){
      layer.open({
        type: 1,
        title: false,
        closeBtn: 0,
        area: '516px',
        skin: 'layui-layer-nobg', //没有背景色
        shadeClose: true,
        content: $('#open_import')
      });
    });

    // 导入商户
    $('.name').click(function(){
      console.log('00')
      $("#file").click();
    })
    // 获取文件名
    var file = $('#file');
    file.on('change', function( e ){
        //e.currentTarget.files 是一个数组，如果支持多个文件，则需要遍历
        var name = e.currentTarget.files[0].name;
        console.log( name );
        $('.name').html(name);
    });
    // excel文件导入
    $('#Upload').click(function(){
      var formData = new FormData($( "#uploadForm" )[0]);  
      console.log(formData);
      layer.confirm('是否确定上传该文件?',{icon: 7}, function(index){

        layer.msg('文件上传中', {
          icon: 16
          ,shade: 0.01
          ,time: 5000
        });
        
        $.ajax({   
          url: "{{url('/api/dfpay/info_import?token=')}}"+token+"&pay_password="+$('.passward').val(),  
          type: 'POST',  
          data: formData,  
          async: false,  
          cache: false,  
          contentType: false,  
          processData: false,
          dataType:"json",
          success: function (res) {  
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
              layer.alert(res.message, {icon: 2});
            }
           
          },  
          error: function (res) {  
            layer.msg(res.message, {
              offset: '15px'
              ,icon: 2
              ,time: 3000
            });
              
          }  
        });
      });

      

    });
    });

  </script>

</body>
</html>





