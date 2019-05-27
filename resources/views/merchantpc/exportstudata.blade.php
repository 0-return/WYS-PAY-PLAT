<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>excel导入学生资料</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
</head>
<body>

  <div class="layui-fluid" style="background-color: #fff;">
    <div class="layui-row">
      <div class="layui-form">
        <a href="{{url('/student-add.xlsx')}}">模板下载</a>
        <div class="layui-form-item" style="display: inline-block;">
          <form id= "uploadForm" style="display: inline-block;padding-left:50px;">            
            <div class="layui-btn name" style="margin-right:20px;">选择所需上传文件</div>                  
            <input type="file" name="file" id="file" style="display: none;"/> 
            <input type="button" value="确定上传" id="Upload" class="layui-btn"/>  
          </form>
        </div>




        <!-- <div class="layui-form-item layui-layout-admin">
            <div class="layui-input-block">
                <div class="layui-footer" style="left: 0;">
                    <button class="layui-btn submit">确定导入</button>
                </div>
            </div>
        </div> -->

      </div>
    </div>
  </div>

  <input type="hidden" class="teacher_type">
  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
  var token = localStorage.getItem("token");
 
  var str=location.search;
  var stu_class_no=str.split('?')[1];

  layui.config({
    base: '../../../layuiadmin/' //静态资源所在路径
  }).extend({
    index: 'lib/index' //主入口模块
  }).use(['index', 'form','table'], function(){
    var $ = layui.$
    ,admin = layui.admin
    ,element = layui.element
    ,table = layui.table
    ,form = layui.form;
    
    $('.name').click(function(){
      $("#file").click();
    })
    // 获取文件名
    var file = $('#file');
    file.on('change', function( e ){
        //e.currentTarget.files 是一个数组，如果支持多个文件，则需要遍历
        var name = e.currentTarget.files[0].name;
        console.log( name );
        $('.name').html(name);
    });
    // excel文件导入
    $('#Upload').click(function(){
      var formData = new FormData($( "#uploadForm" )[0]);  
      console.log(formData);
      $.ajax({   
        url: "{{url('/api/school/teacher/stu/import?token=')}}"+token+"&stu_class_no="+stu_class_no,  
        type: 'POST',  
        data: formData,  
        async: false,  
        cache: false,  
        contentType: false,  
        processData: false,  
        success: function (res) {  
          console.log(res);
          
          if(status==1){
            layer.msg(res.message, {
              offset: '15px'
              ,icon: 1
              ,time: 3000
            });
          }else{
            layer.alert(res.message, {icon: 2});
          }
         
        },  
        error: function (res) {  
          layer.msg(res.message, {
            offset: '15px'
            ,icon: 2
            ,time: 3000
          });
            
        }  
     });

    });


    $('.submit').click(function(){
      $.post("{{url('/api/school/teacher/ter/relate')}}",
      {
        token:token,
        store_id:store_id,
        merchant_id:mer_id,
        stu_class_no:classno,
        type:$('.teacher_type').val()
      }, 
      function(res){
        console.log(res); 
        if(res.status==1){
          layer.msg(res.message, {
            offset: '15px'
            ,icon: 1
            ,time: 1000
          });
          
        }else{
          layer.msg(res.message, {
            offset: '15px'
            ,icon: 2
            ,time: 1000
          });
        }
      });      
    })

  });
  </script>
</body>
</html>