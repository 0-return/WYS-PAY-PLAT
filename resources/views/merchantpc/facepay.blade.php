<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>学生列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    /*.qrcode{width:560px;height:820px;background: url("../../layuiadmin/layui/images/bg-katai.png") no-repeat;background-size: 100%;margin:0 auto;}*/
    .qrcode{text-align: center;position: relative;}
    .qrcode img.bg_img{width:595px;height:auto;}
    .qrcode .img{position: absolute;left: 50%;margin-left: -90px;top: 50%; margin-top: -5px;width: 180px;}
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
            <div class="layui-col-md12 no-print" style="background-color: #fff;">
              <div class="layui-card"> 
                <div class="layui-card-header">生成当面付二维码</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="schooltype" id="schooltype" lay-filter="schooltype">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="grade" id="grade" lay-filter="grade">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="class" id="class" lay-filter="class">
                                
                            </select>
                        </div>
                      </div>                      
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:100px;display: inline-block;">                      
                      <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-list shengcheng" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                          生成二维码
                        </button>                        
                      </div>

                    </div>
                    
                </div>
                <div>缴费链接：<span class="href_url"></span></div>

                
                <button class="layui-btn dayin no-print" onClick="Print('#qrcode')">打印</button>
                <a class="download" href="" download="图片"><button class="layui-btn" id="down_load">下载图片</button></a>
              </div>
            </div>
            <div class="qrcode" id="qrcode" style="width:595px;margin:50px auto">
              <img class="bg_img" src="{{asset('/layuiadmin/layui/images/bg-katai.png')}}">
              <div class="img" id="code"><img src=""></div>
              <div class="img" id="pic_code" style="display: none"><img src=""></div>
              <div class="schoolname"></div>
              <div class="g_c_name"><img src="{{asset('/layuiadmin/layui/images/banji-bg.png')}}" class="qr_img" download=""><span></span></div>
              <div class="logo"><img src="{{asset('/layuiadmin/layui/images/xiangyong-logo.png')}}"></div>
              <div class="contents"><span class="store_name"></span>提供技术支持</div>
              <div class="content"><span class="tel"></span></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>


  <div class="down" id="downLoad" style="display: none;background-color: transparent;">
    <img class="bg_img" src="{{asset('/layuiadmin/layui/images/bg-katai.png')}}">
    <div class="img" id="code2"></div>
    <div class="schoolname"></div>
    <div class="g_c_name"><img src="{{asset('/layuiadmin/layui/images/banji-bg.png')}}" class="qr_img" download=""><span></span></div>
    <div class="logo"><img src="{{asset('/layuiadmin/layui/images/xiangyong-logo.png')}}"></div>
    <div class="contents"><span class="store_name"></span>提供技术支持</div>
    <div class="content"><span class="tel"></span></div>
    
  </div>


