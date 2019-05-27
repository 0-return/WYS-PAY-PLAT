<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>转移门店</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style type="text/css">
        .userbox{
            height:200px;
            overflow-y: auto;
        }
        .userbox .list{
            height:38px;line-height: 38px;cursor:pointer;
            padding-left:10px;
        }
        .userbox .list:hover{
            background-color:#eeeeee;
        }
    </style>
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">转移门店</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                <div class="layui-form-item">
                    <label class="layui-form-label">转移门店：</label>
                    <div class="layui-input-block">
                        <div class="store_name" style='line-height: 38px;'></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">转移到:</label>
                    <div class="layui-input-block">
                        <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入归属手机号或者名字" class="layui-input transfer">

                        <div class="userbox" style='display: none'></div>
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

<input type="hidden" class="js_user_id">

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
    // 未登录,跳转登录页面
    $(document).ready(function(){        
        if(token==null){
            window.location.href="{{url('/user/login')}}"; 
        }
    })

     function GetQueryString(name)
    {
         var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
         var r = window.location.search.substr(1).match(reg);
         if(r!=null)return  unescape(r[2]); return null;
    }
    var store_name=localStorage.getItem('js_add_store_name');
    var store_id=localStorage.getItem('js_add_store_id');

    $('.store_name').html(store_name)



    $(".transfer").bind("input propertychange",function(event){
       console.log($(this).val())
       $.post("{{url('/api/user/get_sub_users')}}",
        {
            token:token,
            user_name:$(this).val()            

        },function(res){
            console.log(res);
            var html="";
            console.log(res.t)
            if(res.t==0){
                $('.userbox').html('')
            }else{
                for(var i=0;i<res.data.length;i++){
                    html+='<div class="list" data='+res.data[i].id+'>'+res.data[i].name+'</div>'
                }
                $(".userbox").show()
                $('.userbox').html('')
                $('.userbox').append(html)
            }
            
        },"json");
    });

    $(".userbox").on("click",".list",function(){
  
        $('.transfer').val($(this).html())
        $('.js_user_id').val($(this).attr('data'))
        $('.userbox').hide()
    })
    




    $('.submit').on('click', function(){
        // console.log($('.private_key').val());
        $.post("{{url('/api/user/update_user')}}",
        {
            token:token,
            store_id:store_id,
            user_id:$('.js_user_id').val()
      
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
