<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>查看门店信息</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <style>
        .img{width:130px;height:90px;overflow: hidden;}
        .img img{width:100%;height:100%;}
        .layui-layer-nobg{width: none !important;}
        /*.layui-layer-content{width:600px;height:550px;}*/
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">法人信息</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-row layui-form" lay-filter="component-form-group">  
            <div class="layui-col-md6">              
                <div class="layui-form-item" style="width:500px;">
                    <label class="layui-form-label">法人姓名</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div> 
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证号</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
            </div>
            <div class="layui-col-md6">
                <div class="layui-form-item">                    
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;">
                        <div class="img sfz_z"><img data-type="test" src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">身份证正面</label>
                    </div>
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                        <div class="img sfz_f"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">身份证反面</label>
                    </div>
                </div> 
            </div>   
            </div>

        </div>
    </div>
    <div class="layui-card">
      <div class="layui-card-header">门店信息</div>
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-row layui-form">
            <div class="layui-col-md6"> 
                <div class="layui-form-item">
                    <label class="layui-form-label">门店名称</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">地址</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">入驻性质</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">门店分类</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
            </div>
            <div class="layui-col-md6"> 
                <div class="layui-form-item">                    
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;">
                        <div class="img mt"><img data-type="test" src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">门头</label>
                    </div>
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                        <div class="img store1"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">店内照1</label>
                    </div>
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                        <div class="img store2"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">店内照2</label>
                    </div>
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                        <div class="img store3"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">店内照3</label>
                    </div>
                </div> 
            </div>
        </div>        
      </div>
    </div>
    <div class="layui-card">
        <div class="layui-card-header">账户信息</div>
        <div class="layui-card-body layui-row layui-col-space10">
            <div class="layui-row layui-form">
                <div class="layui-col-md6"> 
                    <div class="layui-form-item">
                        <label class="layui-form-label">银行卡号</label>
                        <div class="layui-input-block">
                            <div class="layui-form-mid"></div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">银行户主</label>
                        <div class="layui-input-block">
                            <div class="layui-form-mid"></div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">卡类型</label>
                        <div class="layui-input-block">
                            <div class="layui-form-mid"></div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">所属银行</label>
                        <div class="layui-input-block">
                            <div class="layui-form-mid"></div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">所属支行</label>
                        <div class="layui-input-block">
                            <div class="layui-form-mid"></div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">联行号</label>
                        <div class="layui-input-block">
                            <div class="layui-form-mid"></div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">支付宝账户</label>
                        <div class="layui-input-block">
                            <div class="layui-form-mid"></div>
                        </div>
                    </div>
                </div>
                <div class="layui-col-md6"> 
                    <div class="layui-form-item">                    
                        <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;">
                            <div class="img bank_z"><img data-type="test" src="{{asset('/school/images/zanwu.png')}}"></div>
                            <label class="layui-form-label">银行卡正面</label>
                        </div>
                        <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                            <div class="img bank_f"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                            <label class="layui-form-label">银行卡反面</label>
                        </div>                        
                    </div> 
                </div>
            </div>   
      </div>
    </div>
    <div class="layui-card">
      <div class="layui-card-header">证件照信息</div>
      <div class="layui-card-body layui-row layui-col-space10">
         <div class="layui-row layui-form">
            <div class="layui-col-md6"> 
                <div class="layui-form-item">
                    <label class="layui-form-label">营业执照编号</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">营业执照过期时间</label>
                    <div class="layui-input-block">
                        <div class="layui-form-mid"></div>
                    </div>
                </div>                
            </div>
            <div class="layui-col-md6"> 
                <div class="layui-form-item">                    
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;">
                        <div class="img license1"><img data-type="test" src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">营业执照</label>
                    </div>
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                        <div class="img license2"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">开户许可证</label>
                    </div>
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                        <div class="img license3"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">其他照片1</label>
                    </div>
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                        <div class="img license4"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">其他照片2</label>
                    </div>
                    <div class="layui-input-block" style="width:130px;overflow: hidden;display: inline-block;margin-left:20px;">
                        <div class="img license5"><img src="{{asset('/school/images/zanwu.png')}}"></div>
                        <label class="layui-form-label">其他照片3</label>
                    </div>
                </div> 
            </div>
        </div>     
      </div>
    </div>
    
