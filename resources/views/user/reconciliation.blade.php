<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>对账统计</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    
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
    <!-- 筛选------------------------------------------------------------ -->
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">
        <div class="layui-card"> 
          <div class="layui-card-header">对账统计</div>

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
              <!-- 学校 -->
              <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                <div class="layui-form-item">                          
                  <div class="layui-input-block" style="margin-left:0">
                      <select name="schooltype" id="schooltype" lay-filter="schooltype" lay-search>
                          
                      </select>
                  </div>
                </div>
              </div>
              <!-- 支付状态 -->
              <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                <div class="layui-form-item">                          
                  <div class="layui-input-block" style="margin-left:0">
                      <select name="status" id="status" lay-filter="status">
                        <option value="">选择支付类型</option>
                        <option value="alipay">支付宝</option>
                        <option value="weixin">微信</option>
                        <option value="jd">京东</option>
                        <option value="unionpay">银联刷卡</option>
                        <option value="unionpayqr">银联扫码</option>
                        <option value="unionpaysf">银联闪付</option>
                      </select>
                  </div>
                </div>
              </div>
              <!-- 通道类型 -->
              <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                <div class="layui-form-item">                          
                  <div class="layui-input-block" style="margin-left:0">
                      <select name="passway" id="passway" lay-filter="passway">
                        <option value="">选择通道类型</option>
                        <option value="alipay">官方支付宝</option>
                        <option value="weixin">官方微信</option>
                        <option value="jdjr">京东金融</option>
                        <option value="newland">新大陆</option>
                        <option value="mybank">网商银行</option>
                        <option value="herongtong">和融通</option>
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
                  <div class="layui-inline">
                    <div class="layui-input-inline">
                      <input type="text" class="layui-input end-item test-item" placeholder="订单结束时间" lay-key="24">
                    </div>
                  </div>
                  
                </div>
              </div>  
              <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                <div class="layui-form-item">                          
                  <div class="layui-input-block" style="margin-left:0">
                     <button class="layui-btn" id="today">今日</button>
                     <button class="layui-btn" id="yesterday">昨日</button>
                  </div>
                </div>
              </div>                 
            </div>  
          </div>
        </div>
      </div>
    </div>
    
    
    
    <!-- 统计数据------------------------------------------------------------ -->
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">
        <div class="layui-card" style="background-color: transparent;"> 
          <div class="layui-card-header">统计数据(*商家实收=交易金额-退款金额，实际净额=商家实收-结算手续费)</div>          
        </div>
      </div>
    </div>
    <div class="layui-row layui-col-space15">
      <div class="layui-col-sm6 layui-col-md4">
        <div class="layui-card">
          <div class="layui-card-header">
            商家实收
            <span class="layui-badge layui-bg-blue layuiadmin-badge">元</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font acounts"></p>            
          </div>
        </div>
      </div>
      <div class="layui-col-sm6 layui-col-md4">
        <div class="layui-card">
          <div class="layui-card-header">
            实际净额
            <span class="layui-badge layui-bg-black layuiadmin-badge">元</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font acounts"></p>            
          </div>
        </div>
      </div>
      <div class="layui-col-sm6 layui-col-md4">
        <div class="layui-card">
          <div class="layui-card-header">
            交易金额
            <span class="layui-badge layui-bg-orange layuiadmin-badge">元</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font acounts"></p>            
          </div>
        </div>
      </div>
      <div class="layui-col-sm6 layui-col-md4">
        <div class="layui-card">
          <div class="layui-card-header">
            交易笔数
            <span class="layui-badge layui-bg-green layuiadmin-badge">笔</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font acounts"></p>            
          </div>
        </div>
      </div>
      <div class="layui-col-sm6 layui-col-md4">
        <div class="layui-card">
          <div class="layui-card-header">
            退款金额/笔数
            <span class="layui-badge layuiadmin-badge" style="background-color: #5FB878;color: #fff;">元/笔</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font acounts"></p>            
          </div>
        </div>
      </div>
      <div class="layui-col-sm6 layui-col-md4">
        <div class="layui-card">
          <div class="layui-card-header">
            手续费
            <span class="layui-badge layui-bg-red layuiadmin-badge">元</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font acounts"></p>            
          </div>
        </div>
      </div>      
    </div>
 
  </div>



  </div>
  <div id="main" style="width: 600px;height:400px;"></div>


  <input type="hidden" class="store_id">
  <input type="hidden" class="user_id">

  <input type="hidden" class="ways_source_id">
  <input type="hidden" class="company_id">

  <input type="hidden" class="starttime"><!-- 今天的开始时间 -->
  <input type="hidden" class="endtime"><!-- 今天的开始时间 -->

  <input type="hidden" class="starttimeY"><!-- 昨天的开始时间 -->
  <input type="hidden" class="endtimeY"><!-- 昨天的结束时间 -->

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script>   
<script>
    var token = localStorage.getItem("Usertoken");
    var agentName = localStorage.getItem("duizhang_agentName");
    var str=location.search;
    // var user_id=str.split('?')[1];
    // var user_name=str.split('?')[1];
    var user_id="{{$_GET['user_id']}}";
    var user_name="{{$_GET['user_name']}}";
    console.log(user_id)
    console.log(user_name)

    

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
      // $(document).ready(function(){        
      //     if(token==null){
      //         window.location.href="{{url('/mb/login')}}"; 
      //     }
      // })
    // 获取时间
    var nowdate = new Date();
    // 本月
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
    $('.endtime').val(nwedata)
    //今天的开始时间
    if(mounth.toString().length<2 && day.toString().length<2){
        var nwedatastart = year+'-0'+mounth+'-0'+day+' '+'00'+':'+'00'+':'+'00';
    }
    else if(mounth.toString().length<2){
        var nwedatastart = year+'-0'+mounth+'-'+day+' '+'00'+':'+'00'+':'+'00';
    }
    else if(day.toString().length<2){
        var nwedatastart = year+'-'+mounth+'-0'+day+' '+'00'+':'+'00'+':'+'00';
    }
    else{
        var nwedatastart = year+'-'+mounth+'-'+day+' '+'00'+':'+'00'+':'+'00';
    }
    $('.starttime').val(nwedatastart);
    // *******************************************************************************
    var years=nowdate.getFullYear();
    var mounths=nowdate.getMonth()+1;
    var days=nowdate.getDate()-1;
    //昨天的开始时间
    if(mounth.toString().length<2 && day.toString().length<2){
        var yesterdaystart = years+'-0'+mounths+'-0'+days+' '+'00'+':'+'00'+':'+'00';
    }
    else if(mounth.toString().length<2){
        var yesterdaystart = year+'-0'+mounths+'-'+days+' '+'00'+':'+'00'+':'+'00';
    }
    else if(day.toString().length<2){
        var yesterdaystart = years+'-'+mounths+'-0'+days+' '+'00'+':'+'00'+':'+'00';
    }
    else{
        var yesterdaystart = years+'-'+mounths+'-'+days+' '+'00'+':'+'00'+':'+'00';
    }

    if(mounth.toString().length<2 && day.toString().length<2){
        var yesterdayend = years+'-0'+mounths+'-0'+days+' '+'23'+':'+'59'+':'+'59';
    }
    else if(mounth.toString().length<2){
        var yesterdayend = years+'-0'+mounths+'-'+days+' '+'23'+':'+'59'+':'+'59';
    }
    else if(day.toString().length<2){
        var yesterdayend = years+'-'+mounths+'-0'+days+' '+'23'+':'+'59'+':'+'59';
    }
    else{
        var yesterdayend = years+'-'+mounths+'-'+days+' '+'23'+':'+'59'+':'+'59';
    }
    $('.starttimeY').val(yesterdaystart);
    $('.endtimeY').val(yesterdayend);


    // 华丽的分割线----------------------------------------------------
    // nowdate.setMonth(nowdate.getMonth()-1);
    // 上个月
    var y = nowdate.getFullYear();
    var mon = nowdate.getMonth()+1;
    var d = nowdate.getDate();
    var h = '00';
    var m = '00';
    var s = '00';
    if(mon.toString().length<2 && d.toString().length<2){
        var formatwdate = y+'-0'+mon+'-0'+d+' '+h+':'+m+':'+s;
    }
    else if(mon.toString().length<2){
        var formatwdate = y+'-0'+mon+'-'+d+' '+h+':'+m+':'+s;
    }
    else if(d.toString().length<2){
        var formatwdate = y+'-'+mon+'-0'+d+' '+h+':'+m+':'+s;
    }
    else{
        var formatwdate = y+'-'+mon+'-'+d+' '+h+':'+m+':'+s;
    }
    $('.start-item').val(formatwdate);

    if(user_id == ''){
      // $('.layui-card-header').html('对账统计');
    }else{
      $('.user_id').val(user_id)
      $('.transfer').val(user_name)
      $('.layui-card-header').html(agentName);
      acount()
    }
    function acount(){
      // 对账查询
      $.post("{{url('/api/user/order_count')}}",
      {
        token:token,
        store_id:$('.store_id').val(),
        user_id:$('.user_id').val(),

        time_start:$('.start-item').val(),
        time_end:$('.end-item').val(),

        ways_source:$('.ways_source_id').val(),
        company:$('.company_id').val(),
      },
      function(res){
        console.log(res); 
        $('.layui-col-space15 .layui-col-sm6').eq(0).find('.layui-card .acounts').html(res.data.get_amount);
        $('.layui-col-space15 .layui-col-sm6').eq(1).find('.layui-card .acounts').html(res.data.receipt_amount);
        $('.layui-col-space15 .layui-col-sm6').eq(2).find('.layui-card .acounts').html(res.data.total_amount);
        $('.layui-col-space15 .layui-col-sm6').eq(3).find('.layui-card .acounts').html(res.data.total_count);
        $('.layui-col-space15 .layui-col-sm6').eq(4).find('.layui-card .acounts').html(res.data.refund_amount+'/'+res.data.refund_count);
        $('.layui-col-space15 .layui-col-sm6').eq(5).find('.layui-card .acounts').html(res.data.fee_amount);        
      },"json");
     
    }



    // 选择门店
    $.ajax({
      url : "{{url('/api/user/store_lists')}}",
      data : {token:token,l:100},
      type : 'post',
      success : function(data) {
          console.log(data);
          var optionStr = "";
              for(var i=0;i<data.data.length;i++){
                  optionStr += "<option value='" + data.data[i].store_id + "'>" + data.data[i].store_name + "</option>";
              }    
              $("#schooltype").append('<option value="">选择门店</option>'+optionStr);
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
        $('.user_id').val($(this).attr('data'))
        $('.userbox').hide()

        table.reload('test-table-page', {
          where: { 
            user_id:$(this).attr('data')
          }
          ,page: {
            curr: 1
          }
        });

        acount();
        $.ajax({
          url : "{{url('/api/user/store_lists')}}",
          data : {token:token,user_id:$(this).attr('data'),l:100},
          type : 'post',
          success : function(data) {
              console.log(data);
              var optionStr = "";
                  for(var i=0;i<data.data.length;i++){
                      optionStr += "<option value='" + data.data[i].store_id + "'>" + data.data[i].store_name + "</option>";
                  }    
                  $("#schooltype").html('')
                  $("#schooltype").append('<option value="">选择门店</option>'+optionStr);
                  layui.form.render('select');
          },
          error : function(data) {
              alert('查找板块报错');
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
    //                 optionStr += "<option value='" + data.data[i].id + "' "+((user_id==data.data[i].id)?"selected":"")+">" + data.data[i].name + "</option>";
    //             }    
    //             $("#agent").append('<option value="">选择业务员</option>'+optionStr);
    //             layui.form.render('select');
    //     },
    //     error : function(data) {
    //         alert('查找板块报错');
    //     }
    // });
    // 选择业务员
    form.on('select(agent)', function(data){
      var user_id = data.value;
      $('.user_id').val(user_id);
      acount();
      if(user_id == ''){
        // 选择门店
        $.ajax({
            url : "{{url('/api/user/store_lists')}}",
            data : {token:token,l:100},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].store_id + "'>"
                          + data.data[i].store_name + "</option>";
                    }    
                    $("#schooltype").html('');
                    $("#schooltype").append('<option value="">选择门店</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
      }else{
        // 选择门店
        $.ajax({
            url : "{{url('/api/user/store_lists')}}",
            data : {token:token,user_id:user_id,l:100},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].store_id + "'>"
                          + data.data[i].store_name + "</option>";
                    }    
                    $("#schooltype").html('');
                    $("#schooltype").append('<option value="">选择门店</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });

      }

    });
    // 选择门店
    form.on('select(schooltype)', function(data){
      var store_id = data.value;
      $('.store_id').val(store_id);
      //执行重载
      acount()
    });

    // 选择支付类型
    form.on('select(status)', function(data){
      var store_id = data.value;
      $('.ways_source_id').val(store_id);
      //执行重载
      acount()
    });
    // 选择通道类型
    form.on('select(passway)', function(data){
      var store_id = data.value;
      $('.company_id').val(store_id);
      //执行重载
      acount()
    });

    
    


  
    

    laydate.render({
      elem: '.start-item'
      ,type: 'datetime'
      ,done: function(value){
        $('.start-item').val(value)
        //执行重载
        acount();
      }
    });

    laydate.render({
      elem: '.end-item'
      ,type: 'datetime'
      ,done: function(value){
        $('.end-item').val(value)
        //执行重载
        acount();
      }
    });

    $('#today').click(function(){
      $('.start-item').val($('.starttime').val())
      $('.end-item').val($('.endtime').val());
      acount();
    })

    $('#yesterday').click(function(){
      $('.start-item').val($('.starttimeY').val())
      $('.end-item').val($('.endtimeY').val())
      acount();
    })

    acount();
      

        
    });

  </script>

</body>
</html>