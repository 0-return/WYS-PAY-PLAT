<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>修改门店信息</title>
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
        .layui-card-header{width:80px;text-align: right;float:left;}
        .layui-card-body{margin-left:28px;}
        .layui-upload-img{width: 100px; height: 92px; /*margin: 0 10px 10px 0;*/}

        .up{position: relative;display: inline-block;cursor: pointer;border-color: #1ab394; color: #FFF;width: auto !important;font-size: 10px !important;text-align: center !important;}
        .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
        .layui-upload-list{width: 100px;height:96px;overflow: hidden;margin: 10px auto;}
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {-webkit-appearance: none !important;margin: 0;}


        .userbox{
          height:200px;
          overflow-y: auto;
          z-index: 999;
          position: absolute;
          left: 0px;
          top: 85px;
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
    
    <div class="layui-card">
      <div class="layui-card-header">门店信息</div>
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-row layui-form">
            <div class="layui-col-md6"> 
                <div class="layui-form-item xingzhi">
                    <label class="layui-form-label">入驻性质</label>
                    <div class="layui-input-block">
                        <select name="xingzhi" id="xingzhi" lay-filter="xingzhi">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item storeitem">
                    <label class="layui-form-label">门店分类</label>
                    <div class="layui-input-block">
                        <select name="storeitem" id="storeitem" lay-filter="storeitem">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">营业执照名称</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入门店名称" class="layui-input item3">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">门店简称</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入门店简称" class="layui-input item17">
                    </div>
                </div>
                <div class="layui-form-item js_phone">
                    <label class="layui-form-label">联系人</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入联系人" class="layui-input store_people">
                    </div>
                </div>
                <div class="layui-form-item js_phone">
                    <label class="layui-form-label">联系人手机号</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入联系人手机号" class="layui-input phone">
                    </div>
                </div>
                <div class="layui-form-item js_phone">
                    <label class="layui-form-label">联系人微信名</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入联系人微信名" class="layui-input wechatname">
                    </div>
                </div>
                <div class="layui-form-item js_phone">
                    <label class="layui-form-label">联系人微信号</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入联系人微信号" class="layui-input wechatno">
                    </div>
                </div>
                <div class="layui-form-item js_phone">
                    <label class="layui-form-label">邮箱</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入联系人邮箱" class="layui-input email">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">商户所在地</label>
                    <div class="layui-input-block addressall">
                        <div class="layui-inline">
                            <select name="province" lay-filter="filterProvince" id="province">
                                
                            </select>
                        </div>
                        <div class="layui-inline">
                            <select name="city" lay-filter="filterCity" id="city">
                                
                            </select>
                        </div>
                        <div class="layui-inline">
                            <select name="area" lay-filter="filterArea" id="area">
                                
                            </select>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">商户详细地址</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入地址" class="layui-input item4">
                    </div>
                </div>
                
            </div>
            <div class="layui-col-md6"> 
                <div class="layui-card">                        
                    <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                        <div class="layui-upload">
                            <button class="layui-btn up"><input type="file" name="img_upload" class="test3">上传门头</button>
                            <div class="layui-upload-list">
                               <img class="layui-upload-img" id="demo3">
                               <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                        <div class="layui-upload">
                            <button class="layui-btn up"><input type="file" name="img_upload" class="test4">收银台照</button>
                            <div class="layui-upload-list">
                               <img class="layui-upload-img" id="demo4">
                               <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                        <div class="layui-upload">
                            <button class="layui-btn up"><input type="file" name="img_upload" class="test5">经营内容照</button>
                            <div class="layui-upload-list">
                               <img class="layui-upload-img" id="demo5">
                               <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                    <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                        <div class="layui-upload">
                            <button class="layui-btn up"><input type="file" name="img_upload" class="test6">店内全景照</button>
                            <div class="layui-upload-list">
                               <img class="layui-upload-img" id="demo6">
                               <p id="demoText"></p>
                            </div>
                        </div>
                    </div>
                </div>                
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block" style="margin-left:0;">
                    <div class="layui-footer" style="left: 0;">
                        <button class="layui-btn submit2">保存</button>
                    </div>
                </div>
            </div>
        </div>        
      </div>
    </div>
    <div class="layui-card">
        <div class="layui-card-header">法人信息</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-row layui-form" lay-filter="component-form-group">  
                <div class="layui-col-md6">              
                    <div class="layui-form-item">
                        <label class="layui-form-label">法人姓名</label>
                        <div class="layui-input-block">
                            <input type="text" placeholder="请输入法人姓名" class="layui-input item1">
                        </div>
                    </div> 
                    <div class="layui-form-item">
                        <label class="layui-form-label">法人身份证号码</label>
                        <div class="layui-input-block">
                            <input type="text" placeholder="请输入身份证号" class="layui-input item2">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">法人身份证开始时间</label>
                        <div class="layui-inline">                          
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input frs-item test-item item18" placeholder="法人身份证开始时间" lay-key="21">
                            </div>
                        </div>
                    </div> 
                    <div class="layui-form-item">
                        <label class="layui-form-label">法人身份证过期时间</label>
                        <div class="layui-inline">                          
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input fre-item test-item item16" placeholder="法人身份证过期时间" lay-key="22">
                            </div>
                        </div>
                    </div>                    
                </div>
                <div class="layui-col-md6">                
                    <div class="layui-form-item">
                        <div class="layui-card">                        
                            <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test1">上传身份证正面</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo1">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload"">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test2">上传身份证反面</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo2">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div> 
                </div>   
                <div class="layui-form-item">
                    <div class="layui-input-block" style="margin-left:0;">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit1">保存</button>
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
                    <div class="layui-form-item cardtype">
                        <label class="layui-form-label">卡类型</label>
                        <div class="layui-input-block">
                            <select name="cardtype" id="cardtype" lay-filter="cardtype">
                                <option value="">选择卡类型</option>
                                <option value="01">私人</option>
                                <option value="02">对公</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">银行卡号</label>
                        <div class="layui-input-block">
                            <input type="text" placeholder="请输入银行卡号" class="layui-input item7">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">银行户主</label>
                        <div class="layui-input-block">
                            <input type="text" placeholder="请输入银行户主" class="layui-input item8">
                        </div>
                    </div>                    
                    

                    <div class="layui-form-item">
                        <label class="layui-form-label">所属地区</label>
                        <div class="layui-input-block addressall">
                            <div class="layui-inline">
                                <select name="provincebank" lay-filter="filterProvincebank" id="provincebank">
                                    
                                </select>
                            </div>
                            <div class="layui-inline">
                                <select name="citybank" lay-filter="filterCitybank" id="citybank">
                                    
                                </select>
                            </div>
                            <div class="layui-inline">
                                <select name="areabank" lay-filter="filterAreabank" id="areabank">
                                    
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <label class="layui-form-label">所属银行</label>
                        <div class="layui-input-block js_bank">
                            <select name="bank" id="bank" lay-filter="bank" lay-search>
                                
                            </select>

                            <input type="text" name="schoolname" lay-verify="schoolname" autocomplete="off" placeholder="搜索所属银行" class="layui-input transfer" style='margin-top:10px;'>

                            <div class="userbox" style='display: none'></div>
                            
                        </div>
                        
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">所属支行</label>
                        <div class="layui-input-block subbank">
                            <select name="subbank" id="subbank" lay-filter="subbank" lay-search>
                                
                            </select>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">联行号</label>
                        <div class="layui-input-block">
                            <input type="text" placeholder="请输入联行号" class="layui-input item12" disabled="">
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">支付宝账户</label>
                        <div class="layui-input-block">
                            <input type="text" placeholder="" class="layui-input item13" disabled="">
                        </div>
                    </div>

                    <div class="layui-form-item different">
                        <label class="layui-form-label">持卡人身份证开始时间</label>
                        <div class="layui-inline">                          
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input test-item haves-item item20" placeholder="持卡人身份证开始时间" lay-key="25">
                            </div>
                        </div>
                    </div> 
                    <div class="layui-form-item different">
                        <label class="layui-form-label">持卡人身份证过期时间</label>
                        <div class="layui-inline">                          
                            <div class="layui-input-inline">
                                <input type="text" class="layui-input test-item havee-item item21" placeholder="持卡人身份证过期时间" lay-key="26">
                            </div>
                        </div>
                    </div> 
                </div>
                <div class="layui-col-md6"> 
                    <div class="layui-form-item">
                        <div class="layui-card">                        
                            <div class="layui-card-body bank" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test7">上传银行卡正面</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo7">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-card-body bank" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test8">上传银行卡反面</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo8">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-card-body bank" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test17">手持身份证正面</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo17">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-card-body different" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test14">持卡人身份证正面</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo14">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-card-body different" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test15">持卡人身份证反面</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo15">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="layui-card-body different" style="margin-left:28px;padding:0 15px;float:left;">
                                <div class="layui-upload">
                                    <button class="layui-btn up"><input type="file" name="img_upload" class="test16">授权结算书</button>
                                    <div class="layui-upload-list">
                                       <img class="layui-upload-img" id="demo16">
                                       <p id="demoText"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div>  
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block" style="margin-left:0;">
                        <div class="layui-footer" style="left: 0;">
                            <button class="layui-btn submit3">保存</button>
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
                <div class="layui-form-item per_lincse">
                    <label class="layui-form-label">营业执照编号</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入营业执照编号" class="layui-input item14">
                    </div>
                </div>
                <div class="layui-form-item per_lincse">
                    <label class="layui-form-label">营业执照开始时间</label>
                    <div class="layui-inline">                          
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input end-item test-item item22" placeholder="营业执照开始时间" lay-key="24">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item per_lincse">
                    <label class="layui-form-label">营业执照过期时间</label>
                    <div class="layui-inline">                          
                        <div class="layui-input-inline">
                            <input type="text" class="layui-input start-item test-item item15" placeholder="营业执照过期时间" lay-key="23">
                        </div>
                    </div>
                </div> 
                                
            </div>
            <div class="layui-col-md6"> 
                
                <div class="layui-form-item"> 
                    <div class="layui-card">                        
                        <div class="layui-card-body public" style="margin-left:28px;padding:0 15px;float:left;">
                            <div class="layui-upload">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test9">上传营业执照</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo9">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <div class="layui-card-body public" style="margin-left:28px;padding:0 15px;float:left;">
                            <div class="layui-upload"">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test10">上传开户许可证</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo10">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <div class="layui-card-body per" style="margin-left:28px;padding:0 15px;float:left;">
                            <div class="layui-upload"">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test11">手持身份证照</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo11">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <div class="layui-card-body per" style="margin-left:28px;padding:0 15px;float:left;">
                            <div class="layui-upload"">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test12">人站在门口照</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo12">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="layui-card-body" style="margin-left:28px;padding:0 15px;float:left;">
                            <div class="layui-upload"">
                                <button class="layui-btn up"><input type="file" name="img_upload" class="test13">上传其他照片3</button>
                                <div class="layui-upload-list">
                                   <img class="layui-upload-img" id="demo13">
                                   <p id="demoText"></p>
                                </div>
                            </div>
                        </div> -->
                    </div>
                    
                </div> 
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block" style="margin-left:0;">
                    <div class="layui-footer" style="left: 0;">
                        <button class="layui-btn submit4">保存</button>
                    </div>
                </div>
            </div>
        </div>     
      </div>
    </div>
    
</div>




<!-- 商户性质 -->
<input type="hidden" class="store_name" value="">
<input type="hidden" class="store_type" value="">
<input type="hidden" class="category_name" value="">
<input type="hidden" class="category_id" value="">
<!-- 卡类型 -->
<input type="hidden" class="cardtype_id" value="">
<!-- 银行卡 -->
<input type="hidden" class="bankname" value="">
<input type="hidden" class="sub_bank_name" value="">
<input type="hidden" class="bank_no" value="">
<!-- 地区 -->
<input type="hidden" class="provincecode" value="">
<input type="hidden" class="provincename" value="">
<input type="hidden" class="citycode" value="">
<input type="hidden" class="cityname" value="">
<input type="hidden" class="areacode" value="">
<input type="hidden" class="areaname" value="">

<!-- 银行卡卡户地区 -->
<input type="hidden" class="provincecodebank" value="">
<input type="hidden" class="provincenamebank" value="">
<input type="hidden" class="citycodebank" value="">
<input type="hidden" class="citynamebank" value="">
<input type="hidden" class="areacodebank" value="">
<input type="hidden" class="areanamebank" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("Usertoken");
    var store_id = localStorage.getItem("store_store_id");

    var province_code = localStorage.getItem("store_province_code");
    var city_code = localStorage.getItem("store_city_code");
    var area_code = localStorage.getItem("store_area_code");

    var str=location.search;
    var store_id_add=str.split('?')[1];
    console.log(store_id_add)
   

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
            ,upload = layui.upload
            ,formSelects = layui.formSelects;
        // 未登录,跳转登录页面
        $(document).ready(function(){        
            if(token==null){
                window.location.href="{{url('/user/login')}}"; 
            }
        })
        $(".transfer").bind("input propertychange",function(event){
          console.log($(this).val())  


          $.post("{{url('/api/basequery/bank')}}",
          {
              token:token,
              bankname:$(this).val(),                        

          },function(res){
              console.log(res);
              var html="";

              for(var i=0;i<res.data.length;i++){
                  html+='<div class="list" data='+res.data[i].bankname+'>'+res.data[i].bankname+'</div>';
              }
              $(".userbox").show()
              $('.userbox').html('')
              $('.userbox').append(html)
              
          },"json");
        });

        $(".userbox").on("click",".list",function(){

            $('.userbox').hide()

            $(".transfer").val('')
            var bankname=$(this).attr('data')
            $('.bankname').val(bankname); 

            // $("#bank").html('');
            // $("#bank").append('<option value=" ">'+bankname+'</option>');
            // layui.form.render('select');  

            $.post("{{url('/api/basequery/bank')}}",
            {
                token:token,
                bankname:$(this).attr('data'),
            },function(data){
                console.log(data);
                if(data.status==1){
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].bankname + "' "+((bankname==data.data[i].bankname)?"selected":"")+">" + data.data[i].bankname + "</option>";
                    }    
                    $("#bank").append('<option value="">请选择银行</option>'+optionStr);
                    layui.form.render('select');                        
                }

            },"json");



          $.post("{{url('/api/basequery/sub_bank')}}",
            {
                token:token,
                bank_name:$(this).attr('data'),
                l:'500'
            },function(data){
                console.log(data);
                if(data.status==1){
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].bank_no + "'>"+ data.data[i].sub_bank_name + "</option>";
                    }    
                    $("#subbank").html('');
                    $("#subbank").append('<option value="">选择所属支行</option>'+optionStr);
                    layui.form.render('select');                       
                }

            },"json"); 
        })
       
        $('#searchbank').bind('input propertychange', function() { 
            console.log($(this).val())
            $.post("{{url('/api/basequery/sub_bank')}}",
            {
                token:token,
                
                l:'500'
            },function(data){
                console.log(data);
                if(data.status==1){
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].bank_no + "'>"+ data.data[i].sub_bank_name + "</option>";
                    }    
                    $("#subbank").html('');
                    $("#subbank").append('<option value="">选择所属支行</option>'+optionStr);
                    layui.form.render('select');                       
                }

            },"json");
        });
        // 地区star------------------------------------------------------ 
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
                    $("#provincebank").append('<option value="">请选择省</option>'+optionStr);
                    layui.form.render('select');
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });
        
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
        });
        form.on('select(filterArea)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.areacode').val(category);
            $('.areaname').val(categoryName);           
        });
        // 银行卡开户地区分界线-----------------------
        form.on('select(filterProvincebank)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.provincecodebank').val(category);
            $('.provincenamebank').val(categoryName);
            $("#citybank").html('');
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
                        $("#citybank").append('<option value="">请选择市</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
        });
        form.on('select(filterCitybank)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.citycodebank').val(category);
            $('.citynamebank').val(categoryName);
            $("#areabank").html('');
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
                        $("#areabank").append('<option value="">请选择县/区</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
        });
        form.on('select(filterAreabank)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.areacodebank').val(category);
            $('.areanamebank').val(categoryName);           
        });
        // 性质
        form.on('select(xingzhi)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.store_type').val(category);    
            $('.store_name').val(categoryName);    
            if(category == 1){
                $('.bank').show()                
                $('.per').hide()
                $('.cardtype').show()
                $('.public').show()
                $('.per_lincse').show()

                if($('.cardtype_id').val()=='01'){
                    if($(".item1").val() == $('.item8').val()){
                        $('.different').hide()
                    }else{
                        $('.different').show()
                    } 
                }else{
                    $('.different').hide()
                    $('.bank').hide()
                }


                $('#cardtype option').each(function(){    
                    console.log($(this).val())                    
                    if($(this).val()==='01'){
                        $(this).attr('selected','selected');
                    }
                    $('.cardtype_id').val('01')
                });
                layui.form.render('select');
                // *******
                if($('.cardtype_id').val() == '01'){
                    if($(".item1").val() == $('.item8').val()){
                        $('.different').hide()
                    }else{
                        $('.different').show()
                    }
                    $('.bank').show()
                }else if($('.cardtype_id').val() == '02'){
                    if($(".item1").val() == $('.item8').val()){
                        $('.different').hide()
                    }else{
                        $('.different').show()
                    }
                    $('.bank').show()
                }


                
                $('.bank').show()

                
                
            }else if(category == 2){
                
                $('.per').hide()
                $('.cardtype').show()
                $('.public').show() 
                $('.per_lincse').show()

                if($('.cardtype_id').val()=='01'){
                    if($(".item1").val() == $('.item8').val()){
                        $('.different').hide()
                    }else{
                        $('.different').show()
                    } 
                }else{
                    $('.bank').hide()
                }     
                       
                $('#cardtype option').each(function(){                        
                    if('02'==$(this).val()){
                        $(this).attr('selected','selected');
                    }
                    $('.cardtype_id').val('02')
                });
                layui.form.render('select');

                $('.bank').hide()
                $('.different').hide()

            }else{
                $('.per').show()
                $('.public').hide() 
                $('.cardtype').hide()  

                $('.different').hide()
                $('.per_lincse').hide()
            }
            
        });
        // 卡类型
        form.on('select(cardtype)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.cardtype_id').val(category);
            if(category=='01'){//对私
                if($('.store_type').val() == 1){
                    if($(".item1").val() == $('.item8').val()){
                        $('.different').hide()
                    }else{
                        $('.different').show()
                    }
                    $('.bank').show()
                }else if($('.store_type').val() == 2){
                    if($(".item1").val() == $('.item8').val()){
                        $('.different').hide()
                    }else{
                        $('.different').show()
                    }
                    $('.bank').show()
                }
                $('.bank').show()
            }else{//对公
                $('.bank').hide()
                $('.different').hide()
            }
        });
        // 选择银行卡
        form.on('select(bank)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.bankname').val(category);   
            $.post("{{url('/api/basequery/sub_bank')}}",
            {
                token:token,
                bank_name:$('.bankname').val(),
                bank_province_name:$('.provincenamebank').val(),
                bank_city_name:$('.citynamebank').val(),
                bank_area_name:$('.areanamebank').val(),
                l:'500'
            },function(data){
                console.log(data);
                if(data.status==1){
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].bank_no + "'>"+ data.data[i].sub_bank_name + "</option>";
                    }    
                    $("#subbank").html('');
                    $("#subbank").append('<option value="">选择所属支行</option>'+optionStr);
                    layui.form.render('select');                       
                }

            },"json");       
        });
         // 选择支行
        form.on('select(subbank)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.item12').val(category);
            $('.sub_bank_name').val(categoryName);
        });
        // 选择商户经营性质
        form.on('select(storeitem)', function(data){            
            category = data.value;  
            categoryName = data.elem[data.elem.selectedIndex].text; 
            $('.category_id').val(category);
            $('.category_name').val(categoryName);
        });
        
        // 地区end------------------------------------------------------  

        // 输入名称是否一致******************************** 
        $(".item8").bind('input propertychange', function () {
            console.log($('.store_type').val())
            console.log($('.cardtype_id').val())

            if($(".item1").val() == $(this).val()){
                if($('.store_type').val()==1 && $('.cardtype_id').val()=='01'){
                    $('.different').hide()
                }else if($('.store_type').val()==1 && $('.cardtype_id').val()=='02'){
                    $('.different').hide()
                }
                if($('.store_type').val()==2 && $('.cardtype_id').val()=='01'){
                    $('.different').hide()
                }else if($('.store_type').val()==2 && $('.cardtype_id').val()=='02'){
                    $('.different').hide()
                }
                
            }else{
                if($('.store_type').val()==1 && $('.cardtype_id').val()=='01'){
                    $('.different').show()
                }else if($('.store_type').val()==1 && $('.cardtype_id').val()=='02'){
                    $('.different').hide()
                }
                if($('.store_type').val()==2 && $('.cardtype_id').val()=='01'){
                    $('.different').show()
                }else if($('.store_type').val()==2 && $('.cardtype_id').val()=='02'){
                    $('.different').hide()
                }
            }

        });
        // 用户信息star------------------------------------------------------ 

        if(store_id_add==0){
            console.log('不执行')
            $('.js_phone').show()
            nostore()
        }else{
            $('.js_phone').show()
            getBoards();
        }

        function nostore(){
            // 商户类型查询
            $.ajax({
                url : "{{url('/api/basequery/store_type')}}",
                data : {token:token},
                type : 'get',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){

                            optionStr += "<option value='" + data.data[i].store_type + "'>"
                            + data.data[i].store_type_desc + "</option>";
                        }    
                        $("#xingzhi").append('<option value="">请选择商户类型</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });
            // 商户经营性质
            $.ajax({
                url : "{{url('/api/basequery/store_category')}}",
                data : {token:token},
                type : 'get',
                success : function(data) {
                    // console.log(data);
                    var optionStr = "";
                        for(var i=0;i<data.data.length;i++){

                            optionStr += "<option value='" + data.data[i].category_id + "'>"
                            + data.data[i].category_name + "</option>";
                        }    
                        $("#storeitem").append('<option value="">请选择商户经营性质</option>'+optionStr);
                        layui.form.render('select');
                },
                error : function(data) {
                    alert('查找板块报错');
                }
            });

            // 所属银行                
            $.post("{{url('/api/basequery/bank')}}",
            {
                token:token
            },function(data){
                // console.log(data);
                if(data.status==1){
                    var optionStr = "";
                    for(var i=0;i<data.data.length;i++){

                        optionStr += "<option value='" + data.data[i].bankname + "'>" + data.data[i].bankname + "</option>";
                    }    
                    $("#bank").append('<option value="">请选择银行</option>'+optionStr);
                    layui.form.render('select');                        
                }

            },"json");

        }
        
           
        function getBoards(){   
            $.post("{{url('/api/user/store')}}",
            {
                token:token,
                store_id:store_id
            },function(res){
                console.log(res);
                if(res.status==1){
                    // 一部分+++++++++++++++++++++++++++++++++++
                    $('.item1').val(res.data.head_info.head_name);
                    $('.item2').val(res.data.head_info.head_sfz_no);
                    $('.item16').val(res.data.head_info.head_sfz_time);
                    $('.item18').val(res.data.head_info.head_sfz_stime);
                    // 二部分+++++++++++++++++++++++++++++++
                    $('.item3').val(res.data.store_info.store_name);
                    $('.item17').val(res.data.store_info.store_short_name);
                    $('.store_people').val(res.data.store_info.people);
                    $('.phone').val(res.data.store_info.people_phone);
                    $('.email').val(res.data.store_info.store_email);
                    $('.wechatname').val(res.data.account_info.weixin_name);
                    $('.wechatno').val(res.data.account_info.weixin_no);
                    
                    // 地区渲染
                   
                    $('.provincecode').val(res.data.store_info.province_code);
                    $('.citycode').val(res.data.store_info.city_code);
                    $('.areacode').val(res.data.store_info.area_code);
                    // 省
                    $.ajax({
                        url : "{{url('/api/basequery/city')}}",
                        data : {area_code:'1'},
                        type : 'get',
                        success : function(data) {
                            // console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){
                                    optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.store_info.province_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                                }    
                                $("#province").append('<option value="">请选择省</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            alert('查找板块报错');
                        }
                    });
                    // 市
                    $.ajax({
                        url : "{{url('/api/basequery/city')}}",
                        data : {area_code:$('.provincecode').val()},
                        type : 'get',
                        success : function(data) {
                            // console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){
                                    optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.store_info.city_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                                }    
                                $("#city").append('<option value="">请选择市</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            alert('查找板块报错');
                        }
                    });
                    // 区
                    $.ajax({
                        url : "{{url('/api/basequery/city')}}",
                        data : {area_code:$('.citycode').val()},
                        type : 'get',
                        success : function(data) {
                            // console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){
                                    optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.store_info.area_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                                }    
                                $("#area").append('<option value="">请选择区</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            alert('查找板块报错');
                        }
                    });

                    $('.item4').val(res.data.store_info.store_address);
                    // 商户类型查询
                    $.ajax({
                        url : "{{url('/api/basequery/store_type')}}",
                        data : {token:token},
                        type : 'get',
                        success : function(data) {
                            // console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){

                                    optionStr += "<option value='" + data.data[i].store_type + "' "+((res.data.store_info.store_type==data.data[i].store_type)?"selected":"")+">"
                                    + data.data[i].store_type_desc + "</option>";
                                }    
                                $("#xingzhi").append('<option value="">请选择商户类型</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            alert('查找板块报错');
                        }
                    });
                    $('.store_type').val(res.data.store_info.store_type);
                    $('.store_name').val(res.data.store_info.store_type_name);
                    // 商户经营性质
                    $.ajax({
                        url : "{{url('/api/basequery/store_category')}}",
                        data : {token:token},
                        type : 'get',
                        success : function(data) {
                            // console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){

                                    optionStr += "<option value='" + data.data[i].category_id + "' "+((res.data.store_info.category_id==data.data[i].category_id)?"selected":"")+">"
                                    + data.data[i].category_name + "</option>";
                                }    
                                $("#storeitem").append('<option value="">请选择商户经营性质</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            alert('查找板块报错');
                        }
                    });
                    $('.category_id').val(res.data.store_info.category_id);
                    $('.category_name').val(res.data.store_info.category_name);
                    $('.item5').val(res.data.store_info.store_type_name);
                    $('.item6').val(res.data.store_info.category_name);
                    // 三部分++++++++++++++++++++++++
                    $('.item7').val(res.data.account_info.store_bank_no);
                    $('.item8').val(res.data.account_info.store_bank_name);                
                    
                    $('.cardtype_id').val(res.data.account_info.store_bank_type);
                    // 账户信息
                    $('#cardtype option').each(function(){                        
                        if(res.data.account_info.store_bank_type==$(this).val()){
                            $(this).attr('selected','selected');
                        }
                    });
                    $('.bankname').val(res.data.account_info.bank_name)
                    // 地区渲染
                   
                    $('.provincecodebank').val(res.data.account_info.bank_province_code);
                    $('.citycodebank').val(res.data.account_info.bank_city_code);
                    $('.areacodebank').val(res.data.account_info.bank_area_code);
                    // 省
                    $.ajax({
                        url : "{{url('/api/basequery/city')}}",
                        data : {area_code:'1'},
                        type : 'get',
                        success : function(data) {
                            // console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){
                                    optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.account_info.bank_province_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                                }    
                                $("#provincebank").append('<option value="">请选择省</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            alert('查找板块报错');
                        }
                    });
                    // 市
                    $.ajax({
                        url : "{{url('/api/basequery/city')}}",
                        data : {area_code:$('.provincecodebank').val()},
                        type : 'get',
                        success : function(data) {
                            // console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){
                                    optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.account_info.bank_city_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                                }    
                                $("#citybank").append('<option value="">请选择市</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            alert('查找板块报错');
                        }
                    });
                    // 区
                    $.ajax({
                        url : "{{url('/api/basequery/city')}}",
                        data : {area_code:$('.citycodebank').val()},
                        type : 'get',
                        success : function(data) {
                            // console.log(data);
                            var optionStr = "";
                                for(var i=0;i<data.data.length;i++){
                                    optionStr += "<option value='" + data.data[i].area_code + "' "+((res.data.account_info.bank_area_code==data.data[i].area_code)?"selected":"")+">"
                                    + data.data[i].area_name + "</option>";
                                }    
                                $("#areabank").append('<option value="">请选择区</option>'+optionStr);
                                layui.form.render('select');
                        },
                        error : function(data) {
                            alert('查找板块报错');
                        }
                    });
                    // 所属银行                
                    $.post("{{url('/api/basequery/bank')}}",
                    {
                        token:token
                    },function(data){
                        // console.log(data);
                        if(data.status==1){
                            var optionStr = "";
                            for(var i=0;i<data.data.length;i++){

                                optionStr += "<option value='" + data.data[i].bankname + "' "+((res.data.account_info.bank_name==data.data[i].bankname)?"selected":"")+">" + data.data[i].bankname + "</option>";
                            }    
                            $("#bank").append('<option value="">请选择银行</option>'+optionStr);
                            layui.form.render('select');                        
                        }

                    },"json");
                    // 所属支行 
                    $('.provincenamebank').val(res.data.account_info.bank_province_name); 
                    $('.citynamebank').val(res.data.account_info.bank_city_name); 
                    $('.areanamebank').val(res.data.account_info.bank_area_name);

                    if(res.data.account_info.bank_name==''){

                    }else{
                        $.post("{{url('/api/basequery/sub_bank')}}",
                        {
                            token:token,
                            bank_name:res.data.account_info.bank_name,
                            bank_province_name:res.data.account_info.bank_province_name,
                            bank_city_name:res.data.account_info.bank_city_name,
                            bank_area_name:res.data.account_info.bank_area_name
                        },function(data){
                            console.log(data);
                            if(data.status==1){
                                var optionStr = "";
                                for(var i=0;i<data.data.length;i++){

                                    optionStr += "<option value='" + data.data[i].bank_no + "' "+((res.data.account_info.bank_no==data.data[i].bank_no)?"selected":"")+">"+ data.data[i].sub_bank_name + "</option>";
                                }    
                                $("#subbank").append('<option value="">选择所属支行</option>'+optionStr);
                                layui.form.render('select');                       
                            }

                        },"json");
                    }
                    
                    $('.bank_no').val(res.data.account_info.bank_no);
                    $('.sub_bank_name').val(res.data.account_info.sub_bank_name);
                    $('.item12').val(res.data.account_info.bank_no);//联行号
                    $('.item13').val(res.data.account_info.store_alipay_account);//支付宝账户
                    $('.item13').val(res.data.account_info.store_alipay_account);
                    $('.item20').val(res.data.account_info.bank_sfz_stime);
                    $('.item21').val(res.data.account_info.bank_sfz_time);

                    // 四部分++++++++++++++++++++++++++++++++++++
                    $('.item14').val(res.data.license_info.store_license_no);
                    $('.item19').val(res.data.license_info.store_license_no);
                    $('.item22').val(res.data.license_info.store_license_stime);
                    $('.item15').val(res.data.license_info.store_license_time);


                    //图片第一部分
                    if(res.data.head_info.head_sfz_img_a!=''){
                        $('#demo1').attr('src',res.data.head_info.head_sfz_img_a);
                    }
                    if(res.data.head_info.head_sfz_img_b!=''){
                        $('#demo2').attr('src',res.data.head_info.head_sfz_img_b);
                    }               
                    
                    //***第二部分图片
                    if(res.data.store_info.store_logo_img!=''){
                        $('#demo3').attr('src',res.data.store_info.store_logo_img);
                    }
                    if(res.data.store_info.store_img_a!=''){
                        $('#demo4').attr('src',res.data.store_info.store_img_a);
                    }
                    if(res.data.store_info.store_img_b!=''){
                        $('#demo5').attr('src',res.data.store_info.store_img_b);
                    }
                    if(res.data.store_info.store_img_c!=''){
                        $('#demo6').attr('src',res.data.store_info.store_img_c);
                    }                
                    //**第三部分图片
                    if(res.data.account_info.bank_img_a!=''){
                        $('#demo7').attr('src',res.data.account_info.bank_img_a);
                    }                
                    if(res.data.account_info.bank_img_b!=''){
                        $('#demo8').attr('src',res.data.account_info.bank_img_b);
                    }
                    if(res.data.account_info.bank_sfz_img_a!=''){
                        $('#demo14').attr('src',res.data.account_info.bank_sfz_img_a);
                    }
                    if(res.data.account_info.bank_sfz_img_b!=''){
                        $('#demo15').attr('src',res.data.account_info.bank_sfz_img_b);
                    }
                    if(res.data.account_info.store_auth_bank_img!=''){
                        $('#demo16').attr('src',res.data.account_info.store_auth_bank_img);
                    }
                    if(res.data.account_info.bank_sc_img!=''){
                        $('#demo17').attr('src',res.data.account_info.bank_sc_img);
                    }

                    
                    if(res.data.store_info.store_type == 3 || res.data.store_info.store_type == 1 && res.data.account_info.store_bank_type=='01' || res.data.store_info.store_type == 2 && res.data.account_info.store_bank_type=='01'){
                        $('.bank').show()
                        if(res.data.account_info.store_bank_name == res.data.head_info.head_name){
                            $('.different').hide()
                        }else{
                            $('.different').show()
                        }
                    }else{
                        $('.bank').hide()
                        $('.different').hide()
                    }

                    if(res.data.store_info.store_type == 3){
                        $('.cardtype').hide()
                    }

                    

                    //**第四部分图片
                    if(res.data.license_info.store_license_img!=''){
                        $('#demo9').attr('src',res.data.license_info.store_license_img);
                    }
                    if(res.data.license_info.store_industrylicense_img!=''){
                        $('#demo10').attr('src',res.data.license_info.store_industrylicense_img);
                    }
                    if(res.data.license_info.head_sc_img!=''){
                        $('#demo11').attr('src',res.data.license_info.head_sc_img);
                    }
                    if(res.data.license_info.head_store_img!=''){
                        $('#demo12').attr('src',res.data.license_info.head_store_img);
                    }
                    if(res.data.store_info.store_type == 3){
                        $('.per').show()
                        $('.public').hide()
                        
                    }else{
                        $('.public').show()
                        $('.per').hide()
                    }
                    // if(res.data.license_info.store_other_img_c!=''){
                    //     $('#demo13').attr('src',res.data.license_info.store_other_img_c);
                    // }

                }else{
                    layer.msg(res.message, {
                        offset: '15px'
                        ,icon: 2
                        ,time: 1000
                    });
                }
            },"json");
            
        }

        // 上传图片函数

        //身份证正面
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token+"&img_type="+"2"+"&attach_name="+"head_sfz_img_a_url"+"&type="+"img", //提交到的地址 可以自定义其他参数
            elem : '.test1',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo1').attr("src", res.data.img_url);
                    layui.jquery('.item1').val(res.data.sfz_name);
                    layui.jquery('.item2').val(res.data.sfz_no);
                    
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //身份证反面
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token+"&img_type="+"3"+"&attach_name="+"head_sfz_img_b_url"+"&type="+"img",  //提交到的地址 可以自定义其他参数
            elem : '.test2',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo2').attr("src", res.data.img_url);
                    if(res.data.sfz_stime!=''){
                        layui.jquery('.item18').val(res.data.sfz_stime);
                    }
                    if(res.data.sfz_time!=''){
                        layui.jquery('.item16').val(res.data.sfz_time);
                    }
                    
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //上传门头
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test3',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo3').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //店内照1
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test4',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo4').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //店内照2
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test5',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo5').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //店内照3
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test6',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo6').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //上传银行卡正面
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token+'&img_type='+4,  //提交到的地址 可以自定义其他参数
            elem : '.test7',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 1){
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo7').attr("src", res.data.img_url); 
                    if(res.data.store_bank_no!=''){
                        $('.item7').val(res.data.store_bank_no)
                    }
                    if(res.data.bank_name!=''){
                        $('.bankname').val(res.data.bank_name)
                    }
                    
                    
                    $.post("{{url('/api/basequery/bank')}}",
                    {
                        token:token,
                        bankname:res.data.bank_name,
                    },function(data){
                        console.log(data);
                        if(data.status==1){
                            var optionStr = "";
                            for(var i=0;i<data.data.length;i++){

                                optionStr += "<option value='" + data.data[i].bankname + "' "+((res.data.bank_name==data.data[i].bankname)?"selected":"")+">" + data.data[i].bankname + "</option>";
                            }    
                            $("#bank").append('<option value="">请选择银行</option>'+optionStr);
                            layui.form.render('select');                        
                        }

                    },"json");
                    $.post("{{url('/api/basequery/sub_bank')}}",
                    {
                        token:token,
                        bank_name:res.data.bank_name,
                        l:'500'
                    },function(data){
                        console.log(data);
                        if(data.status==1){
                            var optionStr = "";
                            for(var i=0;i<data.data.length;i++){

                                optionStr += "<option value='" + data.data[i].bank_no + "'>"+ data.data[i].sub_bank_name + "</option>";
                            }    
                            $("#subbank").html('');
                            $("#subbank").append('<option value="">选择所属支行</option>'+optionStr);
                            layui.form.render('select');                       
                        }

                    },"json"); 

                }else{
                    layer.msg(res.message, {icon:2, shade:0.5, time:res.time});
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //上传银行卡反面
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test8',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo8').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //营业执照
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token+"&img_type="+"1"+"&attach_name="+"store_license_img"+"&type="+"img",  //提交到的地址 可以自定义其他参数
            elem : '.test9',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo9').attr("src", res.data.img_url);
                    if(res.data.store_license_no !=''){
                        layui.jquery('.item14').val(res.data.store_license_no);
                    }
                    if(res.data.store_license_stime !=''){
                        layui.jquery('.item22').val(res.data.store_license_stime);
                        layui.jquery('.item15').val(res.data.store_license_time);
                    }
                    
                    
                }
                
            }
        });
        //开户许可证
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test10',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo10').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //手持身份证照
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test11',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo11').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //人站在门口照
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test12',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo12').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //持卡人身份证正面
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test14',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo14').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //持卡人身份证反面
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token+'&img_type='+ 3,  //提交到的地址 可以自定义其他参数
            elem : '.test15',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo15').attr("src", res.data.img_url);
                    layui.jquery('.item20').val(res.data.sfz_stime);
                    layui.jquery('.item21').val(res.data.sfz_time);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //授权结算书
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test16',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo16').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
        });
        //授权结算书
        var uploadInst = upload.render({
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token,  //提交到的地址 可以自定义其他参数
            elem : '.test17',  //指定元素的选择器，默认直接查找class为layui-upload-file的元素
            method : 'POST',    //设置http类型，如：post、get。默认post。也可以直接在input设置lay-method="get"来取代。
            type : 'images',    //[images 图片类型，默认][file普通文件类型][video视频文件类型][audio音频文件类型]
            ext : 'jpg|png|gif',    //自定义支持的文件格式
            unwrap : true, //是否不改变input的样式风格。默认false 
            size : 5120,
            before : function(input){
                //执行上传前的回调  可以判断文件后缀等等
                layer.msg('上传中，请稍后......', {icon:16, shade:0.5, time:0});
            },
            done: function(res){
                console.log(res);
                if(res.status == 0){
                    layer.msg(res.msg, {icon:2, shade:0.5, time:res.time});
                }else{
                    layer.msg("文件上传成功", {icon:1, shade:0.5, time:res.time});
                    layui.jquery('#demo17').attr("src", res.data.img_url);
                }
                //console.log(res); //上传成功返回值，必须为json格式
            }
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
        // 营业执照时间选择
        laydate.render({
          elem: '.start-item'
          ,done: function(value){
                       
          }
        });
        laydate.render({
          elem: '.end-item'
          ,done: function(value){
                       
          }
        });
        // -****法人身份证时间选择***
        laydate.render({
          elem: '.frs-item' 
          ,done: function(value){
                       
          }
        });
        laydate.render({
          elem: '.fre-item' 
          ,done: function(value){
                       
          }
        });
        // ****持卡人身份证时间选择****
        laydate.render({
          elem: '.haves-item'
          ,done: function(value){
                       
          } 
        });
        laydate.render({
          elem: '.havee-item' 
          ,done: function(value){
                       
          }
        });

        if(store_id_add==0){
            var data = new Date()
            var year = data.getFullYear();
            var mon = data.getMonth() + 1;
            var day = data.getDate();
            var h = data.getHours()
            var m = data.getMinutes()
            var s = data.getSeconds()
            function test(){
                // 0-9的随机数
                var arr = [];//容器
                for(var i =0;i<6;i++){//循环六次
                    var num = Math.random()*9;//Math.random();每次生成(0-1)之间的数;
                    num = parseInt(num,10);
                    arr.push(num);
                }       
                console.log(year+''+mon+''+day+''+arr.join(''));
                new_storeid=year+''+mon+''+day+''+h+''+m+''+s+''+arr.join('')
                return new_storeid;
            };
            test()
            // 提交法人信息1111111111
            $('.submit1').click(function(){
                $.post("{{url('/api/user/up_store')}}",
                {
                    token:token,
                    store_id:new_storeid,
                    head_name:$('.item1').val(),
                    head_sfz_no:$('.item2').val(),
                    head_sfz_img_a:$('#demo1').attr('src'),
                    head_sfz_img_b:$('#demo2').attr('src'),
                    head_sfz_stime:$('.item18').val(),
                    head_sfz_time:$('.item16').val()
                    
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
            // 提交门店信息22222222
            $('.submit2').click(function(){
                $.post("{{url('/api/user/up_store')}}",
                {
                    token:token,
                    store_id:new_storeid,

                    store_name:$('.item3').val(),
                    store_short_name:$('.item17').val(),
                    province_code:$('.provincecode').val(),
                    city_code:$('.citycode').val(),
                    area_code :$('.areacode').val(),
                    store_address:$('.item4').val(),
                    store_type:$('.store_type').val(),
                    store_type_name:$('.store_name').val(),/*-------*/
                    category_id:$('.category_id').val(),
                    category_name:$('.category_name').val(),
                    store_logo_img:$('#demo3').attr('src'),
                    store_img_a:$('#demo4').attr('src'),
                    store_img_b:$('#demo5').attr('src'),
                    store_img_c:$('#demo6').attr('src'),
                    people_phone:$('.phone').val(),
                    store_email:$('.email').val(),
                    people:$('.store_people').val(),
                    weixin_name:$('.wechatname').val(),
                    weixin_no:$('.wechatno').val(),

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
            // 提交账户信息33333333
            $('.submit3').click(function(){
                $.post("{{url('/api/user/up_store')}}",
                {
                    token:token,
                    store_id:new_storeid,

                    store_bank_no:$('.item7').val(),
                    store_bank_name:$('.item8').val(),
                    store_bank_phone:$('.item9').val(),
                    store_bank_type:$('.cardtype_id').val(),
                    bank_name:$('.bankname').val(),
                    bank_no:$('.item12').val(),
                    sub_bank_name:$('.sub_bank_name').val(),

                    bank_province_code:$('.provincecodebank').val(),
                    bank_city_code:$('.citycodebank').val(),
                    bank_area_code:$('.areacodebank').val(),
                    bank_img_a:$('#demo7').attr('src'),
                    bank_img_b:$('#demo8').attr('src'),
                    bank_sc_img:$('#demo17').attr('src'),

                    bank_sfz_img_a:$('#demo14').attr('src'),
                    bank_sfz_img_b:$('#demo15').attr('src'),
                    store_auth_bank_img:$('#demo16').attr('src'),

                    bank_sfz_stime:$('.item20').val(),
                    bank_sfz_time:$('.item21').val(),

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
            // 证件照信息44444444
            $('.submit4').click(function(){
                if($('.store_type').val() == 1 || $('.store_type').val() == 2){
                    $.post("{{url('/api/user/up_store')}}",
                    {
                        token:token,
                        store_id:new_storeid,

                        store_license_no:$('.item14').val(),
                        store_license_stime:$('.item22').val(),
                        store_license_time:$('.item15').val(),
                        store_license_img:$('#demo9').attr('src'),
                        store_industrylicense_img:$('#demo10').attr('src')
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
                }else{
                    $.post("{{url('/api/user/up_store')}}",
                    {
                        token:token,
                        store_id:store_id,

                        store_license_no:$('.item14').val(),
                        store_license_stime:$('.item22').val(),
                        store_license_time:$('.item15').val(),
                        head_sc_img:$('#demo11').attr('src'),
                        head_store_img:$('#demo12').attr('src')
                        // store_other_img_c:$('#demo13').attr('src')
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
                }
                
            });
        }else{
            // 提交法人信息1111111111
            $('.submit1').click(function(){
                $.post("{{url('/api/user/up_store')}}",
                {
                    token:token,
                    store_id:store_id,
                    head_name:$('.item1').val(),
                    head_sfz_no:$('.item2').val(),
                    head_sfz_img_a:$('#demo1').attr('src'),
                    head_sfz_img_b:$('#demo2').attr('src'),
                    head_sfz_stime:$('.item18').val(),
                    head_sfz_time:$('.item16').val()
                    
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
            // 提交门店信息22222222
            $('.submit2').click(function(){
                $.post("{{url('/api/user/up_store')}}",
                {
                    token:token,
                    store_id:store_id,

                    store_name:$('.item3').val(),
                    store_short_name:$('.item17').val(),
                    province_code:$('.provincecode').val(),
                    city_code:$('.citycode').val(),
                    area_code :$('.areacode').val(),
                    store_address:$('.item4').val(),
                    store_type:$('.store_type').val(),
                    store_type_name:$('.store_name').val(),/*-------*/
                    category_id:$('.category_id').val(),
                    category_name:$('.category_name').val(),
                    store_logo_img:$('#demo3').attr('src'),
                    store_img_a:$('#demo4').attr('src'),
                    store_img_b:$('#demo5').attr('src'),
                    store_img_c:$('#demo6').attr('src'),
                    people_phone:$('.phone').val(),
                    store_email:$('.email').val(),
                    people:$('.store_people').val(),
                    weixin_name:$('.wechatname').val(),
                    weixin_no:$('.wechatno').val(),

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
            // 提交账户信息33333333
            $('.submit3').click(function(){
                $.post("{{url('/api/user/up_store')}}",
                {
                    token:token,
                    store_id:store_id,

                    store_bank_no:$('.item7').val(),
                    store_bank_name:$('.item8').val(),
                    store_bank_phone:$('.item9').val(),
                    store_bank_type:$('.cardtype_id').val(),
                    bank_name:$('.bankname').val(),
                    bank_no:$('.item12').val(),
                    sub_bank_name:$('.sub_bank_name').val(),

                    bank_province_code:$('.provincecodebank').val(),
                    bank_city_code:$('.citycodebank').val(),
                    bank_area_code:$('.areacodebank').val(),
                    bank_img_a:$('#demo7').attr('src'),
                    bank_img_b:$('#demo8').attr('src'),
                    bank_sc_img:$('#demo17').attr('src'),

                    bank_sfz_img_a:$('#demo14').attr('src'),
                    bank_sfz_img_b:$('#demo15').attr('src'),
                    store_auth_bank_img:$('#demo16').attr('src'),

                    bank_sfz_stime:$('.item20').val(),
                    bank_sfz_time:$('.item21').val()

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
            // 证件照信息44444444
            $('.submit4').click(function(){
                if($('.store_type').val() == 1 || $('.store_type').val() == 2){
                    $.post("{{url('/api/user/up_store')}}",
                    {
                        token:token,
                        store_id:store_id,

                        store_license_no:$('.item14').val(),
                        store_license_stime:$('.item22').val(),
                        store_license_time:$('.item15').val(),
                        store_license_img:$('#demo9').attr('src'),
                        store_industrylicense_img:$('#demo10').attr('src')
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
                }else{
                    $.post("{{url('/api/user/up_store')}}",
                    {
                        token:token,
                        store_id:store_id,

                        store_license_no:$('.item14').val(),
                        store_license_stime:$('.item22').val(),
                        store_license_time:$('.item15').val(),
                        head_sc_img:$('#demo11').attr('src'),
                        head_store_img:$('#demo12').attr('src')
                        // store_other_img_c:$('#demo13').attr('src')
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
                }
                
            });
        }
        


    });
</script>
</body>
</html>