<div id="canvas_url" style="display: none"></div>
 <input type="hidden" class="js_school">
 <input type="hidden" class="js_grades">
 <input type="hidden" class="js_class">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
  <script src="{{asset('/user/js/html2canvas.js')}}"></script> 
  <script src="{{asset('/user/js/jquery.base64.js')}}"></script> 
  <script src="{{asset('/layuiadmin/Print.js')}}"></script> 
  <!-- <script src="{{asset('/layuiadmin/jquery.jqprint-0.3.js')}}"></script>  -->
    <script>
    var token = localStorage.getItem("token");
    var store_id = localStorage.getItem("store_id");
    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form', 'table'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,table = layui.table
            ,form = layui.form;
        // 公司信息
        
        $.post("{{url('/api/basequery/alipay_isv_info')}}",
        {
          store_id:store_id,                
        },function(res){
            console.log(res);

            if(res.status==1){
              $('.store_name').html(res.data.isv_name)
              $('.tel').html(res.data.isv_phone)
            }else{
                // layer.msg(res.message, {
                //     offset: '15px'
                //     ,icon: 2
                //     ,time: 3000
                // });

            }

        },"json");


        // 选择学校
        $.ajax({
            url : "{{url('/api/school/teacher/lst')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                // console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].store_id + "'>"
                            + data.data[i].school_name + "</option>";
                    }    
                    $("#schooltype").append('<option value="">选择学校</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
      
       

        
        // 选择学校
        form.on('select(schooltype)', function(data){
          console.log(data);
          var school = data.value;
          schoolName = data.elem[data.elem.selectedIndex].text;
          $('.js_school').val(school);
          $('.schoolname').html(schoolName); 

          $("#grade").html('');
          // 选择年级
          $.ajax({
              url : "{{url('/api/school/teacher/grade/lst')}}",
              data : {token:token,store_id:school},
              type : 'post',
              success : function(data) {
                  // console.log(data);
                  var optionStr = "";
                      for(var i=0;i<data.data.length;i++){
                          optionStr += "<option value='" + data.data[i].stu_grades_no + "'>"
                              + data.data[i].stu_grades_name + "</option>";
                      }    
                      $("#grade").append('<option value="">选择年级</option>'+optionStr);
                      layui.form.render('select');
              },
              error : function(data) {
                  alert('查找板块报错');
              }
          });  
               
        });
        // 选择年级
        form.on('select(grade)', function(data){
          var grades = data.value;
          gradesName = data.elem[data.elem.selectedIndex].text;
          $('.js_grades').val(grades);
          $('.g_c_name').show();
          $('.g_c_name span').html(gradesName);
          $("#class").html('');
          // 选择班级
          $.ajax({
              url : "{{url('/api/school/teacher/class/lst')}}",
              data : {token:token,store_id:$('.js_school').val(),stu_grades_no:grades},
              type : 'post',
              success : function(data) {
                  // console.log(data);
                  var optionStr = "";
                      for(var i=0;i<data.data.length;i++){
                          optionStr += "<option value='" + data.data[i].stu_class_no + "'>"
                              + data.data[i].stu_class_name + "</option>";
                      }    
                      $("#class").append('<option value="">选择班级</option>'+optionStr);
                      layui.form.render('select');
              },
              error : function(data) {
                  alert('查找板块报错');
              }
          });
        });
        // 选择班级
        form.on('select(class)', function(data){
          var classs = data.value;
          className = data.elem[data.elem.selectedIndex].text;
          $('.js_class').val(classs);  
          if($('.g_c_name span').html()==''){
            $('.g_c_name span').html(className);
          }else{
            $('.g_c_name span').html($('.g_c_name span').html()+className); 
          }
                 
        });  

      // 生成二维码        

      $('.shengcheng').click(function(){
        var protocolStr = document.location.protocol;
        var str= document.domain;
        
        url=protocolStr+"//"+str;

        // var a=window.location.href;
        console.log(url);
        var store_id=$('.js_school').val();
        var stu_grades_no=$('.js_grades').val();
        var stu_class_no=$('.js_class').val();
        $('#pic_code').hide();
        $('#code').show();
          
        $('#code').html('');
        $('#code').qrcode(url+"/school/trade_pay"+"?store_id="+store_id+"&stu_grades_no="+stu_grades_no+"&stu_class_no="+stu_class_no);
        $('#code2').qrcode(url+"/school/trade_pay"+"?store_id="+store_id+"&stu_grades_no="+stu_grades_no+"&stu_class_no="+stu_class_no);
        $('.href_url').html(url+"/school/trade_pay"+"?store_id="+store_id+"&stu_grades_no="+stu_grades_no+"&stu_class_no="+stu_class_no);
        

        dayin();

      })
      // 打印图片
      function dayin(){
        var canvas =$("canvas")[0];
        var context = canvas.getContext('2d'); 
        var strDataURI=canvas.toDataURL("image/png");     
        // console.log(strDataURI);
        $('#code').hide();
        $('#pic_code').show();
        $('#pic_code img').attr('src',strDataURI)
        


        html2canvas(document.querySelector("#qrcode")).then(canvas => {
          // document.body.appendChild(canvas)
          $('#canvas_url').html(canvas)
        });//将div模块转换为canvas

        
      }

      $('#down_load').click(function(){
        var canvas2 =$("canvas")[2];//获取生成的canvas元素
        // console.log(canvas2);
        var context2 = canvas2.getContext('2d'); 
        var strDataURI2=canvas2.toDataURL("image/png");
        // console.log(strDataURI2);
        $('.download').attr('href',strDataURI2)  /*下载图片给a标签href属性赋值*/
      })
      

      



     



       
    });

  </script>

</body>
</html>