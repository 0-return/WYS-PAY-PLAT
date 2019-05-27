<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>创建代付</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
    .layui-card-header{width:80px;text-align: right;float:left;}
    .layui-card-body{margin-left:28px;}
    .layui-upload-img{width: 92px; height: 92px; margin: 0 10px 10px 0;}

    .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;width: 92px !important;font-size: 10px !important;text-align: center !important;}
    .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
    .layui-upload-list{width: 100px;height:96px;overflow: hidden;}
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}
    
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header" style="width:auto !important">创建代付&nbsp;&nbsp;&nbsp;<span class="zong_school_name"></span></div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group">
                
                <div class="layui-form-item">
                    <label class="layui-form-label">收款银行卡发卡行名称</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入收款银行卡发卡行名称" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">收款客户姓名</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入收款客户姓名" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">收款人银行卡号</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入收款人银行卡号" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入备注" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">代付金额</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入代付金额" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">支付密码</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入支付密码" class="layui-input">
                    </div>
                </div>




                <div class="layui-form-item layui-layout-admin">
                    <div class="layui-input-block">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit site-demo-active" data-type="tabChange">确定</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<input type="hidden" class="storeid">
<input type="hidden" class="storename">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    // var str=location.search;
    // var school_name=str.split('?')[1];


    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects','element'], function(){
        var $ = layui.$ 
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form
            ,upload = layui.upload
            ,formSelects = layui.formSelects;

            var src=$('#demo1').attr('src');
        element.render();
  
        
        // 选择门店
        $.ajax({
            url : "{{url('/api/user/store_lists')}}",
            data : {token:token,l:100},
            type : 'post',
            success : function(data) {
                console.log(data);
                var optionStr = "";
                    for(var i=0;i<data.data.length;i++){
                        optionStr += "<option value='" + data.data[i].store_id + "'>"
                          + data.data[i].store_name + "</option>";
                    }    
                    $("#store").html('');
                    $("#store").append('<option value="">选择门店</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });

        form.on('select(store)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.storeid').val(category);
            $('.storename').val(categoryName);           
        });
        
        $('.submit').on('click', function(){
           


            $.post("{{url('/api/huodong/add')}}",
            {
                token:token,
                store_id:$('.storeid').val(),
                store_name:$('.storename').val(),
                content:$('.red_con').val(),
                remark:$('.remark').val()

            },function(res){
                console.log(res);

                if(res.status==1){
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 3000
                    },function(){
                        element.tabAdd('demo', {
                           title: '学校列表'
                           ,id: "{{url('/merchantpc/schoollist')}}"
                        });
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
</script>

</body>
</html>
