<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>查看广告信息</title>
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
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">广告信息</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-row layui-form" lay-filter="component-form-group">  
            <div class="layui-col-md6">              
                <div class="layui-form-item" style="width:500px;">
                    <label class="layui-form-label">广告标题</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div> 
                <div class="layui-form-item">
                    <label class="layui-form-label">广告投放为位置</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">广告生效范围</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid">
                            <div><label>代理商：</label><span class="agent"></span></div>
                            <div><label>门店：</label><span class="store"></span></div>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">广告投放时间</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">广告复制粘贴内容</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
                <div class="layui-form-item">   
                    <label class="layui-form-label">广告展示图片</label>
                        <div class="img_box_con">
                            
                            <!-- <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;">
                                <div class="img sfz_z"><img data-type="test" src="{{asset('/school/images/zanwu.png')}}"></div>
                                <label class="layui-form-label">身份证正面</label>
                            </div>
                            <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                                <div class="img sfz_f"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                                <label class="layui-form-label">身份证反面</label>
                            </div> -->
                        </div>                 
                        
                    </div> 
                </div>   

            </div>
         

        </div>
    </div>
   
    
</div>


<!-- 第一部分 -->
       

<div class="img_box">
    
</div> 



<input type="hidden" class="schooltypeid" value="">
<input type="hidden" class="gradeid" value="">
<input type="hidden" class="classid" value="">
<input type="hidden" class="statusid" value="">
<input type="hidden" class="relationshipid" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    var str=location.search;
    var id=str.split('?')[1];

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects'], function(){
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
        $.post("{{url('/api/ad/ad_info')}}",
        {
            token:token,
            id:id,            

        },function(res){
            console.log(res);
            if(res.status==1){
                $('.layui-row .layui-form-item').eq(0).find('.layui-form-mid').html(res.data.title);
                $('.layui-row .layui-form-item').eq(1).find('.layui-form-mid').html(res.data.ad_p_desc);
                $('.layui-row .layui-form-item').eq(2).find('.layui-form-mid .agent').html(res.data.user_names);
                $('.layui-row .layui-form-item').eq(2).find('.layui-form-mid .store').html(res.data.store_names);
                $('.layui-row .layui-form-item').eq(3).find('.layui-form-mid').html(res.data.s_time+'--'+res.data.e_time);
                $('.layui-row .layui-form-item').eq(4).find('.layui-form-mid').html(res.data.copy_content);

                var str=res.data.imgs
                var data=JSON.parse(str);
                console.log(data);
                var html='';
                var str='';
                for(var i=0;i<data.length;i++){
                    html+='<div id="sfz_z" class="hide" style="display: none"><img style="width:100%;height:100%" src="'+data[i].img_url+'"></div>';
                }
                $('.img_box').append(html);
                
                for(var j=0;j<data.length;j++){
                    str+='<div class="layui-input-block" style="width:130px;display: inline-block;">';
                        str+='<div class="img sfz_z"><img data-type="test" src="'+data[j].img_url+'"></div>';
                        str+='<label class="layui-form-label" style="padding:9px 0">'+data[j].click_url+'</label>';
                    str+='</div>';
                }
                $('.img_box_con').append(str);
                
                
            }
        },"json");
        $('.sfz_z').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#sfz_z')
            });
        });
        $('.sfz_f').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#sfz_f')
            });
        });


    });
</script>
</body>
</html>
