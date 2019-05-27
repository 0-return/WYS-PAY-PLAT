<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加缴费模板</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    
    <div class="layui-card">
      <div class="layui-card-header">缴费模板修改</div>
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-form">
            <div class="layui-form-item school">
                <label class="layui-form-label">选择学校</label>
                <div class="layui-input-block">
                    <select name="schooltype" id="schooltype" lay-filter="schooltype">
                        
                    </select>
                </div>
            </div> 
            <div class="layui-form-item">
                <label class="layui-form-label">模版名称</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入模版名称" class="layui-input templatename">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">模版描述</label>
                <div class="layui-input-block">
                    <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="请输入模版描述" class="layui-input templatedesc">
                </div>
            </div>
            


            <div class="layui-fluid">
                <div class="layui-row layui-col-space15">
                  <div class="layui-col-md12">
                    <div class="layui-card">
                      <button class="layui-btn layui-btn-sm" id="addhang" style="margin-left: 15px;"><i class="layui-icon"></i>添加行</button>
                      <div class="layui-card-body">
                        <table class="layui-hide" id="test-table-cellEdit" lay-filter="test-table-cellEdit">
                            
                        </table>
                        <!-- <script type="text/html" id="test-table-switchTpl">
                            <input type="checkbox" name="zzz" lay-skin="switch" lay-text="是|否" checked>
                        </script> -->
                        <script type="text/html" id="test-table-switchTpl">
                            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
                        </script>
                      </div>
                    </div>
                  </div>
                </div>
            </div>




            <div class="layui-form-item">
                <label class="layui-form-label" style="font-size: 20px;">总金额:</label>
                 <div class="layui-input-block">
                    <div class="amount" style="line-height: 36px;font-size: 20px;">0.00</div>
                </div>
            </div>


            <div class="layui-form-item layui-layout-admin">
                <div class="layui-input-block">
                    <div class="layui-footer" style="left: 0;">
                        <button class="layui-btn submit">确定提交</button>
                        <!--<button type="reset" class="layui-btn layui-btn-primary">重置</button>-->
                    </div>
                </div>
            </div>
        </div>        
      </div>
    </div>
   
</div>

<input type="hidden" class="schooltypeid" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("token");
    var store_id = localStorage.getItem("store_id");
    var stu_order_type_no = localStorage.getItem("stu_order_type_no");
    

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'table','form'], function(){
        var $ = layui.$            
            table = layui.table
            ,form = layui.form;
        $('.schooltypeid').val(store_id);
        

        $.post("{{url('/api/school/teacher/template/show')}}",
        {
          token:token,
          stu_order_type_no:stu_order_type_no
        }, 
        function(res){
          // console.log(res);     
          $('.layui-form .layui-form-item').eq(1).find('input').val(res.data.charge_name);
          $('.layui-form .layui-form-item').eq(2).find('input').val(res.data.charge_desc);
          var money=parseFloat(res.data.amount);

          $('.amount').html(money.toFixed(2));


          var str=res.data.charge_item;//返回json格式
          // var data=JSON.parse(str) ;  //json格式转化数组
          console.log(str);          


            // 构造表格-----------
            var tablenew = str;//将已有数据构造表格

            var tableBox = table.render({
                elem: '#test-table-cellEdit'
                // ,url:"{{asset('/layuiadmin/json/template.js')}}"
                ,page: true 
                ,cellMinWidth: 150     
                ,cols: [[                
                    {field:'item_name', title: '缴费名称', edit: 'text'}
                    ,{field:'item_price', title: '缴费金额', edit: 'text'}
                    ,{field:'item_number',  title: '数量', edit: 'text'}
                    ,{field:'item_mandatory', title: '是否必交(Y或N)', edit: 'text'}
                    ,{fixed: 'right', width:150, align:'center', title: '操作',toolbar: '#test-table-switchTpl'}
                ]]
                ,data:tablenew//表格加载渲染一次空数组
                ,done: function(res){              
                    console.log(res);               


                    $("#addhang").off('click').on('click',function(){//$("#addhang").click(function()  这样写会让表格成倍增加,不可取
                        
                        var _dw={"item_name":"","item_price":"","item_number":"","item_mandatory":"Y"};
                        tablenew.push(_dw);
                        tableBox.reload({
                            data:tablenew
                        })//点击添加行之后渲染表格

                    });
                }
            });


            //监听工具条
            table.on('tool(test-table-cellEdit)', function(obj){
              var data = obj.data;
              var tr = obj.tr;
              var which=tr[0].dataset.index;//删除行的下标
              
              if(obj.event === 'del'){
                layer.confirm('确定删除缴费小项吗?', function(index){
                    obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                    layer.close(index);

                    //向服务端发送删除指令
                    console.log(which);
                    tablenew.splice(which,1);//删除tablenew数组下标为which,长度为1的一个值
                    console.log(tablenew);               
                    
                });
                
              } 
            });

            // 提交表单----------------------
            $('.submit').on('click', function(){

                var tableJson=JSON.stringify(tablenew);//转化成json格式
                console.log(tablenew);
                var sum=0;            
                for(var i=0;i<tablenew.length;i++){
                    sum=sum+parseFloat(tablenew[i].item_price*tablenew[i].item_number);
                }     
                sum=sum.toFixed(2)
                $('.amount').html(sum);//收费金额


                $.post("{{url('/api/school/teacher/template/save')}}",
                {
                    token:token,
                    stu_order_type_no:stu_order_type_no,
                    store_id:$('.schooltypeid').val(),
                    charge_name:$('.templatename').val(),
                    charge_desc:$('.templatedesc').val(),
                    charge_item:tableJson,
                    amount:sum

                },function(res){
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
                },"json");

            });

            //--------------------------------------------------------
            //监听单元格编辑
            table.on('edit(test-table-cellEdit)', function(obj){
                var value = obj.value //得到修改后的值
                ,data = obj.data //得到所在行所有键值
                ,field = obj.field; //得到字段
                console.log(obj);
                if(field == "item_price"){
                    var a = parseFloat(value).toFixed(2);
                    console.log(a);
                    $(this).val(a);
                    obj.value=a;
                    obj.data.item_price=a;
                }else if(field == "item_mandatory"){
                    if(value=='Y' || value=='N'){
                        $(this).val(value);
                    }else{
                        $(this).val('');
                    }
                }else if(field === "item_number"){
                    var reg = /^[\d]+$/g;
                    if(!reg.test(value)){
                        $(this).val('');
                    }else{
                        $(this).val(value);
                    }
                }


                if(field === "item_price" || field === "item_number"){
                    var sum=0;            
                    for(var i=0;i<tablenew.length;i++){
                        sum=sum+parseFloat(tablenew[i].item_price*tablenew[i].item_number);
                    }     
                    sum=sum.toFixed(2)
                    
                    if(sum=='NaN'){
                        $('.amount').html('');//收费金额
                    }else{
                        $('.amount').html(sum);//收费金额
                    }
                }
            });

        }); 
        

        
        

        

        
        



       
 
        getBoards();
        // 选择学校-----开始
        function getBoards(){ 
            // 选择学校
            $.ajax({
            url : "{{url('/api/school/teacher/lst')}}",
            data : {token:token},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].store_id + "' "+((store_id==data.data[i].store_id)?"selected":"")+">"
                            + data.data[i].school_name + "</option>";
                    }    
                    $("#schooltype").append('<option value="">选择学校</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });  
        }

        form.on('select(schooltype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.schooltypeid').val(category);          
        });
        // 选择学校----结束      


        

        

        


    });
</script>
</body>
</html>
