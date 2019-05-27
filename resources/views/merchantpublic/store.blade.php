<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>门店列表</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .shenhe{background-color: #429488;}    
    .see{background-color: #7cb717;} 
    .tongbu{background-color: #4c9ef8;color:#fff;}
    .cur{color:#009688;}
    .manage{background-color:#6c8ff5;}
    .water{background-color:#5fb878;}
    .branchshop{background-color: #11d0be}
    .storecode{background-color: #00963a;}
    #code{width: 200px;height: 200px;margin: 20px auto;}
    #code canvas{width: 100%;}
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
                <div class="layui-card-header">门店列表</div>

                <div class="layui-card-body">
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <!-- 判断状态 -->
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.status == 1){ }}
                      <span class="cur">@{{ d.status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.status_desc }}
                    @{{#  } }}
                  </script>
                  <!-- 判断状态 -->
                  <!-- 判断是否为总店 -->
                  <script type="text/html" id="storetype">
                    @{{#  if(d.store_type == 1){ }}
                      @{{ d.store_name }}(总店)
                    @{{#  } else { }}
                      @{{ d.store_name }}
                    @{{#  } }}
                  </script>
                  <!-- 判断是否为总店 -->
                  <!-- 入驻地址 -->
                  <script type="text/html" id="address">
                    @{{ d.province_name }}@{{ d.city_name }}@{{ d.area_name }}@{{ d.store_address }}
                  </script>
                  <!-- 入驻地址 -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="see">查看</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs cashier" lay-event="cashier">收银员管理</a>
                    
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>


<div id="edit_rate" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        
        <div id="code">
            
        </div>
        <div style="text-align: center;" class="storename"></div>
      </div>      
    </div>
  </div>
</div>



  <input type="hidden" class="user_id">
  <input type="hidden" class="status">
 
  <input type="hidden" class="provincecode" value="">
  <input type="hidden" class="provincename" value="">
  <input type="hidden" class="citycode" value="">
  <input type="hidden" class="cityname" value="">
  <input type="hidden" class="areacode" value="">
  <input type="hidden" class="areaname" value="">

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
    <script>
    var token = localStorage.getItem("Publictoken");
    layui.config({
      base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index','form','table'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,form = layui.form
            ,table = layui.table;

      // 未登录,跳转登录页面
      $(document).ready(function(){        
          if(token==null){
              window.location.href="{{url('/mb/login')}}"; 
          }
      })        

        // 地区选择
        $.ajax({
          url : "{{url('/api/basequery/city')}}",
          data : {area_code:'1'},
          type : 'get',
          success : function(data) {
              // console.log(data);
              var optionStr = "";
                  for(var i=0;i<data.data.length;i++){
                      optionStr += "<option value='" + data.data[i].area_code + "'>"
                          + data.data[i].area_name + "</option>";
                  }    
                  $("#province").append('<option value="">请选择省</option>'+optionStr);
                  layui.form.render('select');
          },
          error : function(data) {
              alert('查找板块报错');
          }
        });

        // 渲染表格
        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/merchant/store_lists')}}"
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
              // {type:'checkbox', fixed: 'left'}
              {field:'store_id', title: '门店id'}
              ,{field:'store_name', title: '门店名称',templet:'#storetype'}
              ,{field:'store_type_name', title: '入驻类型'}
              ,{field:'stu_class_name',  title: '门店地址',templet:'#address'}
              ,{field:'pay_status_desc', title: '状态',templet:'#statusTap'}              
              ,{field:'people',  title: '联系人'}
              ,{field:'people_phone',  title: '联系电话'}
              ,{field:'created_at',  title: '入驻时间'} 
              ,{width:150,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
          // localStorage.setItem('s_store_id', e.store_id);

          if(layEvent === 'see'){ //审核
            layer.open({
              type: 2,
              title: '详细',
              shade: false,
              maxmin: true,
              area: ['80%', '70%'],
              content: "{{url('/mb/seestore?')}}"+e.store_id
            });
          }else if(layEvent === 'cashier'){
            localStorage.setItem('store_store_name', e.store_name);
            $('.cashier').attr('lay-href',"{{url('/mb/cashier?')}}"+e.store_id);

          }


        });

        // 省市区start-------------------------------
        form.on('select(filterProvince)', function(data){   

            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.provincecode').val(category);
            $('.provincename').val(categoryName);
            $("#city").html('');
            $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:category},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "'>"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#city").append('<option value="">请选择市</option>'+optionStr);
                        layui.form.render('select');
                        
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            //执行重载
            table.reload('test-table-page', {
              where: { 
                province_code:$('.provincecode').val(),
                city_code:$('.citycode').val(),
                area_code:$('.areacode').val()
              }
            }); 

        });

        form.on('select(filterCity)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.citycode').val(category);
            $('.cityname').val(categoryName);
            $("#area").html('');
            $.ajax({
                url : "{{url('/api/basequery/city')}}",
                data : {area_code:category},
                type : 'get',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].area_code + "'>"
                                + data.data[i].area_name + "</option>";
                        }    
                        $("#area").append('<option value="">请选择县/区</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });

            //执行重载
            table.reload('test-table-page', {
              where: { 
                province_code:$('.provincecode').val(),
                city_code:$('.citycode').val(),
                area_code:$('.areacode').val()
              }
            }); 
        });
        form.on('select(filterArea)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.areacode').val(category);
            $('.areaname').val(categoryName);
            //执行重载
            table.reload('test-table-page', {
              where: { 
                province_code:$('.provincecode').val(),
                city_code:$('.citycode').val(),
                area_code:$('.areacode').val()
              }
            });           
        });
        // 省市区end-------------------------------


        
        // 选择审核状态
        form.on('select(status)', function(data){
          var status = data.value;
          $('.status').val(status);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              status:$('.status').val()
            }
          });
        });
       


        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var store_name = data.field.schoolname;    
          console.log(data);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_name:store_name
            }
          });
        });

      

    });

  </script>

</body>
</html>





