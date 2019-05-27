<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>导入缴费账单</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
  <style>
    .edit{background-color: #ed9c3a;}
    .jf{background-color: #429488;}
    .layui-form-label{width:90px;}
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
                <div class="layui-card-header">导入缴费账单</div>

                <div class="layui-card-body">
                  <div class="layui-btn-container" style="font-size:14px;">
     
                    <div class="layui-form">

                      <div class="layui-form-item">
                        <div class="layui-inline">
                          <label class="layui-form-label">缴费单excel:</label>
                          <div class="layui-form-mid layui-word-aux">
                            <form id= "uploadForm" style="display: inline-block;">            
                              <div class="layui-btn name" style="margin-right:20px;">选择所需上传文件</div>                  
                              <input type="file" name="file" id="file" style="display: none;"/> 
                              <a href="{{url('/stu-order-import.xlsx')}}">模板下载</a>
                              

                            </form>

                          </div>
                        </div>
                      </div>

                      <div class="layui-form-item">
                        <label class="layui-form-label">缴费截止时间:</label>
                        <div class="layui-inline">
                          <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item" placeholder="截止时间" lay-key="23">
                          </div>
                        </div>               
                      </div>
                      <div class="layui-form-item">
                        <label class="layui-form-label"></label>
                        <div class="layui-inline">
                          <div class="layui-input-inline">
                            <input type="button" value="确定导入" id="Upload" class="layui-btn"/>
                          </div>
                        </div>               
                      </div>
                      <div class="layui-form-item">
                        备注:导入缴费单针对每个学生缴费项目不一样,使用此功能如果缴费项金额是相同的请使用模版缴费更方便              
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
  </div>

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
            ,laydate = layui.laydate
            ,form = layui.form;
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
        // $('.start-item').val(nwedata);
        nowdate.setMonth(nowdate.getMonth()-1);

        laydate.render({
            elem: '.start-item'
            ,type: 'datetime'
            ,done: function(value){
              // console.log(nwedata);
                var oDate1=new Date(nwedata);    
                var oDate2 = new Date(value);
                if(oDate1.getTime() > oDate2.getTime()){

                    layer.msg("截止时间不能低于当前时间", {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                    });

                }
            }
        });




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
            url: "{{url('/api/school/teacher/excel/order/import?token=')}}"+token+"&gmt_end="+$('.start-item').val(),  
            type: 'POST',  
            data: formData,//上传文件此项必填  
            async: false,  
            cache: false,  
            contentType: false,  
            processData: false,  
            success: function (res) {  
                console.log(res);
              if(res.status==1){
                layer.msg(res.message, {
                  offset: '15px'
                  ,icon: 1
                  ,time: 3000
                });
              }else{
                layer.confirm(res.message, function(index){
                  layer.close(index);
                });
                // layer.msg(res.message, {
                //   offset: '15px'
                //   ,icon: 2
                //   ,time: 3000
                // });
              }
                
            },  
            error: function (res) {  
              
                
            }  
         });

        });


    });

  </script>

</body>
</html>