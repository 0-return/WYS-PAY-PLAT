<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>实名认证</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
    /*.layui-card-header{width:80px;text-align: right;float:left;}*/
    /*.layui-card-body{margin-left:28px;}*/
    .layui-upload-img{width: 92px; height: 92px; margin: 0 10px 10px 0;}

    .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;/*width: 92px !important;*/font-size: 10px !important;text-align: center !important;}
    .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
    .layui-upload-list{width: 100px;height:96px;overflow: hidden;}
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
    
    </style>
</head>
<body>

<div class="layui-fluid" style="padding: 0">
    <div class="layui-card">
        <div class="layui-card-header">法人信息</div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item school">
                    <label class="layui-form-label">姓名</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入姓名" class="layui-input name">
                    </div>
                </div>
                <div class="layui-form-item school">
                    <label class="layui-form-label">身份证号码</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入身份证号码" class="layui-input idcard">
                    </div>
                </div>
                
                <div class="layui-form-item">
                    <div class="layui-card">
                        <!-- <div class="layui-card-header">学校logo</div> -->
                        <div class="layui-card-body" style="margin-left:28px;float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test1">上传身份证正面</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo1">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <div class="layui-card-body" style="margin-left:28px;float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test2">上传身份证反面</button>
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
        <div class="layui-card-header">门店信息</div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item school">
                    <label class="layui-form-label">门店名称</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入门店名称" class="layui-input storename">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">门店地址</label>
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
                    <label class="layui-form-label">详细地址</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" lay-verify="title" autocomplete="off" placeholder="请输入详细地址" class="layui-input address">

                    </div>

                </div>
                <div class="layui-form-item school">
                    <label class="layui-form-label">商户类型</label>
                    <div class="layui-input-block">
                        <select name="Merchanttype" id="Merchanttype" lay-filter="Merchanttype">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item school">
                    <label class="layui-form-label">经营品类</label>
                    <div class="layui-input-block">
                        <select name="category" id="category" lay-filter="category">
                            
                        </select>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-card">
                        <div class="layui-card-body" style="margin-left:28px;float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test3">门头照</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo3">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <div class="layui-card-body" style="margin-left:28px;float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test4">店内照</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo4">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">下一步</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" class="Merchanttypeid" value="">
<input type="hidden" class="Merchanttypename" value="">

<input type="hidden" class="categoryId" value="">
<input type="hidden" class="categoryname" value="">

<input type="hidden" class="provincecode" value="">
<input type="hidden" class="provincename" value="">
<input type="hidden" class="citycode" value="">
<input type="hidden" class="cityname" value="">
<input type="hidden" class="areacode" value="">
<input type="hidden" class="areaname" value="">





