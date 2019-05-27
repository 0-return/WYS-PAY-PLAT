<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>学生列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/layui/css/layui.css')); ?>" media="all">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/style/admin.css')); ?>" media="all">
  <style>
    /*.qrcode{width:560px;height:820px;background: url("../../layuiadmin/layui/images/bg-katai.png") no-repeat;background-size: 100%;margin:0 auto;}*/
    .qrcode{text-align: center;padding:50px 0;position: relative;}
    .qrcode img.bg_img{width:450px;height:auto;}
    .img{position: absolute;left: 50%;margin-left: -71px;top: 50%;margin-top: -6px;width: 142px;}
    .img canvas{width: 100%;border-radius: 8px;}
    .schoolname{position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);margin-top: 177px; color: #fff;}
    .g_c_name{display:none;width:184px;height:140px;background:url("../../layuiadmin/layui/images/banji-bg.png") no-repeat;background-size: 100%; position: absolute;left: 50%;top: 50%;transform: translate(-50%, -50%);margin-top: 177px; color: #fff;margin-top: 276px;margin-left: .5%;color:#2aa1f7;}
    .logo{position: absolute;left: 50%;bottom: 0;transform: translate(-140%, -50%);width: 120px;margin-bottom: 38px;}
    .logo img{width:100%;}
    .content{position: absolute;left: 50%;bottom: 0;transform: translate(40%, -50%);width: 120px;margin-bottom: 50px;color:#fff;font-size: 12px;}
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
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">                      
                      <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-list shengcheng" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                          生成二维码
                        </button>
                      </div>
                    </div>
                </div>
                <div class="qrcode">
                  <img class="bg_img" src="<?php echo e(asset('/layuiadmin/layui/images/bg-katai.png')); ?>">
                  <div class="img" id="code"></div>
                  <div class="schoolname"></div>
                  <div class="g_c_name"></div>
                  <div class="logo"><img src="<?php echo e(asset('/layuiadmin/layui/images/xiangyong-logo.png')); ?>"></div>
                  <div class="content">联系电话:<span class="tel">4008500508</span></div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
 <input type="hidden" class="js_school">
 <input type="hidden" class="js_grades">
 <input type="hidden" class="js_class">

  <script src="<?php echo e(asset('/layuiadmin/layui/layui.js')); ?>"></script> 
  <script src="<?php echo e(asset('/layuiadmin/layui/jquery-2.1.4.js')); ?>"></script>
  <script src="<?php echo e(asset('/layuiadmin/layui/jquery.qrcode.min.js')); ?>"></script>
    <script>
    var token = localStorage.getItem("token");
    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form', 'table'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,table = layui.table
            ,form = layui.form;

        // 选择学校
        $.ajax({
            url : "<?php echo e(url('/api/school/teacher/lst')); ?>",
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

          $('#code').html('');
          $('#code').qrcode(url+"/school/trade_pay"+"?&store_id="+store_id+"&stu_grades_no="+stu_grades_no+"&stu_class_no="+stu_class_no);
        })



        

        
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
              url : "<?php echo e(url('/api/school/teacher/grade/lst')); ?>",
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
          $('.g_c_name').html(gradesName);
          $("#class").html('');
          // 选择班级
          $.ajax({
              url : "<?php echo e(url('/api/school/teacher/class/lst')); ?>",
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
          if($('.g_c_name').html()==''){
            $('.g_c_name').html(className);
          }else{
            $('.g_c_name').html($('.g_c_name').html()+className); 
          }
                 
        });        
    });

  </script>

</body>
</html>