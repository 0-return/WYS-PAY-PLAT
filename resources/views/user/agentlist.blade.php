<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>代理商列表</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
     .box a{
        margin-bottom:10px;
     }       
    </style>
</head>
<body>
<div class="layui-fluid" id="LAY-component-grid-mobile-pc">
    <div class="layui-row layui-col-space10">
      <div class="layui-col-xs4 layui-col-md4">
        <div class="layui-card">
            <div class="layui-card-header">代理商列表</div>
                <div class="layui-card-body" style="height:500px;overflow-y: auto;">
                    <div class="li_list" style="display: inline-block; width: 180px; padding: 10px;  overflow: auto;">
                       <ul id="demo"></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-xs6 layui-col-md6">
            <div class="layui-card">
                <div class="layui-card-header">代理商详情</div>
                <div class="layui-card-body">
                    <div class="cmdlist-container">            
                        <img style='margin-top:-60px;' src="{{asset('/school/images/touxiang.png')}}">            
                        <div class="xinxi" style="display: inline-block;">
                            <p class="name"></p>
                            <p class="phone"></p>
                            <p class="jhm"><a lay-href="{{url('/user/qrcode')}}">激活码</a>:<span></span></p>
                            <p class="jine">赏金余额:<span></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-xs8 layui-col-md8">
            <div class="layui-card">
                <div class="layui-card-header">功能管理</div>
                <div class="layui-card-body">
                    <div class="cmdlist-container box"> 
                        <a class="layui-btn layui-btn-primary" lay-href="{{url('/user/addagent')}}" id="agent" data-id="">添加代理商</a>
                        <a class="layui-btn layui-btn-primary" id="role_fen" data-id="">角色分配</a>
                        <a class="layui-btn layui-btn-primary rate" id="rate" data-id="">成本费率管理</a>
                        <a class="layui-btn layui-btn-primary reward" id="reward" data-id="">赏金列表</a>
                        <a class="layui-btn layui-btn-primary putforward" id="putforward" data-id="">提现记录</a>
                        <a class="layui-btn layui-btn-primary storerate" id="storerate" data-id="">商户默认费率</a>
                        <a class="layui-btn layui-btn-primary storeconfig" id="storeconfig" data-id="">门店配置</a>
                        <a class="layui-btn layui-btn-primary chaxun" id="chaxun" data-id="">对账查询</a>
                        <a class="layui-btn layui-btn-primary settle" id="settle" data-id="">赏金结算明细</a>
                    </div>
                </div>
            </div>
        </div>



    </div>
<!-- <input class="search" ><button class="layui-btn">搜索</button> -->



