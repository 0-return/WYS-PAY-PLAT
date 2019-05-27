<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>学校列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    /*.del{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .cur{color:#009688;}
    .manage{background-color:##6c8ff5;}
    .water{background-color:#5fb878;}*/
    /*.laytable-cell-1-school_icon{height:100%;}*/
  </style>
  <style>
    /*.qrcode{width:560px;height:820px;background: url("../../layuiadmin/layui/images/bg-katai.png") no-repeat;background-size: 100%;margin:0 auto;}*/
    .qrcode{text-align: center;position: relative;}
    .qrcode img.bg_img{width:595px;height:auto;}
    .qrcode .img{position: absolute;left: 50%;margin-left: -90px;top: 50%; margin-top: 25px;width: 180px;}
    .qrcode .img canvas{width: 100%;border-radius: 8px;}
    .qrcode .schoolname{position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);margin-top: 235px; color: #fff;font-size: 16px;}
    .qrcode .g_c_name{display:none;width:240px;/*background:url("../../layuiadmin/layui/images/banji-bg.png") no-repeat;background-size: 100%;*/ position: absolute;left: 50%;bottom: 12%;transform: translate(-50%, -50%);margin-top: 340px; color: #fff;margin-left: 1%;color:#2aa1f7;font-size: 16px;line-height:36px;}
    .g_c_name span{position: absolute;width: 100%;left: 0;}
    .qrcode .g_c_name img{width:100%;}
    .qrcode .logo{position: absolute;left: 50%;bottom: 0;transform: translate(-160%, -50%);width: 150px;margin-bottom: -10px;}
    .qrcode .logo img{width:100%;}
    .qrcode .content{position: absolute;left: 50%;bottom: 0;transform: translate(50%, -50%);margin-bottom: 17px;color:#fff;font-size: 15px;margin-left: -4.5%;}
    .qrcode .contents{position: absolute;left: 50%;bottom: 0;transform: translate(50%, -50%);margin-bottom: 40px;color:#fff;font-size: 15px;margin-left: -11%;}
    /*big*/
    .down{text-align: center;position: relative;width:2480px;height:3508px;}
    .down img.bg_img{}
    .down .img{position: absolute;left: 50%;margin-left: -410px;top: 50%; margin-top: -50px; width: 820px;}
    .down .img canvas{width: 100%;border-radius: 8px;}
    .down .schoolname{position: absolute;left: 50%;bottom: 19.5%;transform: translate(-50%, -50%);margin-top: 177px; color: #fff;font-size:70px;}
    .down .g_c_name{display:none;width:1000px;height:140px;line-height: 140px;/*background:url("../../layuiadmin/layui/images/banji-bg.png") no-repeat;background-size: 100%;*/ position: absolute;left: 50%;bottom: 13%;transform: translate(-50%, -50%);margin-top: 177px; color: #fff;margin-top: 276px;margin-left: 1%;color:#2aa1f7;font-size:70px;}
    .down .logo{position: absolute;left: 50%;bottom: 0;transform: translate(-155%, -50%);/*width: 120px;*/margin-bottom: -10px;}
    /*.down .logo img{width:100%;}*/
    .down .content{position: absolute;left: 50%;bottom: 0;transform: translate(50%, -50%);width: 600px;margin-bottom: 55px;color:#fff;font-size: 60px;margin-left:-4%;}
    .down .contents{position: absolute;left: 50%;bottom: 0;transform: translate(50%, -50%);margin-bottom: 135px;color:#fff;font-size: 60px;margin-left:-10%;}
    #pic_code img{width:100%;}
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
                <div class="layui-card-header">学校列表</div>

                <div class="layui-card-body">
                  <!-- 搜索 -->
                  <div class="layui-form" lay-filter="component-form-group" style="width:600px;display: inline-block;">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                          <div class="layui-input-inline">
                            <input type="text" name="schoolname" placeholder="请输入名称" autocomplete="off" class="layui-input">
                          </div>
                        </div>                       
                        
                        <div class="layui-inline">
                          <button class="layui-btn layuiadmin-btn-list" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                          </button>
                        </div>
                        
                      </div>
                  </div>
                  <div style="padding-bottom: 10px;">                   
                    <button class="layui-btn layuiadmin-btn-forum-list" data-type="add"><a class="add" lay-href="" style='color:#fff'>新增合并</a></button>
                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                 
                  <script type="text/html" id="imgTpl">
                    <img style="display: inline-block;height: 100%;" src= @{{d.ali_code_url }}>
                  </script>
                  <script type="text/html" id="imgTp2">
                    <img style="display: inline-block;height: 100%;" src= @{{d.wx_code_url }}>
                  </script>
                 

                  <script type="text/html" id="table-content-list">
                    
                    <a class="layui-btn  layui-btn-xs del" lay-event="del">删除</a>
                    
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <input type="hidden" class="user_id">
  <input type="hidden" class="status">

  <div id="open_passway" class="hide" style="height:300px;display: none;background-color: #fff;">
    <div class="layui-card-body" style="padding: 15px;">
      <div class="layui-form">
        <div class="layui-form-item">
          <div class="qrcode" id="qrcode" style="width:500px;margin:30px auto">
              
              <div class="img" id="code"><img src=""></div>
              <div class="hb" style="text-align: center;">合并码链接:<span class="hb_url"></span></div>
              
            </div>
        </div>
        
      </div>
    </div>
  </div>


  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
  <script src="{{asset('/user/js/html2canvas.js')}}"></script> 

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
        });

      


        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/user/qr_code_hb_list')}}"
            ,method: 'post'
            ,where:{
              token:token,
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
                {field:'code_name', title: '合并名称'}
                ,{field:'ali_code_url', title: '支付宝',templet: '#imgTpl'}           
                ,{field:'wx_code_url',  title: '微信',templet: '#imgTp2'}                
                ,{field:'hb_url',  title: '合并码',event: 'setSign', style:'cursor: pointer;'}
                // ,{field:'hb_url',  title: '合并码链接'}
                ,{width:100,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
            ]]
            ,response: {
                statusName: 'status' //数据状态的字段名称，默认：code
                ,statusCode: 1 //成功的状态码，默认：0
                ,msgName: 'message' //状态信息的字段名称，默认：msg
                ,countName: 't' //数据总数的字段名称，默认：count
                ,dataName: 'data' //数据列表的字段名称，默认：data
              } 
            ,done: function(res, curr, count){              
              console.log(res); 
              
            }

        });



        table.on('tool(test-table-page)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
          var e = obj.data; //获得当前行数据
          var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
          var tr = obj.tr; //获得当前行 tr 的DOM对象
          console.log(e);
          localStorage.setItem('stu_order_type_no', e.stu_order_type_no);
          var data = obj.data;

          if(layEvent === 'del'){
            layer.confirm('确认删除此消息?',{icon: 2}, function(index){
              $.post("{{url('/api/user/qr_code_hb_del')}}",
                {
                  token:token,
                  id:e.id
                }, 
                function(res){
                  console.log(res); 
                  if(res.status==1){
                    obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                    layer.close(index);
                    layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 1
                      ,time: 2000
                    });
                    
                  }else{                
                    layer.alert(res.message, {icon: 2});//错误提示
                  }
                },"json");              
              
            });
          }

          
          if(obj.event === 'setSign'){
            console.log(data.hb_url)

            // var protocolStr = document.location.protocol;
            // var str= document.domain;            
            // url=protocolStr+"//"+str;
            // // var a=window.location.href;
            // console.log(url);

            var store_id=$('.js_school').val();
            var stu_grades_no=$('.js_grades').val();
            var stu_class_no=$('.js_class').val();
            $('#pic_code').hide();
            $('#code').show();
              
            $('#code').html('');
            // $('#code').qrcode(url+"/school/trade_pay"+"?store_id="+store_id+"&stu_grades_no="+stu_grades_no+"&stu_class_no="+stu_class_no);
            $('#code').qrcode(data.hb_url+"?id="+data.id);
            $('.hb_url').html(data.hb_url+"?id="+data.id)
            


            var openshua=layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#open_passway')
            });
          }

        });
        $('.add').click(function(){
          $(this).attr('lay-href',"{{url('/user/addpercode')}}");
        })

        
        
        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var value = data.field.schoolname; 
          console.log(value)       
          //执行重载
          table.reload('test-table-page', {
            where: { 
              code_name: value
            }
            ,page: {
              curr: 1
            }
          });
        });



    });


  </script>

</body>
</html>