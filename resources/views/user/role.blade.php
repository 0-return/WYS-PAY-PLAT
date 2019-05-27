<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>管理后台 - 角色管理</title>
    <link href="{{asset('/user/css/bootstrap.min.css?v=3.3.5')}}" rel="stylesheet">
    <link href="{{asset('/user/css/animate.min.css')}}" rel="stylesheet">
    <link href="{{asset('/user/css/total.css')}}" rel="stylesheet">
    <link href="{{asset('/user/css/btn.css')}}" rel="stylesheet">
    <!-- <link href="{{asset('/user/css/plugins/sweetalert/sweetalert.css')}}" rel="stylesheet"> -->
    <style>

        .btn-default {
            width: 120px;
            height: 30px;
            line-height: 30px;
            display: inline-block;
            border: 1px solid #dee5e7;
            text-align: center;
            border-radius: 3px;
            cursor: pointer;
            background-color: #fff;
            color: #000;
        }
        
        .two{
            margin-left: 15px;
        }
        .one-name,.two-name{
            margin-left: 10px;
            font-size: 16px;
        }
        .add{
            width: 13px;
            height: 13px;
            float: left;
            margin-top: 5px;
            background: url("{{asset('/user/img/plus.gif')}}") no-repeat;

        }
        .minus{
            width: 13px;
            height: 13px;
            float: left;
            margin-top: 5px;
            background: url("{{asset('/user/img/minus.gif')}}") no-repeat;
        }

        .float-e-margins .btn{
            bottom: 30px;
            right: 30px;
        }
        .ibox-content{
            position: relative;
        }
        .theader_th{
            background: #2F4050 !important;
            opacity: 0.7;
            color: #fff;
            text-align: center;
        }
        tbody>tr>td{
            text-align: center;
        }
        .checkbox{
            float: left;
        }
        .gohome{display: none;}
    </style>

</head>
<body>


<div id="mask" class="mask"></div>
<div style="margin-left: 30px;margin-right: 30px;">

<span id="add-factor" class="btn-default" onclick="ShowDiv('add_role','mask')" style="margin: 30px 0;">添加角色</span>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th class="theader_th">角色名称</th>
            <th class="theader_th">操作</th>
        </tr>
        </thead>
        <tbody>


        </tbody>
    </table>
</div>
<!-- 添加角色 -->
<div id="add_role" class="ant-modal" style="width: 790px; transform-origin: 1054px 10px 0px;display: none">
    <div class="ant-modal-content">
        <button  class="ant-modal-close"  onclick="CloseDiv('add_role','mask')">
            <span class="ant-modal-close-x"></span>
        </button>
        <div class="ant-modal-header">
            <div class="ant-modal-title" >添加角色</div>
        </div>
        <div class="ant-modal-body">
            <form class="ant-form ant-form-horizontal">
                
                <div class="ant-row ant-form-item">
                    <div class="ant-col-6 ant-form-item-label">
                        <label  class="ant-form-item-required" >显示名称</label>
                    </div>
                    <div class="ant-col-16 ant-form-item-control-wrapper">
                        <div class="ant-form-item-control ">
                            <input type="text" id="display_name" name="display_name" placeholder="请输入显示名称" class="input ant-input ant-input-lg" >
                            <span style="color: red;font-size: 12px;display: none">请输入显示名称</span>
                        </div>
                    </div>
                </div>


                <div class="ant-row ant-form-item modal-btn form-button" style="margin-top: 24px; text-align: center;">
                    <div class="ant-col-22 ant-form-item-control-wrapper">
                        <div class="ant-form-item-control ">
                            <button type="button" class="ant-btn ant-btn-primary ant-btn-lg" id="addrole_sure"><span>确定添加</span></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- 分配权限 -->
