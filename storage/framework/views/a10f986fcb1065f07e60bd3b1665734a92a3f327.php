<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>分配教师</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/layui/css/layui.css')); ?>" media="all">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/style/admin.css')); ?>" media="all">
</head>
<body>

  <div class="layui-fluid" style="background-color: #fff;">
    <div class="layui-row">
      <div class="layui-form">

        <div class="layui-form-item">
          <label class="layui-form-label">教师名单</label>
          <div class="layui-input-block teacher_name">
           
          </div>
        </div>




        <div class="layui-form-item layui-layout-admin">
            <div class="layui-input-block">
                <div class="layui-footer" style="left: 0;">
                    <button class="layui-btn submit">关联到班级</button>
                </div>
            </div>
        </div>

      </div>
    </div>
  </div>

  <input type="hidden" class="teacher_type">
  <script src="<?php echo e(asset('/layuiadmin/layui/layui.js')); ?>"></script> 
<script>
  var token = localStorage.getItem("token");
 
  var str=location.search;
  var stu_order_type_no=str.split('?')[1];

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
    
    // 分配教师列表-------------------
    $.post("<?php echo e(url('/api/school/teacher/ter/typelst')); ?>",
    {
      token:token
    }, 
    function(res){
      console.log(res); 
      var html="";
      var arr=[];
      for(var i=0;i<res.data.length;i++){
        html+='<input type="checkbox" name="teacher" lay-filter="teacher" lay-skin="primary" title="'+res.data[i].name+'" value="'+res.data[i].type+'">';

        // arr.push(res.data[i].type);  //学生编号放进数组                          
        // $('.teacher_type').val(arr.join()); //编号用逗号隔开
      }
      $('.teacher_name').html('');
      $('.teacher_name').append(html);
      form.render('checkbox');

    }); 
    // 选择老师--------------------------------------------
    form.on('checkbox(teacher)', function(data){
      var arrs=[];//定义空数组
      $("input:checkbox[name='teacher']:checked").each(function() { // 遍历name=standard选中的多选框的值
          var standards ='';
          standards = $(this).val();          
          arrs.push(standards);
          // console.log(standards);
          
      });
      $('.teacher_type').val(arrs.join());
    });

    
    var mer_id="<?php echo e($_GET['mer_id']); ?>";
    var store_id="<?php echo e($_GET['store_id']); ?>";
    var classno="<?php echo e($_GET['classno']); ?>";


    $('.submit').click(function(){
      $.post("<?php echo e(url('/api/school/teacher/ter/relate')); ?>",
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