<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    var level = localStorage.getItem("level");
    

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','tree'], function(){
        var $ = layui.$ 
            ,admin = layui.admin
            ,element = layui.tree
            ,form = layui.form;

        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null || token==''){
                window.location.href="{{url('/user/login')}}"; 
            }
        })

        var permissions = localStorage.getItem("permissions");
        var str=JSON.parse(permissions);
        var arr=[]
        for(var i=0;i<str.length;i++){
          var aa=str[i].name
          arr.push(aa)
        }
        // console.log(arr)


        if(level != 0){
          // 权限管理+++++++++++++
          $('.box a').each(function(index,item){
            // console.log($(this).attr('data'))
            // if($.inArray($(this).attr('data'),arr)==-1){
            //   $(this).hide()
            // }
          })
          $('.box a').each(function(index,item){
            // console.log($(this).find('a').html())
            // console.log($.inArray($(this).find('a').html(),arr))
            if($.inArray($(this).html(),arr)==-1){
              $(this).hide()
            }
          })
        }


        $.post("{{url('/api/user/get_sub_users')}}",
        {
            token:token,
            return_type:'layui'
        },function(res){
            console.log(res);
            $('.name').html(res.data[0].name);
            $('.phone').html(res.data[0].phone);
            $('.jhm span').html(res.data[0].s_code);
            $('.jine span').html(res.data[0].money);
            $('#agent').attr('data-id',res.data[0].id);
            $('#role_fen').attr('data-id',res.data[0].id);
            $('#rate').attr('data-id',res.data[0].id);
            $('#reward').attr('data-id',res.data[0].id);
            $('#putforward').attr('data-id',res.data[0].id);
            $('#storerate').attr('data-id',res.data[0].id);
            $('#storeconfig').attr('data-id',res.data[0].id);
            $('#chaxun').attr('data-id',res.data[0].id);
            $('#settle').attr('data-id',res.data[0].id);

            layui.tree({
              elem: '#demo'
              ,nodes: res.data
              ,click: function(node){
                console.log(node) //node即为当前点击的节点数据
                $('.name').html(node.name);
                $('.phone').html(node.phone);
                $('.jhm span').html(node.s_code);
                $('.jine span').html(node.money);
                $('#agent').attr('data-id',node.id);
                $('#rate').attr('data-id',node.id);
                $('#reward').attr('data-id',node.id);
                $('#putforward').attr('data-id',node.id);
                $('#role_fen').attr('data-id',node.id);
                $('#storerate').attr('data-id',node.id);
                $('#storeconfig').attr('data-id',node.id);
                $('#chaxun').attr('data-id',node.id);
                $('#settle').attr('data-id',node.id);
              }  
            });

        },"json");

        // 获取激活码
        $('.jhm').click(function(){
            localStorage.setItem('s_code', $(this).find('span').html());
            localStorage.setItem('s_agentname', $('.name').html());
        });
        $('#agent').click(function(){
            localStorage.setItem('agentname', $('.name').html());
            localStorage.setItem('dataid', $(this).attr('data-id'));
        })

        $('.layui-btn').click(function(){
            $.post("{{url('/api/user/get_sub_users')}}",
            {
                token:token,
                user_name:$('.search').val()
            },function(res){
                console.log(res);

            },"json");
        });
        $("#role_fen").click(function(){
            var customer_id=$(this).attr('data-id');
            layer.open({
              type: 2,
              title: '角色分配',
              shade: false,
              maxmin: true,
              area: ['70%', '70%'],
              content: "{{url('/user/rolelist?')}}"+customer_id
            });
        });
        $("#rate").click(function(){
            localStorage.setItem('agentName', $('.name').html());
            var user_id=$(this).attr('data-id');
            $('.rate').attr('lay-href',"{{url('/user/ratelist?')}}"+user_id);
        })
        $("#storerate").click(function(){
            localStorage.setItem('agentName', $('.name').html());
            var user_id=$(this).attr('data-id');
            $('.storerate').attr('lay-href',"{{url('/user/storeratelist?')}}"+user_id);
        })
        $("#reward").click(function(){
            localStorage.setItem('agentName', $('.name').html());
            var user_id=$(this).attr('data-id');
            $('.reward').attr('lay-href',"{{url('/user/reward?')}}"+user_id);
        })
        $("#putforward").click(function(){
            localStorage.setItem('agentName', $('.name').html());
            var user_id=$(this).attr('data-id');
            $('.putforward').attr('lay-href',"{{url('/user/putforward?')}}"+user_id);
        })
        $("#storeconfig").click(function(){
            localStorage.setItem('agentName', $('.name').html());
            var user_id=$(this).attr('data-id');
            $('.storeconfig').attr('lay-href',"{{url('/user/storeconfig?')}}"+user_id);
        })
        $("#chaxun").click(function(){
            localStorage.setItem('agentName', $('.name').html());
            var user_id=$(this).attr('data-id');
            var user_name=$('.name').html();
            $('.chaxun').attr('lay-href',"{{url('/user/reconciliation?user_id=')}}"+user_id+"&user_name="+user_name);
        })
        $("#settle").click(function(){
            localStorage.setItem('agentName', $('.name').html());
            var user_id=$(this).attr('data-id');
            $('.settle').attr('lay-href',"{{url('/user/settledetail?user_id=')}}"+user_id);
        })



        // function myFunction() {
        //     // 声明变量
        //     var input, filter, table, tr, td, i;
        //     input = document.getElementById("myInput");
        //     filter = input.value.toUpperCase();
        //     table = document.getElementById("demo");
        //     li = table.getElementsByTagName("li");
        //     // 循环表格每一行，查找匹配项
        //     for (i = 0; i < li.length; i++) {
        //         span = li[i].getElementsByTagName("span")[0];
        //         if (span) {
        //             if (span.innerHTML.toUpperCase().indexOf(filter) > -1) {
        //                 $('.con1').addClass('current');
        //                 li[i].style.display = "";
        //             } else {
        //                 li[i].style.display = "none";
        //             }
        //         }
        //     }
        // }
    

    });
</script>

</body>
</html>