<div id="jurisdiction_box" class="ant-modal" style="width: 520px; transform-origin: 1054px 10px 0px;display: none">
    <div class="ant-modal-content">
        <button  class="ant-modal-close"  onclick="CloseDiv('jurisdiction_box','mask')">
            <span class="ant-modal-close-x"></span>
        </button>
        <div class="ant-modal-header">
            <div class="ant-modal-title"><span class="power"></span>--分配权限</div>
        </div>
        <div class="ant-modal-body">
            <form class="ant-form ant-form-horizontal">

                <div class="ant-row ant-form-item">
                    <div id="ibox-content"  class="" style="padding-left: 30px;">
                        <div class="box" style="display: none;">
                            <div class="one">
                                <input type="checkbox" name="" id="" value="" style="display: none"/>
                                <div class="add"></div>
                                <span class="one-name"></span>
                            </div>
                        </div>
                        <div class="two" style="display: none;">
                            <input type="checkbox"  name="" class="checkbox" value=""/>
                            <span class="two-name" data-id=' ' ></span>
                        </div>

                    </div>
                    <div class="two" style="display: none;">
                        <input type="checkbox"  name="" class="checkbox" value=""/>
                        <span class="two-name" data-id=' ' ></span>
                    </div>

                </div>

                <div class="ant-row ant-form-item modal-btn form-button" style="margin-top: 24px; text-align: center;">
                    <div class="ant-col-22 ant-form-item-control-wrapper">
                        <div class="ant-form-item-control ">
                            <button type="button" class="ant-btn ant-btn-primary ant-btn-lg" id="sure_submit"><span>确定提交</span></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>




<div id="model_box" class="ant-modal" style="display:none;width: 30%;height: 20%;border-radius: 4px;text-align: center;padding: 20px 30px">
    <div class="ant-modal-content" id="model" style="border-color: #4cae4c; background-color: #4cae4c;color: #fff;padding: 20px 30px;">

    </div>
</div>

