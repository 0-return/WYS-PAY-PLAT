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
    .bind{height:38px;line-height: 38px;text-align: right;}
    </style>
</head>
<body>

<div class="layui-fluid" style="padding: 0">
    <div class="layui-card">
        <div class="layui-card-header">证照信息</div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">注册号</label>
                    <div class="layui-input-block">
                      <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请与营业执照上保持一致" class="layui-input license_no">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">长期有效</label>
                    <div class="layui-input-block time">
                        <div class="layui-col-md12">
                          <input type="checkbox" name="zzz" lay-skin="switch" id="show" lay-text="" checked>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item valid" style="display: none">
                    <label class="layui-form-label">有效期</label>
                    <div class="layui-input-block">
                      <input type="text" class="layui-input start-item test-item" placeholder="请选择" lay-key="23">
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-card">
                        <div class="layui-card-body" style="margin-left:28px;float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test1">营业执照</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo1">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <div class="layui-card-body" style="margin-left:28px;float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test2">开户许可证</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo2">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <div class="layui-card-body" style="margin-left:28px;float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test3">其他证照</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo3">
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
    <div class="layui-card">
        <div class="layui-card-header">收款账户</div>
        <div class="layui-card-body">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">银行卡</label>
                    <div class="layui-input-block">
                      <a class="bindhref" href="{{url('/phone/bindbank')}}"><div class="bind"><span>请绑定</span><i class="layui-icon layui-icon-right"></i></div></a>
                    </div>
                </div>
                
                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">提交</button>
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

<input type="hidden" class="storeId" value="">



<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
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
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/mb/login')}}"; 
            }
        }) 
        // 证照信息
        var rz_zhuce = sessionStorage.getItem("rz_zhuce");
        var re_validtime = sessionStorage.getItem("re_validtime");
        var rz_yingye = sessionStorage.getItem("rz_yingye");
        var rz_kaihu = sessionStorage.getItem("rz_kaihu");
        var rz_other = sessionStorage.getItem("rz_other");
        
        $('.zhuce').val(rz_zhuce);
        $('.start-item').val(re_validtime);
        if(rz_yingye=='' ||  rz_yingye==null || rz_yingye=='undefined'){
          
        }else{
          $('#demo1').attr('src',rz_yingye);
        }
        if(rz_kaihu=='' || rz_kaihu==null || rz_kaihu=='undefined'){

        }else{
          $('#demo2').attr('src',rz_kaihu);
        }
        if(rz_other=='' || rz_other==null || rz_other=='undefined'){

        }else{
          $('#demo3').attr('src',rz_other);
        }
        // 法人信息
        var rz_name = sessionStorage.getItem("rz_name");
        var re_idcard = sessionStorage.getItem("re_idcard");
        var rz_zfz_z = sessionStorage.getItem("rz_zfz_z");
        var rz_zfz_f = sessionStorage.getItem("rz_zfz_f");
        // 门店信息
        var rz_storename = sessionStorage.getItem("rz_storename");
        var rz_p_code = sessionStorage.getItem("rz_p_code");
        var rz_c_code = sessionStorage.getItem("rz_c_code");
        var rz_a_code = sessionStorage.getItem("rz_a_code");
        var rz_address = sessionStorage.getItem("rz_address");
        var rz_Merchanttypeid = sessionStorage.getItem("rz_Merchanttypeid");
        var rz_Merchanttypename = sessionStorage.getItem("rz_Merchanttypename");
        var rz_categoryId = sessionStorage.getItem("rz_categoryId");
        var rz_categoryname = sessionStorage.getItem("rz_categoryname");
        var rz_doorpic = sessionStorage.getItem("rz_doorpic");
        var rz_storepic = sessionStorage.getItem("rz_storepic");

        // 学校类型选择
        $('.time').on("click",".layui-form-switch",function(){
            
            if($(this).hasClass('layui-form-onswitch')){
                $('.valid').hide();
            }else{
                $('.valid').show();
            }
        });

        
        
        laydate.render({
            elem: '.start-item'
            ,done: function(value){
              
            }
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
        //营业执照
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

        // 查询认证信息
        $.ajax({
            url : "{{url('/api/merchant/store')}}",
            data : {token:token},
            type : 'post',
            success : function(res) {
                console.log(res);
                $('.license_no').val(res.data.license_info.store_license_no);
                $('.start-item').val(res.data.license_info.store_license_time);
                $('#demo1').attr('src',res.data.license_info.store_industrylicense_img);
                $('#demo2').attr('src',res.data.license_info.store_license_img);
                $('#demo3').attr('src',res.data.license_info.store_other_img_a);
                if(res.data.license_info.store_license_time!=''){
                    $('.valid').show();
                    $('.time .layui-form-switch').removeClass('layui-form-onswitch');
                    
                }
                // 查询是否绑定银行卡
                if(res.data.account_info.store_bank_no!=''){
                    $('.bind span').html('已绑定');
                    // $('.bindhref').attr('href',"{{url('/phone/bankis')}}")
                }
            }
        });



        $('.submit').on('click', function(){
     
            // 证照信息
            var zhuce=$('.zhuce').val();
            var validtime=$('.start-item').val();
            var yingye=$('#demo1').attr('src');
            var kaihu=$('#demo2').attr('src');
            var other=$('#demo3').attr('src');

            sessionStorage.setItem('rz_zhuce', zhuce);
            sessionStorage.setItem('re_validtime', validtime);
            sessionStorage.setItem('rz_yingye', yingye);
            sessionStorage.setItem('rz_kaihu', kaihu);
            sessionStorage.setItem('rz_other', other);

            $.post("{{url('/api/merchant/add_store')}}",
            {
                token:token,
                // 法人信息
                head_name:rz_name,
                head_sfz_no:re_idcard,
                head_sfz_img_a:rz_zfz_z,
                head_sfz_img_b:rz_zfz_f,
                // 门店信息
                store_name:rz_storename,
                province_code:rz_p_code,
                city_code:rz_c_code,
                area_code:rz_a_code,
                store_address:rz_address,
                store_type:rz_Merchanttypeid,
                store_type_name:rz_Merchanttypename,
                category_id:rz_categoryId,
                category_name:rz_categoryname,
                store_logo_img:rz_doorpic,
                store_img_a:rz_storepic,
                // 证照信息
                store_license_no:$('.license_no').val(),
                store_license_time:$('.start-item').val(),
                store_license_img:$('#demo1').attr('src'),
                store_industrylicense_img:$('#demo2').attr('src'),
                store_other_img_a:$('#demo3').attr('src'),

            },function(data){
                console.log(data);
                if(data.status==1){
                    window.location.href="{{url('/phone/success')}}";                        
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
