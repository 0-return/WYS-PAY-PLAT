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
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .cur{color:#009688;}
    .manage{background-color:##6c8ff5;}
    .water{background-color:#5fb878;}
    /*.laytable-cell-1-school_icon{height:100%;}*/
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
                    <a class="layui-btn layui-btn-primary addschool" lay-href="{{url('/merchantpc/addschool')}}">添加学校</a>
                  </div>
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.status == 1){ }}
                      <span class="cur">@{{ d.status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.status_desc }}
                    @{{#  } }}
                  </script>
                  <script type="text/html" id="alipaystatus">
                    @{{#  if(d.alipay_status == 1){ }}
                      <span class="cur">@{{ d.alipay_status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.alipay_status_desc }}
                    @{{#  } }}
                  </script>
                  <script type="text/html" id="imgTpl">
                    <img style="display: inline-block;height: 100%;" src= @{{d.school_icon }}>
                  </script>

                  <script type="text/html" id="table-content-list">
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs shouquan" lay-event="shouquan" lay-href="">支付宝授权</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs tongbu" lay-event="tongbu">同步支付宝</a>

                    <a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-event="edit" lay-href="">学校修改</a>
                    <a class="layui-btn  layui-btn-xs see" lay-event="see">查看</a>
                    <a class="layui-btn  layui-btn-xs branch" lay-event="branch">分校管理</a>
                    
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
<!-- 操作按钮 -->
<!-- 
                    
<a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="detail">查看</a> -->
  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
    <script>
    var token = localStorage.getItem("token");
    
    

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
            ,url: "{{url('/api/school/teacher/lst')}}"
            ,method: 'post'
            ,where:{
              token:token,
              search_son:'1'
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
                {field:'school_icon', title: 'logo',templet: '#imgTpl'}
                ,{field:'school_name', title: '分校名称'}           
                ,{field:'status',  title: '系统状态',templet: '#statusTap'}                
                ,{field:'alipay_status',  title: '支付宝状态',templet: '#alipaystatus'}
                ,{width:400,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
          

          if(layEvent === 'tongbu'){//同步+++++
            $.post("{{url('/api/school/teacher/sync')}}",
            {
              token:token,
              store_id:e.store_id
            }, 
            function(res){
              console.log(res); 
              if(res.status==1){
                layer.msg(res.message, {
                  offset: '15px'
                  ,icon: 1
                  ,time: 3000
                });
                
              }else{                
                layer.alert(res.message, {icon: 2});//错误提示
              }
            });
          }else if(layEvent === 'shouquan'){//授权+++++
            $('.shouquan').attr('lay-href',"{{url('/merchantpc/alipayauth?')}}"+e.store_id)
            
          }else if(layEvent === 'see'){//查看+++++
            $.post("{{url('/api/school/teacher/show')}}",
            {
              token:token,
              store_id:e.store_id
            }, 
            function(res){
              console.log(res);
              var schooltype=res.data.school_type;
              // 学校类型
              $.post("{{url('/api/school/teacher/typelst')}}",
              {
                token:token
              },function(data){
                // console.log(data);

                // 教师详情
                $.post("{{url('/api/school/teacher/show')}}",
                {
                  token:token,
                  store_id:e.store_id
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

          }else if(layEvent === 'edit'){
            
            $('.edit').attr('lay-href',"{{url('/merchantpc/editschool?')}}"+e.store_id)

          }else if(layEvent === 'branch'){
            localStorage.setItem('parsent_store_id', e.store_id);
            $('.branch').attr('lay-href',"{{url('/merchantpc/branchschool?store_id=')}}"+e.store_id+"&school_name="+e.school_name)
          }

        });

        
        // 选择学校
        form.on('select(schooltype)', function(data){
          var user_id = data.value;
          $('.user_id').val(user_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              user_id: user_id,
              status:$('.status').val()
            }
          });
        });
        
        // 选择状态
        form.on('select(class)', function(data){
          var status = data.value;
          $('.status').val(status);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              status: status,
              user_id:$('.user_id').val()
            }
          });
        });
        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var value = data.field.id;        
          //执行重载
          table.reload('test-table-page', {
            where: { 
              school_name: value
            }
          });
        });



    });


  </script>

</body>
</html>