<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>修改缴费项目</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/layui/css/layui.css')); ?>" media="all">
    <link rel="stylesheet" href="<?php echo e(asset('/layuiadmin/style/admin.css')); ?>" media="all">
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">班级信息</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">                
                <div class="layui-form-item school">
                    <label class="layui-form-label">选择学校</label>
                    <div class="layui-input-block">
                        <select name="schooltype" id="schooltype" lay-filter="schooltype">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item grade">
                    <label class="layui-form-label">选择年级</label>
                    <div class="layui-input-block">
                        <select name="grade" id="grade" lay-filter="grade">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item class">
                    <label class="layui-form-label">选择班级</label>
                    <div class="layui-input-block">
                        <select name="class" id="class" lay-filter="class">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item class">
                    <label class="layui-form-label">选择学生</label>
                    <div class="layui-input-block" style="min-height: 40px;" id="student_list">

                    </div>
                </div>                
            </div>


        </div>
    </div>
    <div class="layui-card">
      
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-form">
            <div class="layui-form-item">
                <label class="layui-form-label">截止时间</label>                          
                <div class="layui-inline">
                  <div class="layui-input-inline">
                    <input type="text" class="layui-input start-item test-item" placeholder="截止时间" lay-key="23">
                  </div>
                </div>               
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">项目名称</label>                          
                <div class="layui-inline">
                  <div class="layui-input-inline">
                    <input type="text" class="layui-input batch_name" placeholder="请输入项目名称" lay-key="23">
                  </div>
                  <div class="layui-form-mid layui-word-aux">例如:2018学杂费</div>
                </div>               
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">缴费模板</label>
                <div class="layui-input-block">
                    <select name="template" id="template" lay-filter="template">
                        
                    </select>
                </div>
            </div>
            <table class="layui-table">
              <colgroup>
                <col width="150">
                <col width="150">
                <col width="200">
                <col width="200">
              </colgroup>
              <thead>
                <tr>
                  <th>缴费名称</th>
                  <th>缴费金额</th>
                  <th>数量</th>
                  <th>是否必交(Y或N)</th>
                </tr> 
              </thead>
              <tbody>            
                
              </tbody>
            </table>


            <div class="layui-form-item">
              <div class="layui-inline">
                <label class="layui-form-label">总金额:</label>
                <div class="layui-form-mid layui-word-aux"><span class="amount">0.00</span>&nbsp;&nbsp;&nbsp;必缴<span class="mustpay">0.00</span>&nbsp;&nbsp;&nbsp;非必缴<span class="nomustpay">0.00</span></div>
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

<input type="hidden" class="store_id" value="">
<input type="hidden" class="gradeid" value="">
<input type="hidden" class="classid" value="">
<input type="hidden" class="templateid" value="">
<input type="hidden" class="student_code" value="">

