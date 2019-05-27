<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>商户号</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">商户号</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">商户号</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">加签密钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">加密密钥</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="" class="layui-input">
                    </div>
                </div>
                <!-- <div class="layui-form-item xingzhi">
                    <label class="layui-form-label">通道状态</label>
                    <div class="layui-input-block">
                        <select name="open" id="open" lay-filter="open">
                            <option value="">选择通道状态</option>
                            <option value="1">已开通</option>
                            <option value="2">审核中</option>
                            <option value="3">审核失败</option>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item status">
                    <label class="layui-form-label">状态说明</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolshortname" lay-verify="schoolshortname" autocomplete="off" placeholder="" class="layui-input">                        
                    </div>
                </div> -->
                


                

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


<input type="hidden" class="pay_status">
<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    var store_id='{{$_GET['store_id']}}'
    var ways_type='{{$_GET['ways_type']}}'
    

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
    // 未登录,跳转登录页面
    $(document).ready(function(){        
        if(token==null){
            window.location.href="{{url('/user/login')}}"; 
        }
    })
    form.on('select(open)', function(data){            
        category = data.value;  
        categoryName = data.elem[data.elem.selectedIndex].text; 
        $('.pay_status').val(category);  
        if(category == 3){
            $('.status').show()
        }else{
            $('.status').hide()
        }
        
        
    });
    $.post("{{url('/api/user/open_ways_type')}}",
    {
        token:token,
        store_id:store_id,
        ways_type:ways_type

    },function(res){
        console.log(res);
        $('.layui-form .layui-form-item').eq(0).find('input').val(res.data.merchant_no);
        $('.layui-form .layui-form-item').eq(1).find('input').val(res.data.md_key);
        $('.layui-form .layui-form-item').eq(2).find('input').val(res.data.des_key);
        // $('.layui-form .layui-form-item').eq(3).find('input').val(res.data.systemId);
        // $('.layui-form .layui-form-item').eq(4).find('textarea').html(res.data.ali_appid);
        // $('.layui-form .layui-form-item').eq(5).find('textarea').html(res.data.wx_appid);
        // $('.layui-form .layui-form-item').eq(6).find('input').val(res.data.wx_secret);
    },"json");
        


    $('.submit').on('click', function(){
       
        $.post("{{url('/api/user/open_ways_type')}}",
        {
            token:token,
            store_id:store_id,
            ways_type:ways_type,
            merchant_no:$('.layui-form .layui-form-item').eq(0).find('input').val(),
            md_key:$('.layui-form .layui-form-item').eq(1).find('input').val(),
            des_key:$('.layui-form .layui-form-item').eq(2).find('input').val(),           
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
