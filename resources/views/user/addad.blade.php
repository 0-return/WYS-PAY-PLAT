<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>添加缴费项目</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/formSelects-v4.css')}}" media="all">
    <style>
        .icon-close{display: none;}
        #demo1 img{width: 100%;height: 100%;}
        .up input{position: absolute;top:0;left: 0;display: block;opacity: .01;width: 100px;height:30px;}
        .img_box{position: relative;width:13%;height:10%;display: inline-block; margin-right: 10px;}
        .img_box span{position: absolute;right:0;top:0;font-size: 30px;background: #fff;cursor: pointer;}
    </style>
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-card-header">缴费项目</div>
        <div class="layui-card-body" style="padding: 15px;">
            <div class="layui-form" lay-filter="component-form-group"> 
                <div class="layui-form-item">
                    <label class="layui-form-label">广告标题</label>
                    <div class="layui-input-block">
                        <input type="text" placeholder="请输入广告标题:" class="layui-input title">
                    </div>
                </div>             
                
                <div class="layui-form-item class">
                    <label class="layui-form-label">广告投放位置</label>
                    <div class="layui-input-block">
                        <select name="position" id="position" xm-select="position">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item class">
                    <label class="layui-form-label">广告投生效范围</label>
                    <div class="layui-input-block" style="width:40%;display: inline-block;margin-left:0">
                        <select name="range" id="range" xm-select="range" xm-select-search="">
                            
                        </select>
                    </div>
                    <div class="layui-input-block store" style="width:40%;display: inline-block;margin-left:50px">
                        <select name="store" id="store" xm-select="store">
                            
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">广告投放时间</label>                          
                    <div class="layui-inline">
                      <div class="layui-input-inline" style="margin-right: 0px;">
                        <input type="text" class="layui-input start-item test-item" placeholder="开始时间" lay-key="23">
                      </div>
                    </div>
                    -
                    <div class="layui-inline" style="margin-left: 10px;">
                      <div class="layui-input-inline">
                        <input type="text" class="layui-input end-item test-item" placeholder="结束时间" lay-key="24">
                      </div>
                    </div>               
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">广告展示图片</label>
                    <div class="layui-input-block">
                        <div class="layui-upload">
                          <button class="layui-btn up"><input type="file" name="img_upload" class="test1">上传图片</button>
                          <blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 10px;">
                            预览图：
                            <div class="layui-upload-list" id="demo1"></div>
                         </blockquote>
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">广告复制粘贴内容</label>
                    <div class="layui-input-block">
                        <textarea name="desc" placeholder="请输入内容" class="layui-textarea con"></textarea>
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

<input type="hidden" class="user_id" value="">
<input type="hidden" class="position_id" value="">
<input type="hidden" class="position_name" value="">
<input type="hidden" class="store-id" value="">

<input type="hidden" class="classname" value="">
<input type="hidden" class="templateid" value="">
<input type="hidden" class="student_code" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<!-- <script src="{{asset('/layuiadmin/modules/formSelects.js')}}"></script> -->
<script>
    var token = localStorage.getItem("Usertoken");
    

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
        formSelects.render('position');
        formSelects.btns('position', []);
        formSelects.render('range');
        formSelects.btns('range', []);
        formSelects.render('store');
        formSelects.btns('store', []);
        var arrp=[];
        var arrr=[];
        var arrs=[];

        // 位置
        formSelects.config('position', {
            beforeSuccess: function(id, url, searchVal, result){
                //我要把数据外层的code, msg, data去掉
                result = result.data;
                // console.log(result);
                for(var i=0;i<result.length;i++){

                    var data ={"value":result[i].ad_p_id,"name":result[i].ad_p_desc}
                    arrp.push(data);
                    // console.log(arr);
                }
                console.log(arrp);
                //然后返回数据
                return arrp;
            }
        }).data('position', 'server', {
            url:"{{url('/api/ad/ad_p_id?token=')}}"+token
        });



        // 业务员

        $('#range').html('');
        formSelects.config('range', {
            searchUrl: "{{url('/api/user/get_sub_users?token=')}}"+token+'&self='+1,
            searchName: 'user_name',
            beforeSuccess: function(id, url, searchVal, result){
                //我要把数据外层的code, msg, data去掉
                console.log(result);
                result = result.data;
                var arrr=[];
                for(var i=0;i<result.length;i++){

                    var data ={"value":result[i].id,"name":result[i].name+'-'+result[i].level_name}
                    arrr.push(data);
                }
                console.log(arrr);
                //然后返回数据
                return arrr;
            }
        }).data('range', 'server', {
            // url:"{{url('/api/school/teacher/class/lst?token=')}}"+token
            // url:"{{url('/api/user/get_sub_users?token=')}}"+token
        });

        // $('#range').attr('xm-select-search',"{{url('/api/user/get_sub_users?token=')}}"+token)
        // layui.formSelects.search('range', 'click');



        
        var arr=[];
        formSelects.on('range', function(id, vals, val, isAdd, isDisabled){
            arrs=[];
            console.log(val);
            
            if(isAdd==true){
                if($('.user_id').val()==''){
                    $('.user_id').val(val.value);
                }else{
                    if($('.user_id').val()!=val.value){
                        $('.user_id').val($('.user_id').val()+','+val.value);
                    }
                    
                }
 
            }else{
                var arrbox=[];
                for(var i=0;i<vals.length;i++){

                    if(val.value!=vals[i].value){//当为false时去掉的userid 在数组中去掉重复的userid,此方法有小bug
                        var box = vals[i].value;
                        arrbox.push(box);
                        // console.log(arrbox);//选中的userid
                        $('.user_id').val(arrbox.join());

                    }
                    
                }

            }
            
            var a=$('.user_id').val();
            var aa=a.split(',');
            
            var c=parseInt(a);
            var b=val.value;//number类型
            
            


            if(aa.length>1){
                
                formSelects.data('store', 'local', {arr: []});

            }else{
                if(b==a && isAdd==false){
                    

                    formSelects.data('store', 'local', {arr: []});
                    
                    $('.user_id').val('');
                    
                }else{
                    // 门店                
                    formSelects.config('store', {
                        beforeSuccess: function(id, url, searchVal, result){
                            //我要把数据外层的code, msg, data去掉
                            
                            result = result.data;
                            
                            for(var i=0;i<result.length;i++){

                                var data ={"value":result[i].id,"name":result[i].store_name}
                                arrs.push(data);
                            } 
                            //然后返回数据
                            return arrs;
                            
                            // console.log(arr);
                            
                        },
                        clearInput: true
                    }).data('store', 'server', {
                        url:"{{url('/api/user/store_lists?token=')}}"+token+"&user_id="+$('.user_id').val()
                    });
                }
                 
            }

        });



       

        //多图片上传
        upload.render({            
            url : "{{url('/api/basequery/webupload?act=images')}}"+'&token='+token
            ,elem: '.test1'
            ,method : 'POST'
            ,type : 'images'
            ,ext : 'jpg|png|gif'
            ,multiple: true
            ,before: function(obj){
              //预读本地文件示例，不支持ie8
              obj.preview(function(index, file, result){

                $('#demo1').append('<div class="img_box" data=""><img src="'+ result +'" alt="'+ file.name +'" class="layui-upload-img"><span>×</span><input type="text" class="layui-input url" placeholder="点击图片跳转链接"></div>')

                $("#demo1 .img_box").each(function(){
                    var index = $(this).index()+1;
                    $(this).attr('data',index);
                })
                

              });
            }
            ,done: function(res){
                console.log(res);
                
                $("#demo1 .img_box:last-child").find('img').attr('src',res.data.img_url);
               
            }
        });
      



        
           
        
        $('.submit').on('click', function(){
            var adarr=[];
             console.log(layui.formSelects.value('store', 'valStr')); 
             $('.position_id').val(layui.formSelects.value('position', 'valStr'));
             $('.position_name').val(layui.formSelects.value('position', 'nameStr'));

             $('.store-id').val(layui.formSelects.value('store', 'valStr'));

             var daTa={"img_url":"","click_url":""}
             $('#demo1 .img_box').each(function(index,item){
                

                var img_url=$(item).find('img').attr('src');
                var click_url=$(item).find('input').val();             

                var data ={"img_url":img_url,"click_url":click_url}//构造数组
                adarr.push(data)

            })
          
            
            var adarrJson=JSON.stringify(adarr);//转化成json格式
            // console.log(adarrJson);



            $.post("{{url('/api/ad/ad_create')}}",
            {
                token:token,
                title:$('.title').val(),
                ad_p_id:$('.position_id').val(),    //位置合集-'1,2,3,4,5,6’
                ad_p_desc:$('.position_name').val(),   //位置说明合集-‘支付宝，微信’
                user_ids:$('.user_id').val(),  //用户合集-‘1,2,3,4’
                store_key_ids:$('.store-id').val(),  //门店合-'1,2,3,4'

                s_time:$('.start-item').val(), 
                e_time:$('.end-item').val(),   
                imgs:adarrJson,
                copy_content:$('.con').val()   //拷贝内容


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

        $('#demo1').on('click','.img_box span',function(){
            $(this).parent().remove();
        });
        

        laydate.render({
            elem: '.start-item'
            ,type: 'datetime'
            ,done: function(value){
              // console.log(nwedata);
                var oDate1 = new Date(value);    
                var oDate2 = new Date($('.end-item').val());
                if(oDate1.getTime() > oDate2.getTime()){

                    layer.msg("开始时间不能高于当前时间", {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                    });

                }
            }
        });
        laydate.render({
            elem: '.end-item'
            ,type: 'datetime'
            ,done: function(value){
              // console.log(nwedata);
                var oDate1 = new Date($('.start-item').val());    
                var oDate2 = new Date(value);
                if(oDate1.getTime() > oDate2.getTime()){

                    layer.msg("截止时间不能低于开始时间", {
                        offset: '15px'
                        ,icon: 2
                        ,time: 3000
                    });

                }
            }
        });

    });
</script>
</body>
</html>
