<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>微信配置</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
        .up{position: relative;display: inline-block;cursor: pointer;background-color: #1ab394;border-color: #1ab394; color: #FFF;width: 100px !important;text-align: center !important; padding: 0px 8px !important;}
        .up input {
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            opacity: .01;
            width: 100px;
            height: 32px;
        }
        .modal{top:25% !important;}
        .gohome{display: none;}
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">微信配置</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">公众号授权</label>
                    <div class="layui-input-block">
                        <button class="layui-btn shouquan">点击授权(未授权)</button>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">公众号id</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                        <div class="layui-form-mid layui-word-aux">公众号授权可不填写</div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">公众号密钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                        <div class="layui-form-mid layui-word-aux">公众号授权可不填写</div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">微信服务商商户号</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">微信支付key</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">公众号提醒appID</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">公众号提醒秘钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">公众号提醒模板ID</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">公众号名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">证书文件</label>
                    <div class="layui-input-block" style="width: 70%;float: left;display: inline-block;margin-left: 0px;">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input zs">    
                    </div>
                    <div class="col-sm-1" style="float:right;">
                        <div class="btn-img" style="width:115px;">
                            <button class="layui-btn up"><input type="file" name="img_upload" class="test1">上传证书文件</button>
                        </div>
                    </div>
                </div>                
                <div class="layui-form-item">
                    <label class="layui-form-label">密钥文件</label>
                    <div class="layui-input-block" style="width: 70%;float: left;display: inline-block;margin-left: 0px;">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input my">    
                    </div>
                    <div class="col-sm-1" style="float:right;">
                        <div class="btn-img" style="width:115px;">
                            <button class="layui-btn up"><input type="file" name="img_upload" class="test2">上传密钥文件</button>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">域名验证文件</label>
                    <div class="layui-input-block"  style="width: 70%;float: left;display: inline-block;margin-left: 0px;">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input ym">
                        <div class="layui-form-mid layui-word-aux">公众号授权可不填写</div>                           
                    </div>
                    <div class="col-sm-1" style="float:right;">
                        <div class="btn-img" style="width:115px;">
                            <button class="layui-btn up"><input type="file" name="img_upload" class="test3">域名验证文件</button>
                        </div>
                    </div>
                </div>
                

                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">保存</button>
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
    

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects','element'], function(){
        var $ = layui.$ 
            ,admin = layui.admin
            ,element = layui.element
            ,form = layui.form
            ,upload = layui.upload
    // 未登录,跳转登录页面
    $(document).ready(function(){        
        if(token==null){
            window.location.href="{{url('/user/login')}}"; 
        }
    })
    $.post("{{url('/api/user/weixin_config')}}",
    {
        token:token,
        type:'2'

    },function(res){
        console.log(res);
        $('.layui-form .layui-form-item').eq(1).find('input').val(res.data.app_id);
        $('.layui-form .layui-form-item').eq(2).find('input').val(res.data.app_secret);
        $('.layui-form .layui-form-item').eq(3).find('input').val(res.data.wx_merchant_id);
        $('.layui-form .layui-form-item').eq(4).find('input').val(res.data.key);
        $('.layui-form .layui-form-item').eq(5).find('input').val(res.data.wx_notify_appid);
        $('.layui-form .layui-form-item').eq(6).find('input').val(res.data.wx_notify_secret);
        $('.layui-form .layui-form-item').eq(7).find('input').val(res.data.template_id);
        $('.layui-form .layui-form-item').eq(8).find('input').val(res.data.app_name);
        $('.layui-form .layui-form-item').eq(9).find('input.zs').val(res.data.cert_path);
        $('.layui-form .layui-form-item').eq(10).find('input.my').val(res.data.key_path);
        $('.layui-form .layui-form-item').eq(11).find('input.ym').val(res.data.auth_path);
        if(res.data.authorizer_appid==''){
            $('.shouquan').html('点击授权(未授权)');
        }else{
            $('.shouquan').html('更新授权('+res.data.app_id+')');
        }
    },"json");

    $('.shouquan').click(function(){
        // location.href = "{{url('/api/weixinopen/openoauth?token=')}}"+token;
        window.open('{{url('/api/weixinopen/openoauth?token=')}}'+token);
    })


    // demo1
    upload.render({
        url : "{{url('/api/basequery/webupload?act=file')}}"+'&token='+token+'&type='+'wxfile',  //提交到的地址 可以自定义其他参数
        elem : '.test1',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
        method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
        type : 'file',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
        accept : 'file',
        /*ext : 'jpg|png|gif',*/    //自定义支持的文件格式
        unwrap : true, //是否不改变input的样式风格。默认false 

        before : function(obj){
            //执行上传前的回调  可以判断文件后缀等等
            
            layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
        },
        done: function(res){
            console.log(res);
            if(res.status == 0){
                layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
            }else{
                layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                layui.jquery('.zs').val(res.data.img_url);
            }
        }
    });
    // demo2
    upload.render({
        url : "{{url('/api/basequery/webupload?act=file')}}"+'&token='+token+'&type='+'wxfile',  //提交到的地址 可以自定义其他参数
        elem : '.test2',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
        method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
        type : 'file',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
        accept : 'file',
        /*ext : 'jpg|png|gif',*/    //自定义支持的文件格式
        unwrap : true, //是否不改变input的样式风格。默认false 

        before : function(obj){
            //执行上传前的回调  可以判断文件后缀等等
            
            layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
        },
        done: function(res){
            console.log(res);
            if(res.status == 0){
                layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
            }else{
                layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                layui.jquery('.my').val(res.data.img_url);
            }
        }
    });
    // demo3
    upload.render({
        url : "{{url('/api/basequery/webupload?act=file')}}"+'&token='+token+'&type='+'wxauth',  //提交到的地址 可以自定义其他参数
        elem : '.test3',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
        method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
        type : 'file',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
        accept : 'file',
        /*ext : 'jpg|png|gif',*/    //自定义支持的文件格式
        unwrap : true, //是否不改变input的样式风格。默认false 

        before : function(obj){
            //执行上传前的回调  可以判断文件后缀等等
            
            layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
        },
        done: function(res){
            console.log(res);
            if(res.status == 0){
                layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
            }else{
                layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                layui.jquery('.ym').val(res.data.img_url);
            }
        }
    });

    $('.submit').on('click', function(){
        $.post("{{url('/api/user/weixin_config')}}",
        {
            token:token,
            type:"1",
            app_id:$('.layui-form .layui-form-item').eq(1).find('input').val(),
            app_secret:$('.layui-form .layui-form-item').eq(2).find('input').val(),
            wx_merchant_id:$('.layui-form .layui-form-item').eq(3).find('input').val(),
            key:$('.layui-form .layui-form-item').eq(4).find('input').val(),
            wx_notify_appid:$('.layui-form .layui-form-item').eq(5).find('input').val(),
            wx_notify_secret:$('.layui-form .layui-form-item').eq(6).find('input').val(),
            template_id:$('.layui-form .layui-form-item').eq(7).find('input').val(),
            app_name:$('.layui-form .layui-form-item').eq(8).find('input').val(),
            
            cert_path:$('.layui-form .layui-form-item').eq(9).find('input').val(),
            key_path:$('.layui-form .layui-form-item').eq(10).find('input').val(),
            auth_path:$('.layui-form .layui-form-item').eq(11).find('input').val(),
            
        },function(res){
            console.log(res);

            if(res.status==1){
                layer.msg(res.message, {
                    offset: '15px'
                    ,icon: 1
                    ,time: 3000
                });
            }else{
                layer.alert(res.message, {icon: 2});
            }

        },"json");

    });

    });
</script>

</body>
</html>
