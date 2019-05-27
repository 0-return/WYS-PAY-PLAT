<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>分店</title>
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
    .manage{background-color:#6c8ff5;}
    .water{background-color:#5fb878;}
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
                <div class="layui-card-header">总店:<span></span></div>
                <div class="layui-card-body">
                  <div class="layui-btn-container">                    
                    <a class="layui-btn layui-btn-primary addschool" lay-href="">添加分店</a>
                  </div>

                  <div style="padding-bottom: 10px;">                    
                    <button class="layui-btn layuiadmin-btn-forum-list del" data-type="batchdel">删除</button>
                    <button class="layui-btn layuiadmin-btn-forum-list close" data-type="batchdelclose">关闭</button>
                  </div>

                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.status == 1){ }}
                      <span class="cur">@{{ d.status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.status_desc }}
                    @{{#  } }}
                  </script>
                  
                  <!-- 入驻地址 -->
                  <script type="text/html" id="address">
                    @{{ d.province_name }}@{{ d.city_name }}@{{ d.area_name }}@{{ d.store_address }}
                  </script>
                  <!-- 入驻地址 -->

                  <script type="text/html" id="table-content-list">
                    <a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="see">查看</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs openbtn" lay-event="openbtn">门店管理</a>
                    <a class="layui-btn  layui-btn-xs order" lay-event="order" lay-href="">交易流水</a>
                    <a class="layui-btn  layui-btn-xs storecode" lay-event="storecode">门店收款码</a>
                    <a class="layui-btn  layui-btn-xs shenhe" lay-event="shenhe">审核操作</a>
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

<div id="edit_shenhe" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item" pane="">
        <label class="layui-form-label">审核状态</label>
        <div class="layui-input-block pass">
          <input type="radio" name="sex" value="1" title="通过" checked="">
          <input type="radio" name="sex" value="3" title="不通过">
        </div>        
      </div>     
    </div>
    <div class="layui-form">
      <div class="layui-form-item" pane="">
        <label class="layui-form-label">审核说明</label>
        <div class="layui-input-block">
          <textarea name="desc" placeholder="请输入内容" class="layui-textarea textarea"></textarea>
        </div>        
      </div>     
    </div>
    <div class="layui-form-item">
      <div class="layui-input-block">
          <div class="layui-footer" style="left: 0;">
              <button class="layui-btn closestore">确定</button>
          </div>
      </div>
    </div>
    <input type="hidden" class="shenhe_store">
  </div>
</div>

<div id="open_button" class="hide" style="display: none;background-color: #fff;">
  <div class="layui-card-body" style="padding: 15px;">
    <div class="layui-form">
      <div class="layui-form-item">
        <div style="padding-bottom:14px;"><label>门店名称:</label><span class="store_name_total"></span></div>

        <a class="layui-btn layui-btn-normal layui-btn-xs device" lay-event="device" lay-href="{{url('/user/devicelist')}}">设备管理</a>
        <a class="layui-btn layui-btn-normal layui-btn-xs passway" lay-event="passway">通道管理</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs edit" lay-event="edit" lay-href="{{url('/user/editstore')}}">门店修改</a>
        <a class="layui-btn  layui-btn-xs shouyin" lay-event="shouyin">收银插件</a>
        <a class="layui-btn  layui-btn-xs merchantnumber" lay-event="merchantnumber" lay-href="">新大陆D0管理</a>
        
      </div>      
    </div>
  </div>
</div>


  <input type="hidden" class="js_store_id">
  <input type="hidden" class="js_id">
  <input type="hidden" class="js_store_name">



  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
    <script>
    var token = localStorage.getItem("Usertoken");
    var id="{{$_GET['id']}}";
    var store_name="{{$_GET['store_name']}}";
    

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

        
        $('.layui-card-header span').html(store_name);
        $('.addschool').attr('lay-href',"{{url('/user/addbranchdevice?pid=')}}"+id);

        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/user/store_lists')}}"
            ,method: 'post'
            ,where:{
              token:token,
              pid:id
            }
            ,request:{
              pageName: 'p', 
              limitName: 'l'
            }
            ,page: true
            ,cellMinWidth: 150
            ,cols: [[
              {type:'checkbox', fixed: 'left'}
              ,{field:'store_id', title: '门店id'}
              ,{field:'store_name', title: '门店名称'}
              ,{field:'user_name', title: '归属'}
              ,{field:'store_type_name', title: '入驻类型'}
              ,{field:'stu_class_name',  title: '门店地址',templet:'#address'}
              ,{field:'pay_status_desc', title: '状态',templet:'#statusTap'}              
              ,{field:'people',  title: '联系人'}
              ,{field:'people_phone',  title: '联系电话'}
              ,{field:'created_at',  title: '入驻时间'} 
              ,{width:370,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
              area: ['90%', '90%'],
              content: "{{url('/user/seestore?')}}"+e.store_id
            });
          }else if(layEvent === 'openbtn'){
            localStorage.setItem('store_store_id', e.store_id);
            localStorage.setItem('store_store_name', e.store_name);
            $('.js_store_id').val(e.store_id)
            $('.js_id').val(e.id)
            $('.js_store_name').val(e.store_name)
            $('.store_name_total').html(e.store_name)


            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#open_button')
            });
          }else if(layEvent === 'edit'){
            localStorage.setItem('store_store_id', e.store_id);

            localStorage.setItem('store_province_code', e.province_code);
            localStorage.setItem('store_city_code', e.city_code);
            localStorage.setItem('store_area_code', e.area_code);
            
          }else if(layEvent === 'device'){
            localStorage.setItem('store_store_id', e.store_id);
            localStorage.setItem('store_store_name', e.store_name);

          }else if(layEvent === 'order'){

            $('.order').attr('lay-href',"{{url('/user/tradelist?')}}"+e.store_id);

          }else if(layEvent === 'passway'){
            localStorage.setItem('store_store_name', e.store_name);
            $('.passway').attr('lay-href',"{{url('/user/passway?')}}"+e.store_id);
          }else if(layEvent === 'branchshop'){
            $('.branchshop').attr('lay-href',"{{url('/user/branchshop?id=')}}"+e.id+"&store_name="+e.store_name);

          }else if(layEvent === 'storecode'){
            $('.storename').html(e.store_name);
            $.post("{{url('/api/user/store_pay_qr')}}",
            {
                token:token,
                store_id:e.store_id
            },
            function(res){
                console.log(res);                
                if(res.status==1){

                  $('#code').html('');
                  $('#code').qrcode(res.data.store_pay_qr);
                  layer.open({
                    type: 1,
                    title: false,
                    closeBtn: 0,
                    area: '516px',
                    skin: 'layui-layer-nobg', //没有背景色
                    shadeClose: true,
                    content: $('#edit_rate')
                  }); 
                }else if(res.status==2){
                  layer.msg(res.message, {
                      offset: '15px'
                      ,icon: 2
                      ,time: 3000
                  });
                }
                
            },"json");            
          }else if(layEvent === 'shenhe'){
            $('.shenhe_store').val(e.store_id)
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#edit_shenhe')
            });
          }


        });


        // 新添加的弹框按钮2019.1.14

        $('.passway').click(function(){          
          $(this).attr('lay-href',"{{url('/user/passway?')}}"+$('.js_store_id').val());
        });
        $('.branchshop').click(function(){
          $(this).attr('lay-href',"{{url('/user/branchshop?id=')}}"+$('.js_id').val()+"&store_name="+$('.js_store_name').val());
        });
        $('.shouyin').click(function(){
          $('.shouyin').attr('lay-href',"{{url('/user/shouyin?store_id=')}}"+$('.js_store_id').val()+"&store_name="+$('.js_store_name').val());
        });
        $('.merchantnumber').click(function(){
          $(this).attr('lay-href',"{{url('/user/merchantnumber?')}}"+$('.js_store_id').val());
        })
        // *********end

        
        $('.closestore').click(function(){
        
          $("input:radio[name='sex']:checked").each(function() { // 遍历name=standard选中的多选框的值
                
            console.log($(this).val());
            $.post("{{url('/api/user/check_store')}}",
            {
                token:token,
                store_id:$('.shenhe_store').val(),
                status:$(this).val(),
                status_desc:$('.textarea').val()                 

            },function(res){
                console.log(res);
                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 2000
                    },function(){
                        window.location.reload();
                      });
                }else{
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                    });
                }
            },"json");
              
          });
        })


        var arrs=[]
        // 删除
        var active = {
          batchdel: function(){
            var checkStatus = table.checkStatus('test-table-page')
            ,checkData = checkStatus.data; //得到选中的数据
            console.log(checkData);

            for(var i=0;i<checkData.length;i++){ 
              arrs.push(checkData[i].store_id);
            }
            var store_id=arrs.join()
            console.log(arrs.join())

            if(checkData.length === 0){
              return layer.msg('请选择门店');
            }
          
            layer.confirm('确定删除吗？', function(index) {   

              $.post("{{url('/api/user/del_store')}}",
              {
                  token:token,
                  store_id:arrs.join()                    

              },function(res){
                  console.log(res);
                  if(res.status==1){
                      layer.msg(res.message, {
                          offset: '15px'
                          ,icon: 1
                          ,time: 1000
                      },function(){
                        window.location.reload();
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
          }
        }
        // 关闭
        var activet = {
          batchdelclose: function(){
            var checkStatus = table.checkStatus('test-table-page')
            ,checkData = checkStatus.data; //得到选中的数据
            console.log(checkData);

            for(var i=0;i<checkData.length;i++){ 
              arrs.push(checkData[i].store_id);
            }
            var store_id=arrs.join()
            console.log(arrs.join())

            if(checkData.length === 0){
              return layer.msg('请选择门店');
            }
          
            layer.confirm('确定关闭门店吗？', function(index) {   

              $.post("{{url('/api/user/col_store')}}",
              {
                  token:token,
                  store_id:arrs.join()                    

              },function(res){
                  console.log(res);
                  if(res.status==1){
                      layer.msg(res.message, {
                          offset: '15px'
                          ,icon: 1
                          ,time: 1000
                      },function(){
                        window.location.reload();
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
          }
        }

        $('.layui-btn.layuiadmin-btn-forum-list').on('click', function(){
          var type = $(this).data('type');
          console.log(type)
          active[type] ? active[type].call(this) : '';
          activet[type] ? activet[type].call(this) : '';
        });



    });


  </script>

</body>
</html>