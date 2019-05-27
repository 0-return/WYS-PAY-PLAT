

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>学校管理</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/layui/css/layui.css')); ?>" media="all">
  <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/style/admin.css')); ?>" media="all">
  <style>
    .layui-form-item{margin-bottom:0;}
    .layui-word-aux{color:#666 !important;}
    .shenhe{background-color: #429488;}
    .tongbu{background-color: #4c9ef8;color:#fff;}
    .see{background-color: #7cb717;}
    .edit{background-color: #ed9c3a;}
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
                  <div class="layui-btn-container">                    
                    <a class="layui-btn layui-btn-primary" lay-href="<?php echo e(url('/merchantpc/addschool')); ?>">添加学校</a>
                  </div>
                  <table class="layui-table">
                    <colgroup>
                      <col width="150">
                      <col width="150">
                      <col width="200">
                      <col>
                    </colgroup>
                    <thead>
                      <tr>
                        <th>logo</th>
                        <th>学校名称</th>                        
                        <th>系统状态</th>
                        <th>支付宝状态</th>
                        <th>操作</th>
                      </tr> 
                    </thead>
                    <tbody>
                                            
                    </tbody>
                  </table>
                </div>                

                <div class="layui-col-md12">
                  <div class="layui-card">
                    <div class="layui-card-body">
                      <div id="test-laypage-demo7"></div>
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
  

  <script src="<?php echo e(asset('/layuiadmin/layui/layui.js')); ?>"></script> 
  <script src="<?php echo e(asset('/layuiadmin/layui/jquery-2.1.4.js')); ?>"></script>
  <script src="<?php echo e(asset('/layuiadmin/layui/jquery.qrcode.min.js')); ?>"></script>
  <script>
    layui.config({
      base: '../../../layuiadmin/' //静态资源所在路径
    }).extend({
      index: 'lib/index' //主入口模块
    }).use(['index', 'laypage','form'], function(){
      var $ = layui.$
          ,admin = layui.admin
          ,table = layui.table
          ,element = layui.element
          ,layer = layui.layer
          ,form = layui.form;
      var laypage = layui.laypage;
      var token = localStorage.getItem("token");

      form.render(null, 'component-form-element');
    element.render('breadcrumb', 'breadcrumb');
    
    form.on('submit(component-form-element)', function(data){
      layer.msg(JSON.stringify(data.field));
      return false;
    });



      /* 触发弹层 添加学校 */
      var active = {
          test47: function(){
              var index = layer.open({
                  type: 2,
                  content: "<?php echo e(url('/merchantpc/addschool')); ?>",
                  area: ['300px', '300px'],
                  maxmin: true
              });
              layer.full(index);
          }

      };
      $('.layui-btn').on('click', function(){
          var type = $(this).data('type');
          active[type] && active[type].call(this);
      });


      $(document).ready(function(){
        $.post("<?php echo e(url('/api/school/teacher/lst')); ?>",
        {
          token:token,
          p:'1',
          l:'10'
        },function(res){
          var count=res.t;   

          //完整功能
          laypage.render({
            elem: 'test-laypage-demo7'
            ,url: "<?php echo e(url('/api/school/teacher/lst')); ?>"
            ,method: 'post'
            ,where: {
              token:token
            } 
            ,count: count
            ,layout: ['count', 'prev', 'page', 'next', 'limit', 'skip']
            ,jump: function(obj){

              $.post("<?php echo e(url('/api/school/teacher/lst')); ?>",
              {
                token:token,
                p:obj.curr,
                l:'10'
              },function(res){
                console.log(res);
                var html='';
                for(var i=0;i<res.data.length;i++){
                  html+='<tr>';
                  html+='<td><img style="display: inline-block; width: 100%; height: auto;" src="'+res.data[i].school_icon+'"></td>';
                  html+='<td>'+res.data[i].school_name+'</td>'; 
                  if(res.data[i].status==1){
                    html+='<td style="color:#009688">成功</td>';
                  }else if(res.data[i].status==2){
                    html+='<td>审核中</td>';
                  }else if(res.data[i].status==3){
                    html+='<td>审核失败</td>';
                  }else{
                    html+='<td>关闭</td>';
                  }
                    
                  if(res.data[i].alipay_status==1){
                    html+='<td style="color:#009688">已同步</td>';
                  }else{
                    html+='<td>未同步</td>';
                  }
                  
                  html+='<td><a class="layui-btn layui-btn-normal layui-btn-xs shouquan" lay-href="<?php echo e(url('/merchantpc/alipayauth')); ?>?'+res.data[i].store_id+'">支付宝授权</a><button class="layui-btn layui-btn-primary layui-btn-xs tongbu" data-id="'+res.data[i].store_id+'">同步支付宝</button><a class="layui-btn layui-btn-xs see" lay-event="detail" data-id="'+res.data[i].store_id+'">查看</a><a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-href="<?php echo e(url('/merchantpc/editschool')); ?>?'+res.data[i].store_id+'">学校修改</a></td>';
                  
                  html+='</tr>';
                }
                $('tbody').html('');
                $('tbody').append(html);
              },"json");            
            }
          });

        },"json");
      });
      // 审核
      $("tbody").on('click','tr td .shenhe', function(){
        var store_id=$(this).attr('data-id');
        layer.open({
          type: 2,
          title: '审核',
          shade: false,
          maxmin: true,
          area: ['60%', '50%'],
          content: "<?php echo e(url('/merchantpc/examineschool?')); ?>"+store_id
        });
        
      });
     
      // 查看
      $("tbody").on('click','tr td .see', function(){
        var store_id=$(this).attr('data-id');
        // 学校类型
        $.post("<?php echo e(url('/api/school/teacher/typelst')); ?>",
        {
          token:token
        },function(data){
          // console.log(data);

          // 教师详情
          $.post("<?php echo e(url('/api/school/teacher/show')); ?>",
          {
            token:token,
            store_id:store_id
          }, 
          function(res){
            // console.log(res);
            var schooltype=res.data.school_type;

            for(var i=0;i<data.data.length;i++){
              if(schooltype==data.data[i].type){
                var typename=data.data[i].name;
                
                
              }
            } 

            // console.log(typename); 
            layer.open({ 
              type: 1,
              area: ['600px', '360px'],
              shadeClose: true, //点击遮罩关闭              
              content: '<div class="layui-form">'
                +'<div class="layui-form-item">'
                  +'<div class="layui-inline">'
                    +'<label class="layui-form-label">学校名称</label>'
                    +'<div class="layui-form-mid layui-word-aux">'+res.data.school_name+'</div>'
                  +'</div>'
                +'</div>'
                +'<div class="layui-form-item">'
                  +'<div class="layui-inline">'
                    +'<label class="layui-form-label">学校简称</label>'
                    +'<div class="layui-form-mid layui-word-aux">'+res.data.school_sort_name+'</div>'
                  +'</div>'
                +'</div>'
                +'<div class="layui-form-item">'
                  +'<div class="layui-inline">'
                    +'<label class="layui-form-label">学校类型</label>'
                    +'<div class="layui-form-mid layui-word-aux">'+typename+'</div>'
                  +'</div>'
                +'</div>'
                +'<div class="layui-form-item">'
                  +'<div class="layui-inline">'
                    +'<label class="layui-form-label">学校logo</label>'
                    +'<div class="layui-form-mid layui-word-aux" style="width:100px;height:100px;"><img style="width:100%" src="'+res.data.school_icon+'"></div>'
                  +'</div>'
                +'</div>'
                +'<div class="layui-form-item">'
                  +'<div class="layui-inline">'
                    +'<label class="layui-form-label">学校(机构)标识码</label>'
                    +'<div class="layui-form-mid layui-word-aux">'+res.data.school_stdcode+'</div>'
                  +'</div>'
                +'</div>'
                +'<div class="layui-form-item">'
                  +'<div class="layui-inline">'
                    +'<label class="layui-form-label">学校平台ID</label>'
                    +'<div class="layui-form-mid layui-word-aux">'+res.data.store_id+'</div>'
                  +'</div>'
                +'</div>'
                +'<div class="layui-form-item">'
                  +'<div class="layui-inline">'
                    +'<label class="layui-form-label">学校支付宝ID</label>'
                    +'<div class="layui-form-mid layui-word-aux">'+res.data.school_no+'</div>'
                  +'</div>'
                +'</div>'
                +'<div class="layui-form-item">'
                  +'<div class="layui-inline">'
                    +'<label class="layui-form-label">地址</label>'
                    +'<div class="layui-form-mid layui-word-aux">'+res.data.province_name+res.data.city_name+res.data.district_name+res.data.su_store_address+'</div>'
                  +'</div>'
                +'</div>'
                +'</div>'

            });
              
            
          });




                     
        },"json");

      });
      // 同步
      $("tbody").on('click','tr td .tongbu', function(){
        var store_id=$(this).attr('data-id');

        $.post("<?php echo e(url('/api/school/teacher/sync')); ?>",
        {
          token:token,
          store_id:store_id
        }, 
        function(res){
          console.log(res);
          if(res.status==1){
            layer.msg(res.message, {
              offset: '15px'
              ,icon: 1
              ,time: 1000
            },function(){              
              window.parent.location.reload();
            });

          }else{
            layer.alert(res.message, {icon: 2});            
          }          
        });
      });

           
    });
  </script>

</body>
</html>