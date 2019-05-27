<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>缴费模板</title>
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
                <div class="layui-card-header">缴费列表</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
                    <a class="layui-btn layui-btn-primary" lay-href="{{url('/merchantpc/addpaytemplate')}}" style="display: block;width: 122px;">添加缴费模板</a>
                    <div class="layui-form" style="display: block;">                      
                      <div class="layui-form-item">                          
                        <div class="layui-inline">
                          <label class="layui-form-label" style="width: 56px;padding: 9px 15px 9px 0;text-align: left;">创建时间</label>
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item" placeholder="开始日期" lay-key="23">
                          </div>
                        </div>
                        <div class="layui-inline">
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input end-item test-item" placeholder="结束日期" lay-key="24">
                          </div>
                          </div>
                      </div>                      
                    </div>
                    <div class="layui-form" lay-filter="component-form-group" style="width:250px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="schooltype" id="schooltype" lay-filter="schooltype">
                                
                            </select>
                        </div>
                      </div>
                    </div>
                    
                    <div class="layui-form" lay-filter="component-form-group" style="width:250px;display: inline-block;">
                      <div class="layui-form-item">                          
                        <div class="layui-input-block" style="margin-left:0">
                            <select name="class" id="class" lay-filter="class">
                                <option value="">选择状态</option>
                                <option value="1">审核成功</option>
                                <option value="2">未审核</option>
                                <option value="3">审核失败</option>
                            </select>
                        </div>
                      </div>
                    </div>
                    
                    

                  </div>
                  
                  <table class="layui-hide" id="test-table-page" lay-filter="test-table-page"></table>
                  <script type="text/html" id="statusTap">
                    @{{#  if(d.status == 1){ }}
                      <span class="cur">@{{ d.status_desc }}</span>
                    @{{#  } else { }}
                      @{{ d.status_desc }}
                    @{{#  } }}
                  </script>

                  <script type="text/html" id="table-content-list">
                    <a class="layui-btn layui-btn-normal layui-btn-xs shenhe" lay-event="shenhe">审核</a>
                    <a class="layui-btn layui-btn-normal layui-btn-xs edit" lay-event="edit" lay-href="{{url('/merchantpc/edittemplate')}}">模板修改</a>
                    
                    <a class="layui-btn layui-btn-normal layui-btn-xs see" lay-event="detail">查看</a>
                  </script>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  <input type="hidden" class="store_id">
  <input type="hidden" class="status">

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
       
        

        table.render({
            elem: '#test-table-page'
            ,url: "{{url('/api/school/teacher/template/lst')}}"
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
                {field:'charge_name', title: '模板名称'}
                ,{field:'charge_desc', title: '模板描述'}
                ,{field:'status_desc',  title: '状态',templet: '#statusTap'}   
                ,{field:'school_name',  title: '学校'}             
                ,{field:'merchant_name',  title: '创建人'}                
                ,{field:'created_at',  title: '创建时间'}
                ,{width:180,align:'center', fixed: 'right', toolbar: '#table-content-list',title: '操作'}
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
          localStorage.setItem('store_id', e.store_id);

          if(layEvent === 'detail'){ //删除
            layer.open({
              type: 2,
              title: '查看',
              shade: false,
              maxmin: true,
              area: ['70%', '80%'],
              content: "{{url('/merchantpc/seetemplate?')}}"+e.stu_order_type_no
            });
          }else if(layEvent === 'shenhe'){
            layer.open({
              type: 2,
              title: '缴费模板审核 ',
              shade: false,
              maxmin: true,
              area: ['70%', '80%'],
              content: "{{url('/merchantpc/examinetemplate?')}}"+e.stu_order_type_no
            });
          }
        });

        
        // 选择学校
        form.on('select(schooltype)', function(data){
          var store_id = data.value;
          $('.store_id').val(store_id);
          //执行重载
          table.reload('test-table-page', {
            where: { 
              store_id: store_id,
              start_time: $('.start-item').val(),
              end_time:$('.end-item').val(),
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
              store_id:$('.store_id').val(),
              start_time: $('.start-item').val(),
              end_time:$('.end-item').val()
            }
          });
        });



      // 获取时间
      var nowdate = new Date();
      // 本月
      var year=nowdate.getFullYear();
      var mounth=nowdate.getMonth()+1;
      var day=nowdate.getDate();
      var hour = nowdate.getHours();       
      var min = nowdate.getMinutes();     
      var sec = nowdate.getSeconds();
      if(mounth.toString().length<2 && day.toString().length<2){
          var nwedata = year+'-0'+mounth+'-0'+day+' '+hour+':'+min+':'+sec;
      }
      else if(mounth.toString().length<2){
          var nwedata = year+'-0'+mounth+'-'+day+' '+hour+':'+min+':'+sec;
      }
      else if(day.toString().length<2){
          var nwedata = year+'-'+mounth+'-0'+day+' '+hour+':'+min+':'+sec;
      }
      else{
          var nwedata = year+'-'+mounth+'-'+day+' '+hour+':'+min+':'+sec;
      }
      $('.end-item').val(nwedata);
      nowdate.setMonth(nowdate.getMonth()-1);
      // 上个月
      var y = nowdate.getFullYear();
      var mon = nowdate.getMonth()+1;
      var d = nowdate.getDate()+1;
      var h = '00';
      var m = '00';
      var s = '00';
      if(mon.toString().length<2 && d.toString().length<2){
          var formatwdate = y+'-0'+mon+'-0'+d+' '+h+':'+m+':'+s;
      }
      else if(mon.toString().length<2){
          var formatwdate = y+'-0'+mon+'-'+d+' '+h+':'+m+':'+s;
      }
      else if(d.toString().length<2){
          var formatwdate = y+'-'+mon+'-0'+d+' '+h+':'+m+':'+s;
      }
      else{
          var formatwdate = y+'-'+mon+'-'+d+' '+h+':'+m+':'+s;
      }
      $('.start-item').val(formatwdate);


      laydate.render({
        elem: '.start-item'
        ,type: 'datetime'
        ,done: function(value){
          // console.log(value);
          var a=$(".end-item").val();
          var oDate1=new Date(a);

          var oDate2 = new Date(value);
          if(oDate2.getTime() > oDate1.getTime()){
              swal({title:"开始时间不能超过结束时间",timer: 1000,showConfirmButton: false,type:"error"});
          }else{
            //执行重载
            table.reload('test-table-page', {
              where: { 
                start_time: value,
                end_time:$('.end-item').val(),
                store_id:$(".store_id").val(),
                status:$(".status").val()
              }
            });
          }
        }
      });

      laydate.render({
        elem: '.end-item'
        ,type: 'datetime'
        ,done: function(value){
          // console.log(value); 
          var a=$(".start-item").val();
          var oDate1=new Date(a);
          
          var oDate2 = new Date(value);
          if(oDate1.getTime() > oDate2.getTime()){
              swal({title:"开始时间不能超过结束时间",timer: 1000,showConfirmButton: false,type:"error"});
          }else{
            //执行重载
            table.reload('test-table-page', {
              where: { 
                start_time: $('.start-item').val(),
                end_time:value,
                store_id:$(".store_id").val(),
                status:$(".status").val()
              }
            });  
          }
        }
      });
    });


  </script>

</body>
</html>