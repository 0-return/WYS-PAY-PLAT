<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>新增合并</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
        .img{width:130px;height:90px;overflow: hidden;}
        .img img{width:100%;height:100%;}
        .layui-layer-nobg{width: none !important;}
        /*.layui-layer-content{width:600px;height:550px;}*/
        .layui-card-header{width:80px;text-align: right;float:left;}
        .layui-card-body{margin-left:28px;}
        .layui-upload-img{width: 100px; height: 92px; /*margin: 0 10px 10px 0;*/}

        .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;width: auto !important;font-size: 10px !important;text-align: center !important;}
        .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
        .layui-upload-list{width: 100px;height:96px;overflow: hidden;margin: 10px auto;}
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
        

    </style>
   
</head>
<body>

<div class="layui-fluid">
    
    
    <div class="layui-card">
        <div class="layui-card-header">新增合并</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-row layui-form" lay-filter="component-form-group">  
                <div class="layui-col-md12">              
                    <div class="layui-form-item">
                        <label class="layui-form-label">名称</label>
                        <div class="layui-input-block">
                            <input type="text" placeholder="请输入合并名称" class="layui-input item1">
                        </div>
                    </div>                   
                                  
                </div>
                <div class="layui-col-md12">                
                    <div class="layui-form-item">
                        <div class="layui-card">                        
                            <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test1">支付宝个人码</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo1">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test2">微信个人码</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo2">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div> 
                    
                </div>   
                <div class="layui-form-item">
                    <div class="layui-input-block" style="margin-left:0;">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit1">确认合并</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- 商户性质 -->
<input type="hidden" class="store_name" value="">
<input type="hidden" class="store_type" value="">
<input type="hidden" class="category_name" value="">
<input type="hidden" class="category_id" value="">
<!-- 卡类型 -->
<input type="hidden" class="cardtype_id" value="">
<!-- 银行卡 -->
<input type="hidden" class="bankname" value="">
<input type="hidden" class="sub_bank_name" value="">
<input type="hidden" class="bank_no" value="">

<canvas id="qr-canvas" width="800" height="600" style="width: 800px; height: 600px;"></canvas>
<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<!-- <script src="{{asset('/user/js/qrcode.js')}}"></script>  -->
<!-- <script type="text/javascript" src="{{asset('/user/js/llqrcode.js')}}"></script>
<script type="text/javascript" src="{{asset('/user/js/webqr.js')}}"></script> -->
<!-- <script type="text/javascript" src="{{asset('/user/js/qrcode.js')}}"></script> -->
<script>
    var token = localStorage.getItem("Usertoken");
   
   

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
                window.location.href="{{url('/user/login')}}"; 
            }
        })




        


        //身份证正面
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token+"&type="+"img", //提交到的地址 可以自定义其他参数
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
        //身份证反面
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token+"&type="+"img",  //提交到的地址 可以自定义其他参数
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

        $('.submit1').click(function(){

            $.post("{{url('/api/user/qr_code_hb_add')}}",
            {
              token:token,
              code_name:$('.item1').val(),
              ali_code_url:$('#demo1').attr('src'),
              wx_code_url:$('#demo2').attr('src')
            }, 
            function(res){
              console.log(res); 
              if(res.status==1){
                layer.msg(res.message, {
                  offset: '15px'
                  ,icon: 1
                  ,time: 3000
                });
                
              }else{                
                layer.alert(res.message, {icon: 2});//错误提示
              }
            },"json");
        })
        


    });
</script>
</body>
</html>
