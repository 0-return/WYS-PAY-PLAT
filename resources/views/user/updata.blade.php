<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>系统更新</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .cur{color:#009688;}
    .del {background-color: #e85052;}    /*.laytable-cell-1-school_icon{height:100%;}*/
  </style>
</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      <div class="layui-col-md12">

        <div class="layui-fluid">
          <div class="layui-row layui-col-space15">
            <div class="layui-col-md12">
              <div class="layui-card"> 
                <div class="layui-card-header">系统更新</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <div class="layui-btn layui-btn-primary" id="version"></div>
                    <div class="layui-btn" id="up"></div>
                    <div class="layui-card-body">
                      <ul>
                          <li><span id="old"></span>&nbsp;:&nbsp;版本内容:<span class="old"></span></li>
                          <li style="margin-top:5px;"><span id="new"></span>&nbsp;:&nbsp;版本内容:<span class="new"></span></li>  
                      </ul>
                    </div>

                  </div>
                  
                </div>
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
        index: 'lib/index' //主入口模块
    }).use(['index','form', 'table','laydate'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,table = layui.table
            ,form = layui.form
            ,laydate = layui.laydate;

        
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }

            $.get("{{url('/api/basequery/updateInfo')}}",
            {
                token:token
            },function(res){
                console.log(res);
            if(res.status==1){    
                $('#version').html('版本号:'+res.app_version);
                $('#up').html('立即更新:'+res.update_version);

                $('#old').html(res.app_version);
                $('#new').html(res.update_version);
                $('.old').html(res.app_msg);
                $('.new').html(res.update_msg);

                if(res.update_status==1){
                    $('#version').show();
                }else{
                    $('#version').hide();
                }
            }
            },"json");
        })


        $('#up').click(function(){
        $.get("{{url('/api/basequery/appUpdateFile')}}",
        {
            token:token
        },function(res){
            console.log(res);
            if(res.status==1){
              layer.msg(res.message, {
                offset: '15px'
                ,icon: 1
                ,time: 2000
              });
            }else{
              layer.msg(res.message, {
                offset: '15px'
                ,icon: 2
                ,time: 2000
              });
            }
            
        },"json");
    });




        


    });


  </script>

</body>
</html>