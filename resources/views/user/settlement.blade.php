<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>赏金结算</title>
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
                <div class="layui-card-header">赏金结算</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">

                    <!-- 缴费时间 -->
                    <div class="layui-form" style="display: inline-block;">                      
                      <div class="layui-form-item">              
                        <label class="layui-form-label">选择时间</label>           
                        <div class="layui-inline">
                          
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item" placeholder="开始时间" lay-key="23">
                          </div>
                        </div>
                        <div class="layui-inline">
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input end-item test-item" placeholder="结束时间" lay-key="24">
                          </div>
                        </div>
                        
                      </div>
                    </div>

                    <!-- 选择业务员 -->
                    <div class="layui-form" lay-filter="component-form-group" style="">
                      <div class="layui-form-item">                       
                        <label class="layui-form-label" style="float:left;">返佣来源</label>     
                        <div class="layui-input-block" style="margin-left:0;width:500px;float:left;">
                          <select name="schooltype" id="schooltype" lay-filter="schooltype" lay-search>
                              
                          </select>
                            
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="">
                      <div class="layui-form-item">                       
                        <label class="layui-form-label" style="float:left;">结算对象</label>     
                        <div class="layui-input-block" style="margin-left:0;width:500px;float:left;">
                          <select name="agent" id="agent" lay-filter="agent" lay-search>
                              <option value="">选择结算对象</option>
                              <option value="1">服务商</option>
                              <option value="2">商户</option>
                          </select>   

                        </div>
                      </div>
                    </div>

                    <!-- 选择商户时显示 -->
                    <div class="layui-form hidden" lay-filter="component-form-group" style="">
                      <div class="layui-form-item">                       
                        <label class="layui-form-label" style="float:left;">服务商</label>     
                        <div class="layui-input-block" style="margin-left:0;width:500px;float:left;">
                          <select name="daili" id="daili" lay-filter="daili" lay-search>
                              
                          </select>   

                        </div>
                      </div>
                    </div>
                    <div class="layui-form-item">
                      <label class="layui-form-label">税点</label>
                      <div class="layui-input-block"  style="margin-left:0;width:500px;float: left;line-height: 38px;">
                        <input type="text" placeholder="请输入税点" autocomplete="off" class="layui-input rate" style="width: 94%;float: left;margin-right: 10px;">%                        
                      </div>
                    </div>
                    <!-- 选择商户时显示 -->

                    <div class="layui-form-item">
                      <label class="layui-form-label">支付密码</label>
                      <div class="layui-input-block"  style="margin-left:0;width:500px;float:left;">
                        <input type="password" placeholder="请输入你的支付密码" autocomplete="off" class="layui-input pay_password">
                        <div class="layui-form-mid layui-word-aux">支付密码请在app或者小程序上设置</div>
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


  <input type="hidden" class="source_type">
  <input type="hidden" class="source_type_desc">
  <input type="hidden" class="user_id">
  <input type="hidden" class="daili_id">



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
          $('.hidden').hide()
        })
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
 
        
        // 选择业务员
        $.ajax({
            url : "{{url('/api/user/get_sub_users')}}",
            data : {token:token,self:1,sub_type:1,l:100},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].id + "'>" + data.data[i].name + "</option>";
                    }    
                    $("#daili").append('<option value="">选择业务员</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });

    

        
        // 选择赏金来源
        form.on('select(schooltype)', function(data){
          var index = data.elem.selectedIndex;           
          var source_type = data.value;
          $('.source_type').val(source_type);
          $('.source_type_desc').val(data.elem.options[index].text);
          
        });
        
        
        // 选择业务员
        form.on('select(agent)', function(data){
          var dx = data.value;
          $('.user_id').val(dx);
          
          if(dx == 1){
            $('.hidden').show()
          }else{
            $('.hidden').hide()
          }
        });
        form.on('select(daili)', function(data){
          var id = data.value;
          $('.daili_id').val(id);
          
        });
      
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
            // 获取时间
            var nowdate = new Date();
            
            var year=nowdate.getFullYear();
            var mounth=nowdate.getMonth()+1;
            var day=nowdate.getDate();
            var hour = nowdate.getHours();       
            var min = nowdate.getMinutes();     
            var sec = nowdate.getSeconds();
            if(mounth.toString().length<2 && day.toString().length<2){
                var nwedata = year+'-0'+mounth+'-0'+day+' '+hour+':'+min+':'+sec;
            }
            else if(mounth.toString().length<2){
                var nwedata = year+'-0'+mounth+'-'+day+' '+hour+':'+min+':'+sec;
            }
            else if(day.toString().length<2){
                var nwedata = year+'-'+mounth+'-0'+day+' '+hour+':'+min+':'+sec;
            }
            else{
                var nwedata = year+'-'+mounth+'-'+day+' '+hour+':'+min+':'+sec;
            }            

            $('.end-item').val(nwedata);//今天的时间
            var oDate1=new Date(nwedata);//当前时间
            var oDate2 = new Date(value);
            if(oDate2.getTime() > oDate1.getTime()){              
              layer.msg('结束时间不能大于当前时间', {
                offset: '15px'
                ,icon: 2
                ,time: 2000
              });
              
            }
          }
        });

        $('.submit').click(function(){
          if($('.user_id').val() == 1){
            $.post("{{url('/api/wallet/settlement')}}",
            {
              token:token,
              dx:$('.user_id').val(),
              source_type:$('.source_type').val(),
              source_type_desc:$('.source_type_desc').val(),
              time_start:$('.start-item').val(),
              time_end:$('.end-item').val(),
              pay_password:$('.pay_password').val(),

              user_id:$('.daili_id').val(),
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
                      ,time: 2000
                  });
                }
            },"json");
          }else{
            $.post("{{url('/api/wallet/settlement')}}",
            {
              token:token,
              dx:$('.user_id').val(),
              source_type:$('.source_type').val(),
              source_type_desc:$('.source_type_desc').val(),
              time_start:$('.start-item').val(),
              time_end:$('.end-item').val(),
              pay_password:$('.pay_password').val(),
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