<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    // var token = localStorage.getItem("token");
    var token="{{$_GET['token']}}";
    sessionStorage.setItem('rz_token', token);

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects','element'], function(){
        var $ = layui.$ 
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form
            ,upload = layui.upload
            ,formSelects = layui.formSelects;

        var rz_name = sessionStorage.getItem("rz_name");
        var re_idcard = sessionStorage.getItem("re_idcard");
        var rz_zfz_z = sessionStorage.getItem("rz_zfz_z");
        var rz_zfz_f = sessionStorage.getItem("rz_zfz_f");
        var rz_storename = sessionStorage.getItem("rz_storename");
        var rz_p_code = sessionStorage.getItem("rz_p_code");
        var rz_c_code = sessionStorage.getItem("rz_c_code");
        var rz_a_code = sessionStorage.getItem("rz_a_code");
        var rz_p_name = sessionStorage.getItem("rz_p_name");
        var rz_c_name = sessionStorage.getItem("rz_c_name");
        var rz_a_name = sessionStorage.getItem("rz_a_name");
        var rz_address = sessionStorage.getItem("rz_address");
        var rz_address_name = sessionStorage.getItem("rz_address_name");
        var rz_Merchanttype = sessionStorage.getItem("rz_Merchanttype");
        var rz_category = sessionStorage.getItem("rz_category");
        var rz_doorpic = sessionStorage.getItem("rz_doorpic");
        var rz_storepic = sessionStorage.getItem("rz_storepic");


        // 法人信息
        $('.name').val(rz_name);
        $('.idcard').val(re_idcard);
        
        if(rz_zfz_z=='' ||  rz_zfz_z==null || rz_zfz_z=='undefined'){
          
        }else{
          $('#demo1').attr('src',rz_zfz_z);
        }
        if(rz_zfz_f=='' || rz_zfz_f==null || rz_zfz_f=='undefined'){

        }else{
          $('#demo2').attr('src',rz_zfz_f);
        }
        
        
        // 门店信息
        $('.storename').val(rz_storename);
        $('.provincecode').val(rz_p_code);
        $('.citycode').val(rz_c_code);
        $('.areacode').val(rz_a_code);
        $('.address').val(rz_address);
        $('.provincename').val(rz_p_name);
        $('.cityname').val(rz_c_name);
        $('.areaname').val(rz_a_name);
        $('.Merchanttypeid').val(rz_Merchanttype);
        $('.categoryId').val(rz_category);
        if(rz_doorpic=='' || rz_doorpic==null || rz_doorpic=='undefined'){

        }else{
          $('#demo3').attr('src',rz_doorpic);
        }
        if(rz_storepic=='' || rz_storepic==null || rz_storepic=='undefined'){

        }else{
          $('#demo4').attr('src',rz_storepic);
        }
        
        



        element.render();
        // 学校类型选择

        
        form.on('select(Merchanttype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.Merchanttypeid').val(category);
            $('.Merchanttypename').val(categoryName);           
        });
        form.on('select(category)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.categoryId').val(category);
            $('.categoryname').val(categoryName);           
        });

        getBoards();
        // 地区选择
        function getBoards(){
          
          // 市
            if(rz_p_code==''){

            }else{
              $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:rz_p_code},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "' "+((rz_c_code==data.data[i].area_code)?"selected":"")+">"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#city").append('<option value="">请选择市</option>'+optionStr);
                        layui.form.render('select');
                },
                  
              });
            }
            if(rz_c_code==''){

            }else{
              $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:rz_c_code},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "' "+((rz_a_code==data.data[i].area_code)?"selected":"")+">"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#area").append('<option value="">请选择县/区</option>'+optionStr);
                        layui.form.render('select');
                },
                  
              });
            }
            


            // 商户类型
            $.ajax({
                url : "{{url('/api/merchant/store_type')}}",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].store_type + "' "+((rz_Merchanttype==data.data[i].store_type)?"selected":"")+">"
                                + data.data[i].store_type_desc + "</option>";
                        }    
                        $("#Merchanttype").append('<option value="">选择商户类型</option>'+optionStr);
                        layui.form.render('select');
                }
                
            });
            // 经营品类
            $.ajax({
                url : "{{url('/api/merchant/store_category')}}",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].category_id + "' "+((rz_category==data.data[i].category_id)?"selected":"")+">"
                                + data.data[i].category_name + "</option>";
                        }    
                        $("#category").append('<option value="">选择经营品类</option>'+optionStr);
                        layui.form.render('select');
                }
                
            });

            // 查询认证信息
            $.ajax({
                url : "{{url('/api/merchant/store')}}",
                data : {token:token},
                type : 'post',
                success : function(res) {
                  console.log(res);
                  // 法人信息
                  $('.name').val(res.data.head_info.head_name);
                  $('.idcard').val(res.data.head_info.head_sfz_no);
                  
                  if(res.data.head_info.head_sfz_img_a=='' ||  res.data.head_info.head_sfz_img_a==null || res.data.head_info.head_sfz_img_a=='undefined'){
                    
                  }else{
                    $('#demo1').attr('src',res.data.head_info.head_sfz_img_a);
                  }
                  if(res.data.head_info.head_sfz_img_b=='' || res.data.head_info.head_sfz_img_b==null || res.data.head_info.head_sfz_img_b=='undefined'){

                  }else{
                    $('#demo2').attr('src',res.data.head_info.head_sfz_img_b);
                  }
                  // 法人姓名和门店信息
                  $('.storename').val(res.data.store_info.store_name);
                  $('.address').val(res.data.store_info.store_address);

                  if(res.data.store_info.store_logo_img=='' || res.data.store_info.store_logo_img==null || res.data.store_info.store_logo_img=='undefined'){

                  }else{
                    $('#demo3').attr('src',res.data.store_info.store_logo_img);
                  }
                  if(res.data.store_info.store_img_a=='' || res.data.store_info.store_img_a==null || res.data.store_info.store_img_a=='undefined'){

                  }else{
                    $('#demo4').attr('src',res.data.store_info.store_img_a);
                  } 

                  // 省
                  $.ajax({
                    url : "{{url('/api/basequery/city')}}",
                    data : {area_code:'1'},
                    type : 'get',
                    success : function(data) {
                        // console.log(data);
                        var optionStr = "";
                            for(var i=0;i<data.data.length;i++){
                                optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.store_info.province_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                            }    
                            $("#province").append('<option value="">请选择省</option>'+optionStr);
                            layui.form.render('select');
                    },
                    error : function(data) {
                        alert('查找板块报错');
                    }
                  }); 
                  $.ajax({
                    url : "{{url('/api/basequery/city')}}",
                    data : {area_code:res.data.store_info.province_code},
                    type : 'get',
                    success : function(data) {
                        console.log(data);
                        var optionStr = "";
                            for(var i=0;i<data.data.length;i++){
                                optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.store_info.city_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                            }    
                            $("#city").append('<option value="">请选择市</option>'+optionStr);
                            layui.form.render('select');
                    },
                    error : function(data) {
                        alert('查找板块报错');
                    }
                  });
                  $.ajax({
                    url : "{{url('/api/basequery/city')}}",
                    data : {area_code:res.data.store_info.city_code},
                    type : 'get',
                    success : function(data) {
                        console.log(data);
                        var optionStr = "";
                            for(var i=0;i<data.data.length;i++){
                                optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.store_info.area_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                            }    
                            $("#area").append('<option value="">请选择县/区</option>'+optionStr);
                            layui.form.render('select');
                    },
                    error : function(data) {
                        alert('查找板块报错');
                    }
                  });

                  // 商户类型
                  $.ajax({
                      url : "{{url('/api/merchant/store_type')}}",
                      data : {token:token},
                      type : 'post',
                      success : function(data) {
                          console.log(data);
                          var optionStr = "";
                              for(var i=0;i<data.data.length;i++){
                                  optionStr += "<option value='" + data.data[i].store_type + "' "+((res.data.store_info.store_type==data.data[i].store_type)?"selected":"")+">"
                                      + data.data[i].store_type_desc + "</option>";
                              }    
                              $("#Merchanttype").html('');
                              $("#Merchanttype").append('<option value="">选择商户类型</option>'+optionStr);
                              layui.form.render('select');
                      }
                      
                  });
                  // 经营品类
                  $.ajax({
                      url : "{{url('/api/merchant/store_category')}}",
                      data : {token:token},
                      type : 'post',
                      success : function(data) {
                          console.log(data);
                          var optionStr = "";
                              for(var i=0;i<data.data.length;i++){
                                  optionStr += "<option value='" + data.data[i].category_id + "' "+((res.data.store_info.category_id==data.data[i].category_id)?"selected":"")+">"
                                      + data.data[i].category_name + "</option>";
                              }    
                              $("#category").html('');
                              $("#category").append('<option value="">选择经营品类</option>'+optionStr);
                              layui.form.render('select');
                      }
                      
                  });

                }
                
            });
            
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
        });

        //身份证正面
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
        //身份证发面
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
        //门头照
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test3',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
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
                    layui.jquery('#demo3').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //店内照
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test4',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
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
                    layui.jquery('#demo4').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });



        $('.submit').on('click', function(){
          // 法人信息
          var name=$('.name').val();
          var idcard=$('.idcard').val();
          var zfz_z=$('#demo1').attr('src');
          var zfz_f=$('#demo2').attr('src');
          // 门店名称
          var storename=$('.storename').val();

          var p_code=$('.provincecode').val();
          var c_code=$('.citycode').val();
          var a_code=$('.areacode').val();
          var p_name=$('.provincename').val();
          var c_name=$('.cityname').val();
          var a_name=$('.areaname').val();

          var address=$('.address').val();

          var Merchanttypeid=$('.Merchanttypeid').val();
          var Merchanttypename=$('.Merchanttypename').val();
          var categoryId=$('.categoryId').val();
          var categoryname=$('.categoryname').val();
          var doorpic=$('#demo3').attr('src');
          var storepic=$('#demo4').attr('src');




          sessionStorage.setItem('rz_name', name);
          sessionStorage.setItem('re_idcard', idcard);
          sessionStorage.setItem('rz_zfz_z', zfz_z);
          sessionStorage.setItem('rz_zfz_f', zfz_f);
          sessionStorage.setItem('rz_storename', storename);
          sessionStorage.setItem('rz_p_code', p_code);
          sessionStorage.setItem('rz_c_code', c_code);
          sessionStorage.setItem('rz_a_code', a_code);
          sessionStorage.setItem('rz_p_name', p_name);
          sessionStorage.setItem('rz_c_name', c_name);
          sessionStorage.setItem('rz_a_name', a_name);
          sessionStorage.setItem('rz_address', address);
          sessionStorage.setItem('rz_Merchanttypeid', Merchanttypeid);
          sessionStorage.setItem('rz_Merchanttypename', Merchanttypename);
          sessionStorage.setItem('rz_categoryId', categoryId);
          sessionStorage.setItem('rz_categoryname', categoryname);
          sessionStorage.setItem('rz_doorpic', doorpic);
          sessionStorage.setItem('rz_storepic', storepic);




          window.location.href="{{url('/phone/identsecond')}}"; 

        });

    });
</script>

</body>
</html>
