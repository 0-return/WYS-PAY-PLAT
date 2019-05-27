<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加banner</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/formSelects-v4.css')}}" media="all">
    <style>
    .layui-card-header{width:80px;text-align: right;float:left;}
    .layui-card-body{margin-left:28px;}
    /*.layui-upload-img{width: 92px; height: 92px; margin: 0 10px 10px 0;}*/

    .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;width: 92px !important;font-size: 10px !important;text-align: center !important;}
    .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
    .layui-upload-list{overflow: hidden;}
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
    .layui-upload{width:100%;}
    #demo1{width:100%;}
    .layui-card{box-shadow:none;}
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">添加banner</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item class">
                    <label class="layui-form-label">位置</label>
                    <div class="layui-input-block">
                        <select name="store" id="store" xm-select="store">
                            
                        </select>
                    </div>
                </div>
               
                <div class="layui-form-item school">
                    <label class="layui-form-label">banner标题</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入banner标题" class="layui-input title">
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-card-header">banner图片</div>
                    <div class="layui-card">
                        <div class="layui-card-body" style="display: inline-block;padding:0;margin-left:0;width:70%;">
                           <input type="text" placeholder="请输入图片链接或者上传图片" class="layui-input layui-upload-img" id="demo1">  
                        </div>
                        <button class="layui-btn up"><input type="file" name="img_upload" class="test1">上传图片</button>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">跳转链接</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入跳转链接" class="layui-input url">
                    </div>
                </div>
                <div class="layui-form-item">  
                    <label class="layui-form-label">开始时间</label>                        
                    <div class="layui-input-block">                      
                      <input type="text" class="layui-input start-item test-item" placeholder="开始时间" lay-key="23">
                    </div>                   
                </div>   
                <div class="layui-form-item">  
                    <label class="layui-form-label">结束时间</label>                        
                    
                    <div class="layui-input-block">
                      <input type="text" class="layui-input end-item test-item" placeholder="结束时间" lay-key="24">
                    </div>
                </div>   
                

                <div class="layui-form-item">
                    <label class="layui-form-label">排序</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolcode" lay-verify="schoolcode" autocomplete="off" placeholder="请输入排序" class="layui-input sort">                        
                    </div>
                </div>



                
                <div class="layui-form-item" lay-filter="component-form-element">
                  <div class="layui-card-header">状态</div>
                  <div class="layui-input-block">
                    <div class="layui-col-md12">
                      <input type="checkbox" name="zzz" lay-skin="switch" id="tongguo" lay-text="开启|关闭" checked>
                    </div>
                  </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">描述</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolcode" lay-verify="schoolcode" autocomplete="off" placeholder="请输入描述" class="layui-input desc">                        
                    </div>
                </div>



                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">添加</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" class="schooltypeid" value="">
<input type="hidden" class="schooltypename" value="">
<input type="hidden" class="provincecode" value="">
<input type="hidden" class="provincename" value="">
<input type="hidden" class="citycode" value="">
<input type="hidden" class="cityname" value="">

<input type="hidden" class="classid" value="">
<input type="hidden" class="classname" value="">

<div id="BOX" style="display:none">
    
</div>


<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
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

           
        formSelects.render('store');
        formSelects.btns('store', []);
        var arr=[];
        formSelects.config('store', {
            beforeSuccess: function(id, url, searchVal, result){
                //我要把数据外层的code, msg, data去掉
                result = result.data;
                console.log(result);
                for(var i=0;i<result.length;i++){

                    var data ={"value":result[i].type,"name":result[i].type_desc}
                    arr.push(data);
                    // console.log(arr);
                }
                console.log(arr);
                //然后返回数据
                return arr;
            }
        }).data('store', 'server', {
            url:"{{url('/api/user/banner_type?token=')}}"+token
        });
        var arra=[];
        var arrb=[];
        var optionStr='';
        formSelects.on('store', function(id, vals, val, isAdd, isDisabled){
            var optionStr='';
            console.log(val.value);
            if(isAdd==true){
                
                
                arra.push(val.value); 
                arrb.push(val.name); 
                // console.log(arra.join());
                // console.log(arrb.join());

                $('.classid').val(arra.join());
                $('.classname').val(arrb.join());

                optionStr += '<div class="s_box" class_id="'+$('.classid').val()+'"></div>';
                $("#BOX").append(optionStr);
                        
            }else{
                $('#BOX .s_box').each(function(index,item){  

                    if(val.value==$(item).attr('class_id')){
                        
                        $(this).remove();
                    }
                })
            }
            
        });

        
 

 


        


        //普通图片上传
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test1',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif|jpeg',    //自定义支持的文件格式
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
                    // layui.jquery('#demo1').attr("src", res.data.img_url);
                    $('#demo1').val(res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });



        $('.submit').on('click', function(){
            if($('#tongguo').is(':checked')) {

                $.post("{{url('/api/user/add_banners')}}",
                {
                    token:token,
                    type:$('.classid').val(),
                    type_desc:$('.classname').val(),
                    title:$('.title').val(),
                    img_url:$('#demo1').val(),
                    banner_time_s:$('.start-item').val(),
                    banner_time_e:$('.end-item').val(),
                    action_url:$('.url').val(),
                    sort:$('.sort').val(),
                    status:'1',
                    banner_desc:$('.desc').val()

                },function(res){
                    console.log(res);

                    if(res.status==1){
                        layer.msg(res.message, {
                            offset: '15px'
                            ,icon: 1
                            ,time: 3000
                        });
                    }else{
                        layer.msg(res.message, {
                            offset: '15px'
                            ,icon: 2
                            ,time: 3000
                        });
                    }

                },"json");
            }else{
                $.post("{{url('/api/user/add_banners')}}",
                {
                    token:token,
                    type:$('.classid').val(),
                    type_desc:$('.classname').val(),
                    title:$('.title').val(),
                    img_url:$('#demo1').val(),
                    banner_time_s:$('.start-item').val(),
                    banner_time_e:$('.end-item').val(),
                    action_url:$('.url').val(),
                    sort:$('.sort').val(),
                    status:'2',
                    banner_desc:$('.desc').val()

                },function(res){
                    console.log(res);

                    if(res.status==1){
                        layer.msg(res.message, {
                            offset: '15px'
                            ,icon: 1
                            ,time: 3000
                        });
                    }else{
                        layer.msg(res.message, {
                            offset: '15px'
                            ,icon: 2
                            ,time: 3000
                        });

                    }

                },"json");
            }

        });
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
            
          }
        });

    });
</script>

</body>
</html>