</div>


<!-- 第一部分 -->
<div id="sfz_z" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div>       
<div id="sfz_f" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div> 
<!-- 第二部分 -->
<div id="mt" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div>       
<div id="store1" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div> 
<div id="store2" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div>       
<div id="store3" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div> 
<!-- 第三部分 -->
<div id="bank_z" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div>       
<div id="bank_f" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div> 
<!-- 第四部分 -->
<div id="license1" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div>       
<div id="license2" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div> 
<div id="license3" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div>       
<div id="license4" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div> 
<div id="license5" class="hide" style="display: none"><img style="width:100%;height:100%" src="{{asset('/school/images/zanwu.png')}}"></div> 




<input type="hidden" class="schooltypeid" value="">
<input type="hidden" class="gradeid" value="">
<input type="hidden" class="classid" value="">
<input type="hidden" class="statusid" value="">
<input type="hidden" class="relationshipid" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Publictoken");
    var str=location.search;
    var store_id=str.split('?')[1];

    layui.config({
        base: '../../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index', //主入口模块
        formSelects: 'formSelects'
    }).use(['index', 'form','upload','formSelects'], function(){
        var $ = layui.$
            ,admin = layui.admin
            ,element = layui.element
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,form = layui.form
            ,upload = layui.upload
            ,formSelects = layui.formSelects;
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/mb/login')}}"; 
            }
        })   
        $.post("{{url('/api/merchant/store')}}",
        {
            token:token,
            store_id:store_id         

        },function(res){
            console.log(res);
            if(res.status==1){
                // 一部分
                $('.layui-row .layui-form-item').eq(0).find('.layui-form-mid').html(res.data.head_info.head_name);
                $('.layui-row .layui-form-item').eq(1).find('.layui-form-mid').html(res.data.head_info.head_sfz_no);
                // 二部分
                $('.layui-row .layui-form-item').eq(3).find('.layui-form-mid').html(res.data.store_info.store_name);
                $('.layui-row .layui-form-item').eq(4).find('.layui-form-mid').html(res.data.store_info.province_name+res.data.store_info.city_name+res.data.store_info.area_name+res.data.store_info.store_address);
                $('.layui-row .layui-form-item').eq(5).find('.layui-form-mid').html(res.data.store_info.store_type_name);
                $('.layui-row .layui-form-item').eq(6).find('.layui-form-mid').html(res.data.store_info.category_name);
                // 三部分
                $('.layui-row .layui-form-item').eq(8).find('.layui-form-mid').html(res.data.account_info.store_bank_no);
                $('.layui-row .layui-form-item').eq(9).find('.layui-form-mid').html(res.data.account_info.store_bank_name);
                if(res.data.account_info.store_bank_type=='01'){
                    $('.layui-row .layui-form-item').eq(10).find('.layui-form-mid').html('私人');
                }else{
                    $('.layui-row .layui-form-item').eq(10).find('.layui-form-mid').html('对公');
                }
                
                $('.layui-row .layui-form-item').eq(11).find('.layui-form-mid').html(res.data.account_info.bank_name);
                $('.layui-row .layui-form-item').eq(12).find('.layui-form-mid').html(res.data.account_info.sub_bank_name);
                $('.layui-row .layui-form-item').eq(13).find('.layui-form-mid').html(res.data.account_info.bank_no);//联行号
                $('.layui-row .layui-form-item').eq(14).find('.layui-form-mid').html(res.data.account_info.store_alipay_account);//支付宝账户
                // 四部分
                $('.layui-row .layui-form-item').eq(16).find('.layui-form-mid').html(res.data.license_info.store_license_no);
                $('.layui-row .layui-form-item').eq(17).find('.layui-form-mid').html(res.data.license_info.store_license_time);


                //图片第一部分
                if(res.data.head_info.head_sfz_img_a!=''){
                    $('.sfz_z img').attr('src',res.data.head_info.head_sfz_img_a);
                    $('#sfz_z img').attr('src',res.data.head_info.head_sfz_img_a);
                }
                if(res.data.head_info.head_sfz_img_b!=''){
                    $('.sfz_f img').attr('src',res.data.head_info.head_sfz_img_b);
                    $('#sfz_f img').attr('src',res.data.head_info.head_sfz_img_b);
                }               
                
                //***第二部分图片
                if(res.data.store_info.store_logo_img!=''){
                    $('.mt img').attr('src',res.data.store_info.store_logo_img);
                    $('#mt img').attr('src',res.data.store_info.store_logo_img);
                }
                if(res.data.store_info.store_img_a=='' || res.data.store_info.store_img_a=='undefined'){
                    $('.store1 img').attr('src',"{{asset('/school/images/zanwu.png')}}");
                    $('#store1 img').attr('src',"{{asset('/school/images/zanwu.png')}}");                    
                }else{
                    $('.store1 img').attr('src',res.data.store_info.store_img_a);
                    $('#store1 img').attr('src',res.data.store_info.store_img_a);
                }
                if(res.data.store_info.store_img_b!=''){
                    $('.store2 img').attr('src',res.data.store_info.store_img_b);
                    $('#store2 img').attr('src',res.data.store_info.store_img_b);
                }
                if(res.data.store_info.store_img_c!=''){
                    $('.store3 img').attr('src',res.data.store_info.store_img_c);
                    $('#store3 img').attr('src',res.data.store_info.store_img_c);
                }                
                //**第三部分图片
                if(res.data.account_info.bank_img_a!=''){
                    $('.bank_z img').attr('src',res.data.account_info.bank_img_a);
                    $('#bank_z img').attr('src',res.data.account_info.bank_img_a);
                }                
                if(res.data.account_info.bank_img_b!=''){
                    $('.bank_f img').attr('src',res.data.account_info.bank_img_b);
                    $('#bank_f img').attr('src',res.data.account_info.bank_img_b);
                }

                //**第四部分图片
                if(res.data.license_info.store_license_img!=''){
                    $('.license1 img').attr('src',res.data.license_info.store_license_img);
                    $('#license1 img').attr('src',res.data.license_info.store_license_img);
                }
                if(res.data.license_info.store_industrylicense_img!=''){
                    $('.license2 img').attr('src',res.data.license_info.store_industrylicense_img);
                    $('#license2 img').attr('src',res.data.license_info.store_industrylicense_img);
                }
                if(res.data.license_info.store_other_img_a!=''){
                    $('.license3 img').attr('src',res.data.license_info.store_other_img_a);
                    $('#license3 img').attr('src',res.data.license_info.store_other_img_a);
                }
                if(res.data.license_info.store_other_img_b!=''){
                    $('.license4 img').attr('src',res.data.license_info.store_other_img_b);
                    $('#license4 img').attr('src',res.data.license_info.store_other_img_b);
                }
                if(res.data.license_info.store_other_img_c!=''){
                    $('.license5 img').attr('src',res.data.license_info.store_other_img_c);
                    $('#license5 img').attr('src',res.data.license_info.store_other_img_c);
                }

            }else{
                layer.msg(res.message, {
                    offset: '15px'
                    ,icon: 2
                    ,time: 1000
                });
            }
        },"json");
        $('.sfz_z').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#sfz_z')
            });
        });
        $('.sfz_f').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#sfz_f')
            });
        });
        $('.mt').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#mt')
            });
        });
        $('.store1').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#store1')
            });
        });
        $('.store2').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#store2')
            });
        });
        $('.store3').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#store3')
            });
        });
        $('.bank_z').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#bank_z')
            });
        });
        $('.bank_f').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#bank_f')
            });
        });
        $('.license1').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#license1')
            });
        });
        $('.license2').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#license2')
            });
        });
        $('.license3').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#license3')
            });
        });
        $('.license4').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#license4')
            });
        });
        $('.license5').on("click",function(){
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: '516px',
              skin: 'layui-layer-nobg', //没有背景色
              shadeClose: true,
              content: $('#license5')
            });
        });







        var active = {
            test: function(){
               layer.alert('你好，体验者');
            }
        }
        $('#LAY-component-layer-list .layadmin-layer-demo .layui-btn').on('click', function(){
          var type = $(this).data('type');
          active[type] && active[type].call(this);
        });

    });
</script>
</body>
</html>
