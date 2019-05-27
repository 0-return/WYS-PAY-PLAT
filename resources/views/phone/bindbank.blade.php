<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>绑定银行卡</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/template.css')}}" media="all">
    <style>
    /*.layui-card-header{width:80px;text-align: right;float:left;}*/
    /*.layui-card-body{margin-left:28px;}*/
    .layui-upload-img{width: 92px; height: 92px; margin: 0 10px 10px 0;}

    .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;/*width: 92px !important;*/font-size: 10px !important;text-align: center !important;}
    .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
    .layui-upload-list{width: 100px;height:96px;overflow: hidden;}
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
    .bind{height:38px;line-height: 38px;text-align: right;}
    /*.layui-icon-radio:before{content: none;}*/
    #account{height:38px;line-height: 38px;}
    #account .on_list{padding-right:20px;}
    .on_list{float: left;}
    .cur{color: #1ab394}


    .layadmin-maillist-fluid .layadmin-address{margin-bottom: 0;}
    .bank_number{display:block;margin-top:15px;}
    </style>
</head>
<body>
<div class="layui-fluid layadmin-maillist-fluid has_bank">
  <div class="layui-row layui-col-space15">
    <div class="layui-col-md4 layui-col-sm6">
      <div class="layadmin-contact-box" > 
        <!-- <div class="layui-col-md4 layui-col-sm6">
            <a href="javascript:;">
              <div class="layadmin-text-center">
                <img src="../../layuiadmin/style/res/template/character.jpg">
                <div class="layadmin-maillist-img layadmin-font-blod">演员</div>
              </div>
            </a>
        </div> -->
        
        <div class="layui-col-md8 layadmin-padding-left20 layui-col-sm6">
          
          <div class="layadmin-address">
            <a href="javascript:;">
              <span class="bank_name"></span>
              <br>
              <span>储值卡</span>
              <br>
              <span class="bank_number"></span>              
            </a>

          </div>
        </div>
      </div>
      <button class="layui-btn layui-btn-fluid change_bank" style="margin-top:30px;">更换</button>
    </div>
  </div>
</div>






<div class="layui-fluid bind_bank" style="padding: 0">
    <div class="layui-card">
        <!-- <div class="layui-card-header">证照信息</div> -->
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="component-form-group">
                
                <div class="layui-form-item acount_type">
                    <label class="layui-form-label">账户类型</label>
                    <div class="layui-input-block" id="account">
                        <div class="on_list" data-id="01">
                            <i class="layui-icon layui-icon-radio cur"></i>
                            <span>私人</span>
                        </div>
                        <div class="on_list" data-id="02">
                            <i class="layui-icon layui-icon-radio"></i>
                            <span>企业</span>
                        </div>
                    </div>
                </div>
                <!-- 私人 -->
                <div class="layui-form-item siren">
                    <label class="layui-form-label">持卡人</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入持卡人名称" class="layui-input name">
                    </div>
                </div>
                <!-- 企业 -->
                <div class="layui-form-item qiye" style="display: none">
                    <label class="layui-form-label">企业名称</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入企业名称" class="layui-input name">
                    </div>
                </div>

                
                <div class="layui-form-item">
                        <label class="layui-form-label">银行类型</label>
                        <div class="layui-input-block">
                            <select name="bank" id="bank" lay-filter="bank" lay-search>
                                
                            </select>
                        </div>
                    </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">银行卡号</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入银行卡号" class="layui-input bank_number">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">银行卡照片</label>
                    <div class="layui-card">
                        <div class="layui-card-body" style="float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test1">银行卡正面</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo1">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <div class="layui-card-body" style="float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test2">银行卡反面</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo2">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <div class="layui-card">
        <!-- <div class="layui-card-header">收款账户</div> -->
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">所属地区</label>
                    <div class="layui-input-block addressall">
                        <div class="layui-inline">
                            <select name="province" lay-filter="filterProvince" id="province">
                                
                            </select>
                        </div>
                        <div class="layui-inline">
                            <select name="city" lay-filter="filterCity" id="city">
                                
                            </select>
                        </div>
                        <div class="layui-inline">
                            <select name="area" lay-filter="filterArea" id="area">
                                
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">开户支行</label>
                    <div class="layui-input-block">
                        <select name="branch" id="branch" lay-filter="branch" lay-search>
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">手机号码</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入银行预留手机号" class="layui-input js-tel">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">验证码</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入验证码" class="layui-input js-code" style="width:55%;float:left;">
                      
                      <button class="layui-btn site-demo-active js-send" id="btnSendCode" style="float:right;width:106px;">获取验证码</button>
                    </div>
                </div>

                
                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">立即绑定</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
   
</div>




<input type="hidden" class="provincecode" value="">
<input type="hidden" class="provincename" value="">
<input type="hidden" class="citycode" value="">
<input type="hidden" class="cityname" value="">
<input type="hidden" class="areacode" value="">
<input type="hidden" class="areaname" value="">

<!-- 银行卡 -->
<input type="hidden" class="bankname" value="">
<!-- 支行 -->
<input type="hidden" class="branchid" value="">
<input type="hidden" class="branchname" value="">
<!-- 银行卡账户类型 -->
<input type="hidden" class="store_bank_type" value="01">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/jsencrypt.min.js')}}"></script>
<script>
    var token = localStorage.getItem("rz_token");
    

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects','laydate'], function(){
        var $ = layui.$ 
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form
            ,upload = layui.upload
            ,formSelects = layui.formSelects;

        $('.on_list').click(function(){
            $('.on_list i').removeClass('cur');
            $(this).find('i').addClass('cur');
            $('.store_bank_type').val($(this).attr('data-id'));
        })

        // 学校类型选择
        $('.acount_type').on("click",".layui-form-radio",function(){            
            $('.store_bank_type').val($(this).prev().attr('data-id'));
            var bank_type=$(this).prev().attr('data-id');
            if(bank_type=='01'){
                $('.siren').show();
                $('.qiye').hide();
            }else{
                $('.siren').hide();
                $('.qiye').show();
            }
        })
        
        getBoards();
        // 地区选择
        function getBoards(){
            
          
            
            // // 所属银行                
            $.post("{{url('/api/basequery/bank')}}",
            {
                token:token
            },function(data){
                console.log(data);
                if(data.status==1){
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].bankname + "'>" + data.data[i].bankname + "</option>";
                    }    
                    $("#bank").append('<option value="">请选择银行</option>'+optionStr);
                    layui.form.render('select');                        
                }

            },"json");
        }
        form.on('select(filterProvince)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.provincecode').val(category);
            $('.provincename').val(categoryName);
            $("#city").html('');
            $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:category},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "'>"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#city").append('<option value="">请选择市</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
        });
        form.on('select(filterCity)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.citycode').val(category);
            $('.cityname').val(categoryName);
            $("#area").html('');
            $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:category},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "'>"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#area").append('<option value="">请选择县/区</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
        });
        form.on('select(filterArea)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.areacode').val(category);
            $('.areaname').val(categoryName);  
            // 选择支行
            $.post("{{url('/api/basequery/sub_bank')}}",
            {
                token:token,
                bank_name:$('.bankname').val(),
                bank_province_name:$('.provincename').val(),
                bank_city_name:$('.cityname').val(),
                bank_area_name:$('.areaname').val()
            },function(data){
                console.log(data);
                if(data.status==1){
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].bank_no + "'>"+ data.data[i].sub_bank_name + "</option>";
                    }    
                    $("#branch").html('');
                    $("#branch").append('<option value="">选择所属支行</option>'+optionStr);
                    layui.form.render('select');                       
                }

            },"json");         
        });
        form.on('select(branch)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.branchid').val(category);
            $('.branchname').val(categoryName);           
        });
        // 选择银行卡
        form.on('select(bank)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.bankname').val(category);   
                   
        });

        


        //营业执照
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test1',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo1').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //营业执照
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test2',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo2').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });

        //发送验证码
        var InterValObj; //timer变量，控制时间
        var count = 60; //间隔函数，1秒执行
        var curCount;//当前剩余秒数
        $('#btnSendCode').click(function(){
          var encrypt = new JSEncrypt();
          // phone=$('.js-tel').val()&info="2"&type=type
          var phone=$('.js-tel').val();
          encrypt.setPublicKey("MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4COVutRbOUfQNjvVOzwK49NzHIPRwwksnJ6QtdHwGmdUZiT2HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS0lfeBMqUcm+bY7aZ54wClm75RI90uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Ycr8S8O004ExtvQqu2QCl7Aai/WC4URIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx37Y9AMR0V94R13v4BFlMQDG+2REVJsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzLPYV0jsiit2AUkyURRLxEaZm2C0YYhfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DXkQIDAQAB");
          var data = encrypt.encrypt('phone='+phone+'&info='+"1"+'&type='+'03');

          curCount = count;
          //设置button效果，开始计时
          $("#btnSendCode").attr("disabled", "true");
          $("#btnSendCode").val(curCount+'(s)');
          InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
          //向后台发送处理数据
          $.post("{{url('/api/Sms/send')}}",
              {
                  sign:data 
              },
              function (res){
                  console.log(res);
                  if(res.status==1){
                      layer.msg(res.message);
                      $("#btnSendCode").attr("disabled", "true");
                      $("#btnSendCode").html(curCount+'(s)');
                      window.clearInterval(InterValObj);//停止计时器
                      InterValObj = window.setInterval(SetRemainTime, 1000); //启动计时器，1秒执行一次
                  }else{
                      
                      window.clearInterval(InterValObj);//停止计时器
                      $("#btnSendCode").removeAttr("disabled");//启用按钮                    
                      alert(res.message);
                      $("#btnSendCode").html("获取验证码");
                  }
              },'json');
        })

        //timer处理函数
        function SetRemainTime() {
            if (curCount == 0) {
                window.clearInterval(InterValObj);//停止计时器
                $("#btnSendCode").removeAttr("disabled");//启用按钮
                $("#btnSendCode").html("获取验证码");
            }
            else {
                curCount--;
                $("#btnSendCode").html(curCount+'(s)');
            }
        }




        // 查询认证信息++++++++++++++++++++++++++++++++++++++++++++++
        $.ajax({
            url : "{{url('/api/merchant/store')}}",
            data : {token:token},
            type : 'post',
            success : function(res) {
                console.log(res);
                // 查询银行卡信息
                if(res.data.account_info.store_bank_no==''){
                    $('.bind_bank').show();
                    $('.has_bank').hide();
                }else{
                    $('.bind_bank').hide();
                    $('.has_bank').show();
                    $('.bank_name').html(res.data.account_info.bank_name);
                    var data=res.data.account_info.store_bank_no;

                    $('.bank_number').html(data.replace(/\s/g,'').replace(/(\d{4})\d+(\d{4})$/, "**** **** **** $2"));
                }

                if(res.data.account_info.store_bank_type=='01'){
                    $('.siren').show();
                    $('.qiye').hide();
                }else{
                    $('.siren').hide();
                    $('.qiye').show();
                }

                $('.on_list i').removeClass('cur');
                $('.on_list').each(function(){
                    if(res.data.account_info.store_bank_type==$(this).attr('data-id')){
                        $(this).find('i').addClass('cur');
                    }
                })

                
                $('.store_bank_type').val(res.data.account_info.store_bank_type);
                $('.name').val(res.data.account_info.store_bank_name);
                $('.bank_number').val(res.data.account_info.store_bank_no);
                $('.js-tel').val(res.data.account_info.store_bank_phone);
                $('#demo1').attr('src',res.data.account_info.bank_img_a);
                $('#demo2').attr('src',res.data.account_info.bank_img_b);
                // 所属银行                
                $.post("{{url('/api/basequery/bank')}}",
                {
                    token:token
                },function(data){
                    console.log(data);
                    if(data.status==1){
                        var optionStr = "";
                        for(var i=0;i<data.data.length;i++){

                            optionStr += "<option value='" + data.data[i].bankname + "' "+((res.data.account_info.bank_name==data.data[i].bankname)?"selected":"")+">" + data.data[i].bankname + "</option>";
                        }    
                        $("#bank").html('');
                        $("#bank").append('<option value="">请选择银行</option>'+optionStr);
                        layui.form.render('select');                        
                    }

                },"json");
                // 省
                $.ajax({
                    url : "{{url('/api/basequery/city')}}",
                    data : {area_code:'1'},
                    type : 'get',
                    success : function(data) {
                        // console.log(data);
                        var optionStr = "";
                            for(var i=0;i<data.data.length;i++){
                                optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.account_info.bank_province_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                            }    
                            $("#province").append('<option value="">请选择省</option>'+optionStr);
                            layui.form.render('select');
                    },
                    error : function(data) {
                        alert('查找板块报错');
                    }
                });
                // 市
                $.ajax({
                    url : "{{url('/api/basequery/city')}}",
                    data : {area_code:res.data.account_info.bank_province_code},
                    type : 'get',
                    success : function(data) {
                        console.log(data);
                        var optionStr = "";
                            for(var i=0;i<data.data.length;i++){
                                optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.account_info.bank_city_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                            }    
                            $("#city").append('<option value="">请选择市</option>'+optionStr);
                            layui.form.render('select');
                    },
                    error : function(data) {
                        alert('查找板块报错');
                    }
                });
                // 区
                $.ajax({
                    url : "{{url('/api/basequery/city')}}",
                    data : {area_code:res.data.account_info.bank_city_code},
                    type : 'get',
                    success : function(data) {
                        console.log(data);
                        var optionStr = "";
                            for(var i=0;i<data.data.length;i++){
                                optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.account_info.bank_area_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                            }    
                            $("#area").append('<option value="">请选择县/区</option>'+optionStr);
                            layui.form.render('select');
                    },
                    error : function(data) {
                        alert('查找板块报错');
                    }
                });
                // 选择支行
                $.post("{{url('/api/basequery/sub_bank')}}",
                {
                    token:token,
                    bank_name:res.data.account_info.bank_name,
                    bank_province_name:res.data.account_info.bank_province_name,
                    bank_city_name:res.data.account_info.bank_city_name,
                    bank_area_name:res.data.account_info.bank_area_name
                },function(data){
                    console.log(data);
                    if(data.status==1){
                        var optionStr = "";
                        for(var i=0;i<data.data.length;i++){

                            optionStr += "<option value='" + data.data[i].bank_no + "' "+((res.data.account_info.bank_no==data.data[i].bank_no)?"selected":"")+">"+ data.data[i].sub_bank_name + "</option>";
                        }    
                        $("#branch").html('');
                        $("#branch").append('<option value="">选择所属支行</option>'+optionStr);
                        layui.form.render('select');                       
                    }

                },"json"); 
                
            }
        });

        $('.change_bank').click(function(){
            $('.bind_bank').show();
            $('.has_bank').hide();
        })

        $('.submit').on('click', function(){
     
            
            var bank_type=$('.store_bank_type').val();
            var name=$('.name').val();
            var bankname=$('.bankname').val();
            var bank_number=$('.bank_number').val();
            var bank_z=$('#demo1').attr('src');
            var bank_f=$('#demo2').attr('src');
            var p_code=$('.provincecode').val();
            var c_code=$('.citycode').val();
            var a_code=$('.areacode').val();
            var p_name=$('.provincename').val();
            var c_name=$('.cityname').val();
            var a_name=$('.areaname').val();
            var branchid=$('.branchid').val();
            var branchname=$('.branchname').val();
            var phone=$('.js-tel').val();
            var code=$('.js-code').val();

            sessionStorage.setItem('bank_bank_type', bank_type);
            sessionStorage.setItem('bank_name', name);
            sessionStorage.setItem('bank_bankname', bankname);
            sessionStorage.setItem('bank_bank_number', bank_number);
            sessionStorage.setItem('bank_bank_z', bank_z);
            sessionStorage.setItem('bank_bank_f', bank_f);
            sessionStorage.setItem('bank_p_code', p_code);
            sessionStorage.setItem('bank_c_code', c_code);
            sessionStorage.setItem('bank_a_code', a_code);
            sessionStorage.setItem('bank_branchid', branchid);
            sessionStorage.setItem('bank_bank_type', bank_type);
            sessionStorage.setItem('bank_phone', phone);
            sessionStorage.setItem('bank_code', code);

                          
            $.post("{{url('/api/merchant/add_store')}}",
            {
                token:token,
                store_bank_no:bank_number,
                store_bank_name:name,
                store_bank_phone:phone,
                store_bank_type:bank_type,
                bank_name:bankname,
                bank_no:branchid,
                sub_bank_name:branchname,
                bank_province_code:p_code,
                bank_city_code:c_code,
                bank_area_code:a_code,
                bank_img_a:bank_z,
                bank_img_b:bank_f,
                bank_msn_code:code

            },function(data){
                console.log(data);
                if(data.status==1){
                    window.location.href="{{url('/phone/identsecond')}}";                        
                }else{
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                    });
                }

            },"json");

            

             
        }); 

    });
</script>

</body>
</html>
