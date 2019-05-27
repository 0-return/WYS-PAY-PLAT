<!DOCTYPE html>
<!-- saved from url=(0049)http://wy.umxnt.com/merchant/permissionList?v=4.0 -->
<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <title>管理后台 - 角色管理</title>
    <link href="{{asset('/user/css/bootstrap.min.css?v=3.3.5')}}" rel="stylesheet">
    <link href="{{asset('/user/css/animate.min.css')}}" rel="stylesheet">
    <link href="{{asset('/user/css/total.css')}}" rel="stylesheet">
    <link href="{{asset('/user/css/btn.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/user/css/layer.css')}}">
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
        .permission_lists{

        }
        .permission_lists li span{
            cursor: pointer;
            font-size: 18px;
            margin-right: 5px;
            margin-left: 5px;
        }
        .permission_lists .item{
            font-size: 24px; background-color: #ccc; line-height: 45px;
            margin-bottom: 30px;
            padding-left: 20px;
        }
        .permission_lists .child-item{
            color: #666;
            float: left;
            margin-right: 18px;
            font-size: 20px;
            padding-left: 5px;
            margin-top: 7px;
        }
        .gohome{display: none;}
       /* .add_perm{ background-position: 0 -24px;}
        .edit_perm{ background-position: 0 -24px;}
        .del_perm{ background-position: 0 -24px;}*/
    </style>
</head>
<body style="">


<div id="mask" class="mask"></div>
<div style="margin-left: 30px;margin-right: 30px;">

    <span id="add-factor" class="btn-default" onclick="ShowDiv(&#39;add_permission&#39;,&#39;mask&#39;)" style="margin: 30px 0;">添加权限</span>

    <div class="permission_lists">
        <!-- <ul class="lists">
            
        </ul> -->
    </div>
</div>