<script src="<?php echo e(asset('/layuiadmin/layui/layui.js')); ?>"></script> 
<script>
    var token = localStorage.getItem("token");
    var jf_store_id=localStorage.getItem("jf_store_id");
    var jf_stu_order_batch_no=localStorage.getItem("jf_stu_order_batch_no");
    var jf_stu_grades_no=localStorage.getItem("jf_stu_grades_no");
    var jf_stu_class_no=localStorage.getItem("jf_stu_class_no");
    var jf_stu_order_type_no=localStorage.getItem("jf_stu_order_type_no");


    var str=location.search;
    var stu_order_batch_no=str.split('?')[1];

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects','laydate'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form
            ,formSelects = layui.formSelects;

        $('.classid').val(jf_stu_class_no);

        getBoards();
       
        function getBoards(){ 
            // 选择学校
            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/lst')); ?>",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].store_id + "' "+((jf_store_id==data.data[i].store_id)?"selected":"")+">"
                            + data.data[i].school_name + "</option>";
                        }    
                        $("#schooltype").append('<option value="">选择学校</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 选择年级
            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/grade/lst')); ?>",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            optionStr += "<option value='" + data.data[i].stu_grades_no + "' "+((jf_stu_grades_no==data.data[i].stu_grades_no)?"selected":"")+">"
                            + data.data[i].stu_grades_name + "</option>";
                        }    
                        $("#grade").append('<option value="">选择年级</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 选择班级
            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/class/lst')); ?>",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            
                            optionStr += "<option value='" + data.data[i].stu_class_no + "' "+((jf_stu_class_no==data.data[i].stu_class_no)?"selected":"")+">"
                            + data.data[i].stu_class_name + "</option>";
                        }    
                        $("#class").append('<option value="">选择班级</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 选择缴费模板
            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/template/lst')); ?>",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){
                            
                            optionStr += "<option value='" + data.data[i].stu_order_type_no + "' "+((jf_stu_order_type_no==data.data[i].stu_order_type_no)?"selected":"")+">"
                            + data.data[i].charge_name + "</option>";
                        }    
                        $("#template").append('<option value="">选择缴费模板</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 页面加载完成,缴费模板
            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/template/lst')); ?>",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        if(jf_stu_order_type_no==data.data[i].stu_order_type_no){
                            var str=data.data[i].charge_item;
                            var res=JSON.parse(str);
                            // console.log(res);
                            var sum=0;
                            var sum2=0;
                            var str="";
                            for(var j=0;j<res.length;j++){
                                
                                str+='<tr>';
                                  str+='<td>'+res[j].item_name+'</td>';
                                  str+='<td>'+res[j].item_price+'</td>';
                                  str+='<td>'+res[j].item_number+'</td>';
                                  str+='<td>'+res[j].item_mandatory+'</td>';
                                str+='</tr>';

                                
                                if(res[j].item_mandatory=='Y'){
                                    sum=sum+parseFloat(res[j].item_price);
                                }else{
                                    sum2=sum2+parseFloat(res[j].item_price);
                                }
                                // console.log(sum+sum2);
                                $('.amount').html((sum+sum2).toFixed(2));
                                $('.mustpay').html(sum.toFixed(2));
                                $('.nomustpay').html(sum2.toFixed(2));

                            }
                            
                        }
                    } 
                    $('tbody').html('');
                    $('tbody').append(str);   
                        
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 学生列表
            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/stu/lst')); ?>",
                data : {token:token,store_id:jf_store_id,stu_grades_no:jf_stu_grades_no,stu_class_no:jf_stu_class_no},
                type : 'post',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                    var arr=[];
                        for(var i=0;i<data.data.length;i++){

                            optionStr += '<input type="checkbox" name="student" student_no="'+data.data[i].student_no+'"  title="'+data.data[i].student_name+'" lay-filter="student" value="'+data.data[i].student_no+'">';
                            

                            arr.push(data.data[i].student_no);  //学生编号放进数组                          
                            $('.student_code').val(arr.join()); //编号用逗号隔开
                        }    
                        $("#student_list").html('');
                        $("#student_list").append(optionStr);
                        form.render('checkbox');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 没条内容详情
            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/payitem/show')); ?>",
                data : {token:token,stu_order_batch_no:jf_stu_order_batch_no},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    $('.start-item').val(data.data.gmt_end);
                    $('.batch_name').val(data.data.batch_name);
                    
                    var student_no=data.data.remove_student_no;
                    console.log(student_no.split(','));
                    var len=student_no.split(',');
                    for(var i=0;i<len.length;i++){
                        console.log(len[i]);
                        $('#student_list input').each(function(){
                            
                            if(len[i]==$(this).attr('student_no')){
                                $(this).attr('checked','checked');
                                $(this).next('.layui-unselect').addClass('layui-form-checked');
                            }
                        })
                    }


                },
                error : function(data) {
                    alert('查找板块报错');
                }
            }); 
            
        }

        form.on('select(schooltype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.store_id').val(category);          
        });
        
        form.on('select(grade)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.gradeid').val(category);       
        });
        form.on('select(class)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.classid').val(category); 

            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/stu/lst')); ?>",
                data : {token:token,store_id:$('.store_id').val(),stu_grades_no:$('.gradeid').val(),stu_class_no:$('.classid').val()},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                    var arr=[];
                        for(var i=0;i<data.data.length;i++){

                            optionStr += '<input type="checkbox" name="student" student_no="'+data.data[i].student_no+'"  title="'+data.data[i].student_name+'" checked lay-filter="student" value="'+data.data[i].student_no+'">';
                            

                            arr.push(data.data[i].student_no);  //学生编号放进数组                          
                            $('.student_code').val(arr.join()); //编号用逗号隔开
                        }    
                        $("#student_list").html('');
                        $("#student_list").append(optionStr);
                        form.render('checkbox');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });       
        });
        form.on('select(template)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.templateid').val(category); 

            $.ajax({
                url : "<?php echo e(url('/api/school/teacher/template/lst')); ?>",
                data : {token:token},
                type : 'post',
                success : function(data) {
                    console.log(data);
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        if(category==data.data[i].stu_order_type_no){
                            var str=data.data[i].charge_item;
                            var res=JSON.parse(str);
                            console.log(res);
                            var sum=0;
                            var sum2=0;
                            var str="";
                            for(var j=0;j<res.length;j++){
                                
                                str+='<tr>';
                                  str+='<td>'+res[j].item_name+'</td>';
                                  str+='<td>'+res[j].item_price+'</td>';
                                  str+='<td>'+res[j].item_number+'</td>';
                                  str+='<td>'+res[j].item_mandatory+'</td>';
                                str+='</tr>';

                                
                                if(res[j].item_mandatory=='Y'){
                                    sum=sum+parseFloat(res[j].item_price);
                                }else{
                                    sum2=sum2+parseFloat(res[j].item_price);
                                }
                                // console.log(sum+sum2);
                                $('.amount').html((sum+sum2).toFixed(2));
                                $('.mustpay').html(sum.toFixed(2));
                                $('.nomustpay').html(sum2.toFixed(2));

                            }
                            
                        }
                    } 
                    $('tbody').html('');
                    $('tbody').append(str);   
                        
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });      
        });
        
        form.on('checkbox(student)', function(data){
            var arrs=[];//定义空数组
            $("input:checkbox[name='student']:checked").each(function() { // 遍历name=standard选中的多选框的值
                var standards ='';
                standards = $(this).val();          
                arrs.push(standards);
                console.log(arrs);
                
            });
            $('.student_code').val(arrs.join());
        });
        
        



        var arr=[];
        $('.submit').on('click', function(){

            var stu_class_no=$('.classid').val();
            var remove_student_no=$('.student_code').val();

            var data ={"stu_class_no":stu_class_no,"remove_student_no":remove_student_no}
            arr.push(data);
            var tableJson=JSON.stringify(arr);//转化成json格式
            console.log(tableJson);




            $.post("<?php echo e(url('/api/school/teacher/payitem/save')); ?>",
            {
                token:token,
                stu_order_batch_no:jf_stu_order_batch_no,
                store_id:$('.store_id').val(),
                stu_grades_no:$('.gradeid').val(), 
                // stu_class_no:$('.classid').val(),
                // remove_student_no:$('.student_code').val(),
                class_and_rmstu:tableJson,
                stu_order_type_no:$('.templateid').val(),
                gmt_end:$('.start-item').val(),
                batch_name:$('.batch_name').val()


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
                        ,time: 3000
                    });
                }
            },"json");

        });



        laydate.render({
            elem: '.start-item'
            ,type: 'datetime'
            ,done: function(value){
              // console.log(value);
              
            }
        });

    });
</script>
</body>
</html>
