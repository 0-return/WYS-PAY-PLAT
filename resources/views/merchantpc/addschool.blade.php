<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加学校</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
    .layui-card-header{width:80px;text-align: right;float:left;}
    .layui-card-body{margin-left:28px;}
    .layui-upload-img{width: 92px; height: 92px; margin: 0 10px 10px 0;}

    .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;width: 92px !important;font-size: 10px !important;text-align: center !important;}
    .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
    .layui-upload-list{width: 100px;height:96px;overflow: hidden;}
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
    
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header" style="width:auto !important">添加学校&nbsp;&nbsp;&nbsp;<span class="zong_school_name"></span></div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item school">
                    <label class="layui-form-label">平台ID</label>
                    <div class="layui-input-block">
                        <select name="platformID" id="platformID" lay-filter="platformID">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item school">
                    <label class="layui-form-label">学校类型</label>
                    <div class="layui-input-block">
                        <select name="schooltype" id="schooltype" lay-filter="schooltype">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">学校名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入学校名称" class="layui-input name">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">学校简称</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="请输入学校简称" class="layui-input shortname">                        
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-card">
                        <div class="layui-card-header">学校logo</div>
                        <div class="layui-card-body" style="margin-left:28px;float:left;">
                            <div class="layui-upload" style="width:100px;">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test1">上传logo</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo1">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">学校(机构)标识码</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolcode" lay-verify="schoolcode" autocomplete="off" placeholder="请输入学校标识码" class="layui-input schoolcode">
                        <div class="layui-form-mid layui-word-aux">由教育部按照国家标准及编码规则编制，可以在教育局官网查询,没有可不填</div>
                    </div>
                </div>



                <div class="layui-form-item">
                    <label class="layui-form-label">所在地</label>
                    <div class="layui-input-block">
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



                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">确定提交</button>
                            <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
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
<input type="hidden" class="areacode" value="">
<input type="hidden" class="areaname" value="">

<input type="hidden" class="storeId" value="">



<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("token");
    // var str=location.search;
    // var school_name=str.split('?')[1];


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

            var src=$('#demo1').attr('src');
        element.render();
        // 学校类型选择

        


        form.on('select(schooltype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.schooltypeid').val(category);
            $('.schooltypename').val(categoryName);           
        });
        form.on('select(platformID)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.storeId').val(category);
            // $('.schooltypename').val(categoryName);           
        });

        getBoards();
        // 地区选择
        function getBoards(){
            $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:'1'},
                type : 'get',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "'>"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#province").append('<option value="">请选择省</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 学校类型
            $.ajax({
                url : "{{url('/api/school/teacher/typelst')}}",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].type + "'>"
                                + data.data[i].name + "</option>";
                        }    
                        $("#schooltype").append('<option value="">学校类型</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 平台id
            $.ajax({
                url : "{{url('/api/merchant/store_lists')}}",
                data : {token:token,return_type:'0'},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].store_id + "'>"
                                + data.data[i].store_name + "</option>";
                        }    
                        $("#platformID").append('<option value="">选择平台id</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
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
                    layui.jquery('#demo1').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });

        var pid = sessionStorage.getItem("branch_pid");//获取上级学校pid
        var zong_school_name = sessionStorage.getItem("add_branch_school_name");//获取总校名称
        // $('.zong_school_name').html('总校:'+zong_school_name);

        $('.submit').on('click', function(){
            // console.log('000');
            // window.parent.layui.element.tabChange('layadmin-layout-tabs', "{{url('/merchantpc/schoollist')}}");
           
            // window.parent.layui.element.render('tab','layadmin-layout-tabs');
            // var index=parent.layer.getFrameIndex(window.name);
            // parent.layer.close(index);
            // window.parent.location.reload();



            $.post("{{url('/api/school/teacher/add')}}",
            {
                token:token,
                store_id:$('.storeId').val(),
                school_name:$('.name').val(),
                school_sort_name:$('.shortname').val(),
                school_icon:$('#demo1').attr('src'),
                school_stdcode:$('.schoolcode').val(),
                school_type:$('.schooltypeid').val(),

                province_code:$('.provincecode').val(),
                province_name:$('.provincename').val(),
                city_code:$('.citycode').val(),
                city_name:$('.cityname').val(),
                district_code:$('.areacode').val(),
                district_name:$('.areaname').val(),
                su_store_address:$('.address').val()

            },function(res){
                console.log(res);

                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 3000
                    },function(){
                        element.tabAdd('demo', {
                           title: '学校列表'
                           ,id: "{{url('/merchantpc/schoollist')}}"
                        });
                    });


                    
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