<div id="add_permission" class="ant-modal" style="width: 790px; transform-origin: 1054px 10px 0px;display: none">
    <div class="ant-modal-content">
        <button class="ant-modal-close" onclick="CloseDiv(&#39;add_permission&#39;,&#39;mask&#39;)">
            <span class="ant-modal-close-x"></span>
        </button>
        <div class="ant-modal-header">
            <div class="ant-modal-title">添加权限</div>
        </div>
        <div class="ant-modal-body">
            <form class="ant-form ant-form-horizontal">

                
                <div class="ant-row ant-form-item">
                    <div class="ant-col-6 ant-form-item-label">
                        <label class="ant-form-item-required">显示名称</label>
                    </div>
                    <div class="ant-col-16 ant-form-item-control-wrapper">
                        <div class="ant-form-item-control ">
                            <input type="text" id="display_name" name="display_name" placeholder="请输入显示名称" class="input ant-input ant-input-lg" required="">
                            <span style="color: red;font-size: 12px;display: none">请输入显示名称</span>
                        </div>
                    </div>
                </div>
                


                <div class="ant-row ant-form-item modal-btn form-button" style="margin-top: 24px; text-align: center;">
                    <div class="ant-col-22 ant-form-item-control-wrapper">
                        <div class="ant-form-item-control ">
                            <input type="hidden" id="pid" name="pid" value="">
                            <button type="button" class="ant-btn ant-btn-primary ant-btn-lg" id="add_sure"><span>确定添加</span></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="amend_permission" class="ant-modal" style="width: 790px; transform-origin: 1054px 10px 0px;display: none">
    <div class="ant-modal-content">
        <button class="ant-modal-close" onclick="CloseDiv(&#39;amend_permission&#39;,&#39;mask&#39;)">
            <span class="ant-modal-close-x"></span>
        </button>
        <div class="ant-modal-header">
            <div class="ant-modal-title">编辑权限</div>
        </div>
        <div class="ant-modal-body">
            <form class="ant-form ant-form-horizontal">

                
                <div class="ant-row ant-form-item">
                    <div class="ant-col-6 ant-form-item-label">
                        <label class="ant-form-item-required">显示名称</label>
                    </div>
                    <div class="ant-col-16 ant-form-item-control-wrapper">
                        <div class="ant-form-item-control ">
                            <input type="text" id="amend_display_name" name="display_name" placeholder="请输入显示名称" class="input ant-input ant-input-lg" required="">
                            <span style="color: red;font-size: 12px;display: none">请输入显示名称</span>
                        </div>
                    </div>
                </div>
        

                <div class="ant-row ant-form-item modal-btn form-button" style="margin-top: 24px; text-align: center;">
                    <div class="ant-col-22 ant-form-item-control-wrapper">
                        <div class="ant-form-item-control ">

                            <input type="hidden" id="amend_id" name="id">
                            <button type="button" class="ant-btn ant-btn-primary ant-btn-lg" id="amend_sure"><span>确定修改</span></button>
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
<script src="{{asset('/user/js/jquery.min.js?v=2.1.4')}}"></script>

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<!-- <script src="{{asset('/user/js/layer.js')}}"></script> -->
<script>
    var token = localStorage.getItem("Usertoken");
    layui.use('layer', function(){
      var layer = layui.layer;
      
        //删除角色
        $('.permission_lists').on("click",".del_perm",function () {
            var permission_id = $(this).parent().attr('data-id');

            layer.confirm('确定删除此条信息吗?', function(index){
              
              $.ajax({
                url : "{{url('/api/role_permission/del_permission')}}",
                    data : {token:token,permission_id:permission_id},
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
    //--------------------------------------------------------------------- ---------------------
    $(document).ready(function () {
        render();
        function render() {
            var jsonDataArr = "";
            var jsonData="";        
            //获取数据 视图渲染           
            $.ajax({
                type: "post",
                url:  location.protocol+'//'+document.domain+"/api/role_permission/permission_list",
                async: false,
                data: {
                    token: token
                },
                dataType: "json",
                success: function (res) {
                    console.log(res);
                    jsonData = res;
                    var html = '';
                    html +='<ul class="lists">';
                    $.each(jsonData, function (i, v) { 
                        console.log(v);
                        for(var i=0;i<v.length;i++){
                            if(v[i].permission_id==undefined){

                            }else{
                                html +='<li class="item" data-id="'+v[i].permission_id+'"><span class="add_perm layui-icon layui-icon-add-1" title="新增子权限" ></span>'+v[i].display_name+'('+v[i].permission_id+')<span class="del_perm layui-icon layui-icon-delete" title="删除权限"></span>';

                                if(v[i].child){
                                    html += '<ul class="child-lists" style="overflow: hidden;background-color: #fff;margin-left: -20px;">'
                                    $.each(v[i].child, function (i2, v2) {
                                        html +='<li class="child-item" data-id="'+v2.permission_id+'">'+v2.display_name+'('+v2.permission_id+')<span class="del_perm layui-icon layui-icon-delete" title="删除权限"></span> | </li>';
                                        
                                    })
                                    html += '</ul>';
                                } 
                            }
                            
                        }                        
                        html += '</li>';
                    })
                    html += '</ul>';
                    $('.permission_lists').html(html);
                },
                error: function (err) {
                    // console.log(err)
                }
            });


            //添加角色
            $('#add_sure').click(function () {
                $.ajax({
                    type: "post",
                    url:  location.protocol+'//'+document.domain+"/api/role_permission/add_permission",
                    async: true,
                    dataType: 'json',
                    data: {
                        token: token,
                        pid:$('#pid').val(),
                        display_name:$('#display_name').val()
                    },
                    success: function (res) {
                        console.log(res);
                        // if(res.status==1){
                        //     var status=res.msg;
                        //     swal({title:status,showConfirmButton: false,type:"success"});
                        //     setTimeout("window.location.reload()",1000);
                        // }else{
                        //     var status=res.msg;
                        //     swal({title:status,timer: 1000,showConfirmButton: false,type:"error"});

                        // }
                    },
                    error: function (err) {
                        // console.log(err)
                    }
                });
 

            });
            $('#add-factor').click(function () {                
                $("#pid").val('0');
            });
            $('.add_perm').click(function () {
                var pid = $(this).parent().attr('data-id');
                ShowDiv('add_permission','mask');
                $("#pid").val(pid);
            });
            // 修改--展示
            $('.edit_perm').click(function () {
                var id = $(this).parent().attr('data-id');

                ShowDiv('amend_permission','mask');
                $.ajax({
                    type: "post",
                    url:  location.protocol+'//'+document.domain+"/api/role_permission/permission_list",
                    async: true,
                    dataType: 'json',
                    data: {
                        token: token,
                        permission_id: id
                    },
                    success: function (res) {   
                        // console.log(res);
                        $.each(res,function(i,v){                            
                            for( var i=0;i<v.length;i++){
                                $('#amend_name').val(v[i].name);
                                $('#amend_display_name').val(v[i].display_name);
                                $('#amend_id').val(id);
                                $('#amend_description').val(v[i].description);

                            }
                        });
                    },
                 
                });
            });
            // 修改
            $('#amend_sure').click(function () {
                $.ajax({
                    type: "post",
                    url:  location.protocol+'//'+document.domain+"/api/role/addPermission",
                    async: true,
                    dataType: 'json',
                    data: {
                        token: token,
                        up:'up',
                        permission_id: $('#amend_id').val(),
                        name: $('#amend_name').val(),
                        display_name: $('#amend_display_name').val(),
                        description: $('#amend_description').val(),
                    },
                    success: function (res) {
                        // console.log(res);
                        if(res.status==1){
                            var status=res.msg;
                            swal({title:status,showConfirmButton: false,type:"success"});
                            setTimeout("window.location.reload()",1000);
                        }else{
                            var status=res.msg;
                            swal({title:status,timer: 1000,showConfirmButton: false,type:"error"});

                        }
                    },
                    
                });

            });

            
        }

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
    //关闭弹出层
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



    $(".input").each(function() {
        $(this).click(function () {
            $(this).css({"border": "1px solid #d9d9d9"});
            $(this).next().hide()
        });
    });
</script>



</body></html>