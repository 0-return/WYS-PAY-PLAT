<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>门店配置</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    
    <div class="layui-card">
      <div class="layui-card-header">门店配置</div>
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-form">
            <div class="layui-form-item" pane="">
                <label class="layui-form-label">是否开启商户审核</label>
                <div class="layui-input-block">
                  <input class="fws" type="checkbox" name="student" lay-skin="primary" lay-filter="encrypt1" value="0" title="服务商审核">
                </div>
                <div class="layui-input-block">
                  <input class="gly" type="checkbox" name="student" lay-skin="primary" lay-filter="encrypt2" value="0" title="管理员审核">
                </div>
            </div>
            
            

            <div class="layui-form-item layui-layout-admin">
                <div class="layui-input-block">
                    <div class="layui-footer" style="left: 0;">
                        <button class="layui-btn submit">确定提交</button>
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
    var agentName = localStorage.getItem("agentName");
    var str=location.search;
    var user_id=str.split('?')[1];
    console.log(user_id)
    

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
            if(user_id == undefined){
                $.post("{{url('/api/user/user_store_set_status')}}",
                {
                    token:token,
                    user_id:'0'                   

                },function(res){
                    console.log(res);
                    if(res.status==1){
                        if(res.data.status_check == 1){                    
                            $('.fws').val('1')
                            $('.fws').attr('checked','true')
                            form.render('checkbox');
                        }else{
                            $('.fws').val('0')
                            $('.fws').removeAttr('checked')
                            form.render('checkbox');
                        }
                        // 分界线++++++++
                        if(res.data.admin_status_check == 1){
                            $('.gly').val('1')
                            $('.gly').attr('checked','true')
                            form.render('checkbox');
                        }else{
                            $('.gly').val('0')
                            $('.gly').removeAttr('checked')
                            form.render('checkbox');
                        }
                    }else{
                        
                    }
                    
                },"json");
            }else{
                $('.layui-card-header').html(agentName);
                $.post("{{url('/api/user/user_store_set_status')}}",
                {
                    token:token,
                    user_id:user_id                

                },function(res){
                    console.log(res);
                    if(res.status==1){
                        if(res.data.status_check == 1){                    
                            $('.fws').val('1')
                            $('.fws').attr('checked','true')
                            form.render('checkbox');
                        }else{
                            $('.fws').val('0')
                            $('.fws').removeAttr('checked')
                            form.render('checkbox');
                        }
                        // 分界线++++++++
                        if(res.data.admin_status_check == 1){
                            $('.gly').val('1')
                            $('.gly').attr('checked','true')
                            form.render('checkbox');
                        }else{
                            $('.gly').val('0')
                            $('.gly').removeAttr('checked')
                            form.render('checkbox');
                        }
                    }else{
                        
                    }
                },"json");
            }

            
        })


        form.on('checkbox(encrypt1)', function(data){
            // console.log(data.elem); //得到checkbox原始DOM对象
            // console.log(data.elem.checked); //是否被选中，true或者false
            // console.log(data.value); //复选框value值，也可以通过data.elem.value得到
            // console.log(data.othis); //得到美化后的DOM对象

            if(data.elem.checked == true){
                $(this).val('1')
            }else{
                $(this).val('0')
            }
            
        });    
        form.on('checkbox(encrypt2)', function(data){
            // console.log(data.elem); //得到checkbox原始DOM对象
            // console.log(data.elem.checked); //是否被选中，true或者false
            // console.log(data.value); //复选框value值，也可以通过data.elem.value得到
            // console.log(data.othis); //得到美化后的DOM对象

            if(data.elem.checked == true){
                $(this).val('1')
            }else{
                $(this).val('0')
            }
            
        });
        

        $('.submit').on('click', function(){
            if(user_id == undefined){
                $.post("{{url('/api/user/user_store_set_status')}}",
                {
                    token:token,
                    status_check:$('.fws').val(),
                    admin_status_check:$('.gly').val(),
                    user_id:'0'                   

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

                $.post("{{url('/api/user/user_store_set_status')}}",
                {
                    token:token,
                    status_check:$('.fws').val(),
                    admin_status_check:$('.gly').val(),
                    user_id:user_id                    

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


    });
</script>
</body>
</html>
