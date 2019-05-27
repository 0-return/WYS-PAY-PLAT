<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加消息</title>
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
        .layui-card-header{width:200px;text-align: left;float:left;}
        .layui-card-body{margin-left:28px;}
        .layui-upload-img{width: 120px; height: 120px; /*margin: 0 10px 10px 0;*/}

        .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;width: auto !important;font-size: 10px !important;text-align: center !important;}
        .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
        .layui-upload-list{width: 120px;height:120px;overflow: hidden;margin: 10px auto;}
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
        
    </style>
</head>
<body>

<div class="layui-fluid">
    
    <div class="layui-card">
      <div class="layui-card-header">添加app消息</div>
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-form">
            <div class="layui-form-item school">
                <label class="layui-form-label">消息类型</label>
                <div class="layui-input-block">
                    <select name="schooltype" id="schooltype" lay-filter="schooltype">
                        
                    </select>
                </div>
            </div> 
            <div class="layui-form-item">
                <label class="layui-form-label">标题</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入标题" class="layui-input templatename">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">消息链接</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入链接" class="layui-input templatedesc">
                </div>
            </div>
            
            <div class="layui-form-item">
                <label class="layui-form-label">封面图片</label>
                <div class="layui-card">                        
                    <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                        <div class="layui-upload">
                            <button class="layui-btn up"><input type="file" name="img_upload" class="test1">上传封面图片</button>
                            <div class="layui-upload-list">
                               <img class="layui-upload-img" id="demo1">
                               <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    
                </div>
                
            </div> 


            <div class="layui-form-item layui-layout-admin">
                <div class="layui-input-block">
                    <div class="layui-footer" style="left: 0;">
                        <button class="layui-btn submit">确定提交</button>
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


<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'table','form','upload'], function(){
        var $ = layui.$   
            admin = layui.admin         
            ,table = layui.table
            ,element = layui.element
            ,upload = layui.upload
            ,form = layui.form;

            element.render();
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })
        
        var arr=[];
        //监听工具条
        table.on('tool(test-table-cellEdit)', function(obj){
          var data = obj.data;
          var tr = obj.tr;
          var which=tr[0].dataset.index;//删除行的下标
          
          if(obj.event === 'del'){
            layer.confirm('确定删除缴费小项吗?', function(index){
                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                layer.close(index);

                //向服务端发送删除指令
                console.log(which);
                tablenew.splice(which,1);//删除tablenew数组下标为which,长度为1的一个值
                console.log(tablenew);               
                
            });
            
          } 
        });

        $.ajax({
            url : "{{url('/api/user/notice_news_type')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].type + "'>"
                            + data.data[i].type_desc + "</option>";
                    }    
                    $("#schooltype").append('<option value="">选择消息类型</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });

        getBoards();
        
       
        function getBoards(){ 
            // 选择员工
            
            
            
        }

        

        form.on('select(schooltype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.schooltypeid').val(category); 
            $('.schooltypename').val(categoryName);
        });
    

    getBoards();
       
    function getBoards(){ 
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

    }

        

        

        $('.submit').on('click', function(){

            $.post("{{url('/api/user/add_notice_news')}}",
            {
                token:token,
                title:$('.templatename').val(),
                redirect_url:$('.templatedesc').val(),
                type:$('.schooltypeid').val(),
                type_desc:$('.schooltypename').val(),
                icon_url:$('#demo1').attr('src')

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

        });


    });
</script>
</body>
</html>