<input type="hidden" class="js_role_id">
<script src="{{asset('/user/js/jquery.min.js?v=2.1.4')}}"></script>
<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    layui.use('layer', function(){
      var layer = layui.layer;

        //删除角色
        $('tbody').on("click","tr td .delete_role",function(){              

            var role_id=$(this).attr("data-id");
            layer.confirm('确定删除此条信息吗?', function(index){
              
              $.ajax({
                url : "{{url('/api/role_permission/del_role')}}",
                    data : {token:token,role_id:role_id},
                    type : 'post',
                    success : function(data) {
                      console.log(data);
                      layer.msg(data.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 1000
                      },function(){
                        window.location.reload()
                      });  
                    },
                    error : function(data) {
                      layer.msg(data.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                      });
                    }
                });
            });
        }); 
    });


    $(document).ready(function () {
        
        render();
        function render() {
            var jsonDataArr = "";        

            //获取数据 视图渲染           
            $.post("{{url('/api/role_permission/role_list')}}",
                {
                    token:token
                },function(res){
                    console.log(res);
                    var html='';
                    for(var i=0;i<res.data.length;i++){
                        html+='<tr><td style="line-height: 32px;">'+res.data[i].display_name+'</td>';
                        html+='<td><span class="btn-info jurisdiction" data-id='+res.data[i].role_id+' data-name='+res.data[i].display_name+' >分配权限</span>';
                        html+='<span class="delete_role btn-info " data-id='+res.data[i].role_id+'>删除</span></td></tr>';
                        
                    }
                    $('tbody').html();
                    $('tbody').append(html)
                },"json");
            }
        });
        //添加角色
        $('#addrole_sure').click(function () {

            $.post("{{url('/api/role_permission/add_role')}}",
            {
                token:token,
                display_name:$('#display_name').val()
            },function(res){
                console.log(res);
                if(res.status==1){                                        
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 1
                        ,time: 2000
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
         



   //获取权限列表
        $('tbody').on("click","tr td .jurisdiction",function(){ 
            ShowDiv('jurisdiction_box','mask')
        })
        var permission_idArr = [];
        $('tbody').on("click","tr td .jurisdiction",function(){ 
            var role_id=$(this).attr("data-id");
            var name=$(this).attr('data-name');
            $('.power').html(name);
            $('.js_role_id').val(role_id);
            //默认
            $.ajax({
                type: "post",
                url:  location.protocol+'//'+document.domain+"/api/role_permission/role_permission_list",
                async: false,
                dataType: 'json',
                data: {
                    token: token,
                    role_id:role_id
                },
                success: function (res) {
                    console.log(res);
                    if(res.status==1){
                        for(var i =0;i < res.data.length;i++){
                            permission_idArr.push(res.data[i].permission_id)
                        }

                    }

                },                    
            });
            //权限层级列表
            $.ajax({
                type: "post",
                url:  location.protocol+'//'+document.domain+"/api/role_permission/permission_list",
                async: true,
                dataType: 'json',
                data: {
                    token: token
                },
                success: function (res) {
                    console.log(res);
                    jsonDataArr=res;
                    //定义2个数组,一个存放一级目录,一个为二级目录
                    var oneArr = [];
                    var twoArr = [];
                    var html="";
                    $.each(jsonDataArr,function(i,v){
                        console.log(v);
                        for(var i=0;i<v.length;i++){
                            if(v[i].permission_id==undefined){

                            }else{
                                html+='<div class="box" style="display: block;">';
                                    html+='<div class="one" data-id="'+v[i].permission_id+'">';
                                        html+='<input type="checkbox" name="" id="" value="" style="display: none"/>';
                                        html+='<div class="add"></div>';
                                        html+='<span class="one-name">'+v[i].display_name+'</span>';
                                    html+='</div>';
                                

                                if(v[i].child){
                                    $.each(v[i].child, function (i2, v2) {
                                        html+='<div class="two" style="display: none;" data-id="'+v2.permission_id+'">';
                                            html+='<input type="checkbox"  name="" class="checkbox" value=""/>';
                                            html+='<span class="two-name" data-id="">'+v2.display_name+'</span>';
                                        html+='</div>';
                                    })
                                }
                                html+='</div>';
                            }

                            
                        }
                        $('#ibox-content').append(html);
                        console.log(permission_idArr);
                        for( var m=0;m<permission_idArr.length;m++){
                            $('#ibox-content .box .two').find('input').each(function(index,item){

                                if(permission_idArr[m]==$(item).parent().attr('data-id')){
                                    $(item).attr('checked',true);                                    
                                }
                            })
                        }
                        
                    });
                    

                    //角色权限分配  
                    $('#sure_submit').click(function () {
                        var iDarr = [];
                        $(".box").find("input[type='checkbox']").each(function(i,e){
                            
                            if($(e).is(":checked")){
                                iDarr.push($(e).parent().attr("data-id"))
                                iDarr.join();
                                console.log(iDarr.join());
                            }
                        });

                        $.ajax({
                            type: "post",
                            url:  location.protocol+'//'+document.domain+"/api/role_permission/assign_permission",
                            async: true,
                            dataType: 'json',
                            data: {
                                token: token,
                                role_id:role_id,
                                permission_id:iDarr.join()
                            },
                            success: function (res) {
                                console.log(res);
                                if(res.status==1){                                        
                                    layer.msg(res.message, {
                                        offset: '15px'
                                        ,icon: 1
                                        ,time: 2000
                                    });
                                }else{
                                    layer.msg(res.message, {
                                        offset: '15px'
                                        ,icon: 2
                                        ,time: 3000
                                    });
                                }
                            },
                        });
                    });
                    //默认是收起来的 点击名称可以下拉
                    $("#ibox-content").on("click",".one",function(){
                        $(this).nextAll(".two").toggle();
                    });
                },
                
            });
        });




    function CloseModel() {
        $('#model_box').hide()
    }
    //    添加角色
    function ShowDiv(show_div,bg_div){
        document.getElementById(show_div).style.display='block';
        document.getElementById(bg_div).style.display='block' ;
        var bgdiv = document.getElementById(bg_div);
        bgdiv.style.width = document.body.scrollWidth;
        $("#"+bg_div).height($(document).height());

    }
//     //关闭弹出层
    function CloseDiv(show_div,bg_div){
        document.getElementById(show_div).style.display='none';
        document.getElementById(bg_div).style.display='none';
        window.location.reload()

    }

    $('#addrole_sure').click(function () {
        $(".input").each(function () {
            var val = $(this).val();
            if (val == "") {
                $(this).focus().css({
                    "border": "1px solid red"
                });
                $(this).next().show()

            }

        });
    });



//     $(".input").each(function() {
//         $(this).click(function () {
//             $(this).css({"border": "1px solid #d9d9d9"});
//             $(this).next().hide()
//         });
//     });
</script>

</body>
</html>
