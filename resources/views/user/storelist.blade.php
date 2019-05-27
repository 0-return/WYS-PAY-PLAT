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
    .box .layui-btn{margin-bottom:10px;}
    .merchantmanage{background-color: #11d0be}
    .fuyoumanage{background-color: #9ad011}

    .userbox{
      height:200px;
      overflow-y: auto;
      z-index: 999;
      position: absolute;
      left: 0px;
      top: 42px;
      width:298px;
      background-color:#ffffff;
      border: 1px solid #ddd;
    }
    .userbox .list{
      height:38px;line-height: 38px;cursor:pointer;
      padding-left:10px;
    }
    .userbox .list:hover{
      background-color:#eeeeee;
    }
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
                  <div class="layui-btn-container" style="font-size:14px;">

                    <!-- 省 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="province" lay-filter="filterProvince" id="province">
                                    
                            </select>
                            
                        </div>
                    </div>
                    </div>
                    <!-- 市 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="city" lay-filter="filterCity" id="city">
                                    
                            </select>
                        </div>
                      </div>
                    </div>
                    <!-- 区 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="area" lay-filter="filterArea" id="area">
                                    
                            </select>
                        </div>
                      </div>
                    </div>
                    
                    <!-- 选择业务员 -->
                    <!-- <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="agent" id="agent" lay-filter="agent">
                                
                            </select>
                        </div>
                      </div>
                    </div> -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                          <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入业务员名称" class="layui-input transfer">

                          <div class="userbox" style='display: none'></div>
                        </div>
                      </div>
                    </div>
                    <!-- 审核状态 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="status" id="status" lay-filter="status">
                              <option value="">审核状态</option>
                              <option value="1">审核成功</option>
                              <option value="2">未审核</option>
                              <option value="3">审核失败</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="open" id="open" lay-filter="open">
                              <option value="0">未关闭</option>
                              <option value="1">已关闭</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:300px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="del" id="del" lay-filter="del">
                              <option value="0">未删除</option>
                              <option value="1">已删除</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    
                    
                    
                    <!-- 搜索 -->
                    <div class="layui-form" lay-filter="component-form-group" style="width:600px;display: inline-block;">
                      <div class="layui-form-item">
                          <div class="layui-inline">
                            <div class="layui-input-inline">
                              <input type="text" name="schoolname" placeholder="请输入门店名称或者门店ID" autocomplete="off" class="layui-input">
                            </div>
                          </div>                       
                          
                          <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-list" lay-submit="" lay-filter="LAY-app-contlist-search" style="margin-bottom: 0;height:36px;line-height: 36px;">
                              <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                          </div>
                          <button class="layui-btn" style="margin-bottom: 4px;height:36px;line-height: 36px;">导出</button>
                        </div>
                    </div>

                  </div>
                  

                  <div style="padding-bottom: 10px;">                    
                    <button class="layui-btn layuiadmin-btn-forum-list del" data-type="batchdel">删除</button>
                    <button class="layui-btn layuiadmin-btn-forum-list recover" data-type="recover">恢复</button>

                    <button class="layui-btn layuiadmin-btn-forum-list close" data-type="batchdelclose">关闭</button>
                    <button class="layui-btn layuiadmin-btn-forum-list open" data-type="batchdelopen">开启</button>
                    <button class="layui-btn layuiadmin-btn-forum-list" data-type="transfer"><a class="addstoretransfer" lay-href="" style='color:#fff'>门店转移</a></button>
                    <button class="layui-btn layuiadmin-btn-forum-list" data-type="addstore"><a class="addstore" lay-href="" style='color:#fff'>添加门店</a></button>
                    

                  </div>
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
                  <!-- 入驻地址 -->
                  <script type="text/html" id="address">
                    @{{ d.province_name }}@{{ d.city_name }}@{{ d.area_name }}@{{ d.store_address }}
                  </script>
                  <!-- 入驻地址 -->
                  <script type="text/html" id="table-content-list" class="layui-btn-small">
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="see">查看</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs openbtn" lay-event="openbtn">门店管理</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs passway" lay-event="passway">通道管理</a>                    
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
      <div class="layui-form-item box">
        <div style="padding-bottom:14px;">
          <label>门店名称:</label><span class="store_name_total"></span>
        </div>

          <a class="layui-btn layui-btn-normal layui-btn-xs device" lay-event="device" lay-href="{{url('/user/devicelist')}}">设备管理</a>
          
          <a class="layui-btn layui-btn-danger layui-btn-xs edit" lay-event="edit" lay-href="{{url('/user/editstore')}}">门店修改</a>
          <a class="layui-btn  layui-btn-xs branchshop" lay-event="branchshop" lay-href="">分店管理</a>
          <a class="layui-btn  layui-btn-xs shouyin" lay-event="shouyin">收银插件</a>
          <a class="layui-btn  layui-btn-xs merchantnumber" lay-event="merchantnumber" lay-href="">新大陆D0管理</a>
          <a class="layui-btn  layui-btn-xs merchantmanage" lay-event="merchantmanage" lay-href="">网商商户管理</a>
          <a class="layui-btn  layui-btn-xs fuyoumanage" lay-event="fuyoumanage" lay-href="">富友商户管理</a>
        </div>      
    </div>
  </div>
</div>


  <input type="hidden" class="js_store_id">
  <input type="hidden" class="js_id">
  <input type="hidden" class="js_store_name">


  <input type="hidden" class="user_id">
  <input type="hidden" class="status">
 
  <input type="hidden" class="provincecode" value="">
  <input type="hidden" class="provincename" value="">
  <input type="hidden" class="citycode" value="">
  <input type="hidden" class="cityname" value="">
  <input type="hidden" class="areacode" value="">
  <input type="hidden" class="areaname" value="">

  <input type="hidden" class="open_id" value="0">
  <input type="hidden" class="del_id" value="0">



  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/layuiadmin/layui/jquery-2.1.4.js')}}"></script>
  <script src="{{asset('/layuiadmin/layui/jquery.qrcode.min.js')}}"></script>
    <script>
    var token = localStorage.getItem("Usertoken");
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
                window.location.href="{{url('/user/login')}}"; 
            }
            $('.recover').hide();
            $('.open').hide();
        })


        $(".transfer").bind("input propertychange",function(event){
         console.log($(this).val())
          $.post("{{url('/api/user/get_sub_users')}}",
          {
              token:token,
              user_name:$(this).val(),
              self:'1'            

          },function(res){
              console.log(res);
              var html="";
              console.log(res.t)
              if(res.t==0){
                  $('.userbox').html('')
              }else{
                  for(var i=0;i<res.data.length;i++){
                      html+='<div class="list" data='+res.data[i].id+'>'+res.data[i].name+'-'+res.data[i].level_name+'</div>'
                  }
                  $(".userbox").show()
                  $('.userbox').html('')
                  $('.userbox').append(html)
              }
              
          },"json");
        });

        $(".userbox").on("click",".list",function(){
  
          $('.transfer').val($(this).html())
          $('.js_user_id').val($(this).attr('data'))
          $('.userbox').hide()

          table.reload('test-table-page', {
            where: { 
              user_id:$(this).attr('data')
            }
            ,page: {
              curr: 1
            }
          });
        })







        // 选择业务员
        $.ajax({
            url : "{{url('/api/user/get_sub_users')}}",
            data : {token:token,},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].id + "'>"
                            + data.data[i].name + "</option>";
                    }    
                    $("#agent").append('<option value="">选择业务员</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });

        // 地区选择
        $.ajax({
          url : "{{url('/api/basequery/city')}}",
          data : {area_code:'1'},
          type : 'get',
          success : function(data) {
              console.log(data);
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
            ,url: "{{url('/api/user/store_pc_lists')}}"
            ,method: 'post'
            ,where:{
              token:token, 
              is_close: $('.open_id').val(),
              is_delete: $('.del_id').val(),
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
              ,{width:430,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
              area: ['90%', '100%'],
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
            
          }else if(layEvent === 'shouyin'){
            $('.shouyin').attr('lay-href',"{{url('/user/shouyin?store_id=')}}"+e.store_id+"&store_name="+e.store_name);
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
        // 新添加的弹框按钮2019.1.14****

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
        $('.merchantmanage').click(function(){
          $(this).attr('lay-href',"{{url('/user/merchantmanage?')}}"+$('.js_store_id').val());
        })
        $('.fuyoumanage').click(function(){
          $(this).attr('lay-href',"{{url('/user/fuyoumanage?')}}"+$('.js_store_id').val());
        })
        // *********end

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
              ,page: {
                curr: 1
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
              ,page: {
                p: 1 //重新从第 1 页开始
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
              ,page: {
                curr: 1
              }
            });           
        });
        // 省市区end-------------------------------


        // 选择业务员
        form.on('select(agent)', function(data){
          var user_id = data.value;
          $('.user_id').val(user_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              user_id:$('.user_id').val()
            }
            ,page: {
              curr: 1
            }
          });
        });
        // 选择审核状态
        form.on('select(status)', function(data){
          var status = data.value;
          $('.status').val(status);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              status:$('.status').val()
            }
            ,page: {
              curr: 1
            }
          });
        });
        // 选择开关闭
        form.on('select(open)', function(data){
          var open_id = data.value;
          $('.open_id').val(open_id);
          if(open_id == 0){
            $('.close').show()
            $('.open').hide()
          }else{
            $('.close').hide()
            $('.open').show()
          }
          //执行重载
          table.reload('test-table-page', {
            where: { 
              is_close:$('.open_id').val()
            }
            ,page: {
              curr: 1
            }
          });
        });
        // 选择是否删除
        form.on('select(del)', function(data){
          var del_id = data.value;
          $('.del_id').val(del_id);
          if(del_id == 0){
            $('.del').show()
            $('.recover').hide()
          }else{
            $('.del').hide()
            $('.recover').show()
          }
          //执行重载
          table.reload('test-table-page', {
            where: { 
              is_delete:$('.del_id').val()
            }
            ,page: {
              curr: 1
            }
          });
        });

        //监听搜索
        form.on('submit(LAY-app-contlist-search)', function(data){
          var obj = data.field
          console.log(obj)
          var store_name = data.field.schoolname;    
          console.log(data);
          //执行重载
          table.reload('test-table-page', {
            page: {
             curr: 1
            },
            where: { 
              store_name:store_name,             
            }
          });
        });


        
        // 删除
        var active = {
          batchdel: function(){
            var checkStatus = table.checkStatus('test-table-page')
            ,checkData = checkStatus.data; //得到选中的数据
            console.log(checkData);
            var arrs=[]

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
        // 恢复
        var actives = {
          recover: function(){
            var checkStatus = table.checkStatus('test-table-page')
            ,checkData = checkStatus.data; //得到选中的数据
            console.log(checkData);
            var arrs=[]

            for(var i=0;i<checkData.length;i++){ 
              arrs.push(checkData[i].store_id);
            }
            var store_id=arrs.join()
            console.log(arrs.join())

            if(checkData.length === 0){
              return layer.msg('请选择门店');
            }
          
            layer.confirm('确定恢复吗？', function(index) {   

              $.post("{{url('/api/user/rec_store')}}",
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
            var arrs=[]

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
        // 开启
        var activets = {
          batchdelopen: function(){
            var checkStatus = table.checkStatus('test-table-page')
            ,checkData = checkStatus.data; //得到选中的数据
            console.log(checkData);
            var arrs=[]

            for(var i=0;i<checkData.length;i++){ 
              arrs.push(checkData[i].store_id);
            }
            var store_id=arrs.join()
            console.log(arrs.join())

            if(checkData.length === 0){
              return layer.msg('请选择门店');
            }
          
            layer.confirm('确定开启门店吗？', function(index) {   

              $.post("{{url('/api/user/ope_store')}}",
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

        // 添加门店转移        
        var activett = {          
          transfer: function(){
            var checkStatus = table.checkStatus('test-table-page')
            ,checkData = checkStatus.data; //得到选中的数据
            console.log(checkData);
            var arrtransfer = []
            var arrtransfername = []

            for(var i=0;i<checkData.length;i++){ 
              arrtransfer.push(checkData[i].store_id);
              arrtransfername.push(checkData[i].store_name);
            }
            var store_id=arrtransfer.join()
            var store_name=arrtransfername.join()
            console.log(arrtransfer.join())
            console.log(arrtransfername.join())
            localStorage.setItem('js_add_store_id', arrtransfer.join());
            localStorage.setItem('js_add_store_name', arrtransfername.join());
        

            if(checkData.length === 0){
              return layer.msg('请选择门店');
            }
            // else{

            //   // $('.addstoretransfer').attr('lay-href',"{{url('/user/addstoretransfer?store_id=')}}"+store_id+'&store_name='+store_name);
            // }
          
            
          }
        }
      

        $('.layui-btn.layuiadmin-btn-forum-list').on('click', function(){
          var type = $(this).data('type');
          console.log(type)
          active[type] ? active[type].call(this) : '';
          actives[type] ? actives[type].call(this) : '';
          activet[type] ? activet[type].call(this) : '';
          activets[type] ? activets[type].call(this) : '';
          activets[type] ? activets[type].call(this) : '';
          activett[type] ? activett[type].call(this) : '';
        });

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
                        ,time: 3000
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
        });

        $('.addstore').click(function(){
          $(this).attr('lay-href',"{{url('/user/editstore?0')}}");
        })
        $('.addstoretransfer').click(function(){
          $(this).attr('lay-href',"{{url('/user/addstoretransfer?store_id=')}}"+$('.js_add_store_id').val()+'&store_name='+$('.js_add_store_name').val());
          
        })
        

    });

  </script>

</body>
</html>





