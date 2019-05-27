<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="">
    <title>缴费明细</title>
    <link rel="stylesheet" href="{{asset('/school/css/style.css')}}" media="all">
    <script type="text/javascript" src="{{asset('/school/js/Screen.js')}}"></script>
</head>
<body style="background-color: #f2f4f5;">

<div class="box">
    <div class="detailed_lst_title" style="font-size:.32rem;">必缴费用</div>
    <div class="detailed contant1">        
        <!-- <div class="detailed_lst">
            <img src="images/xuanzhong.png" data="on">
            <label>书本费</label>
            <p class="jt">200元</p>

            <div class="detailed_lst_child" style="display: none;">
                <div class="detailed_lst_item">
                    <label>缴费数量:</label>
                    <span>20</span>
                </div>
                <div class="detailed_lst_item">
                    <label>费用说明:</label>
                    <span>含本学期所有学科书本费用，每位学生必须缴纳。</span>
                </div>
            </div>
        </div>
        <div class="detailed_lst">
            <img src="images/xuanzhong.png" data="on">
            <label>书本费</label>
            <p class="jt">200元</p>
        </div> -->
    </div>


    <div class="detailed_lst_title" style="font-size:.32rem;">非必缴费用<span style="color:#ff3952;font-size:.28rem;">(*可取消不缴纳)</span></div>
    <div class="detailed contant2">        
        <!-- <div class="detailed_lst">
            <img src="images/xuanzhong.png" data="on">
            <label>书本费</label>
            <p class="jt">200元</p>

            <div class="detailed_lst_child" style="display: none;">
                <div class="detailed_lst_item">
                    <label>缴费数量:</label>
                    <span>20</span>
                </div>
                <div class="detailed_lst_item">
                    <label>费用说明:</label>
                    <span>含本学期所有学科书本费用，每位学生必须缴纳。</span>
                </div>
            </div>

        </div>
        <div class="detailed_lst">
            <img src="images/xuanzhong.png" data="on">
            <label>书本费</label>
            <p class="jt">200元</p>
        </div> -->
        
    </div>
</div>


<div class="bottom_btn">
    <div class="money">￥<span class="jine">0.00</span>&nbsp;(<span class="item">0</span>)项</div>
    <div class="btn_confirm">确认缴费</div>
</div>



<!-- 提示 --------------------------------------------------------->
<div class="mask" style="display: none">
    <div class="popup_bg"></div>
    <div class="result_layer" id="accountMsg">        
    <div style="padding: .4rem 0;">
        <p style="font-size:.35rem;height: .6rem;font-weight: 500;">提示</p>
        <p class="tip" style="padding-top: .25rem;"></p>
    </div>
    <a class="qr" style="width:100%;border-left:1px solid #ccc;color:#219aff">我知道了</a>
    </div>
</div>




<input type="hidden" class="bijiao">
<input type="hidden" class="feibijiao">
<input type="hidden" class="store_id">

<input type="hidden" class="batch_name">
<input type="hidden" class="out_trade_no">


<div class="load-hidden"></div>

<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/fastclick.js')}}"></script>
<script>$(function() {FastClick.attach(document.body);});</script>
<script>
    var out_trade_no="{{$_GET['out_trade_no']}}";
    var open_id="{{$_GET['open_id']}}";
    // 点击选中或者不选中
    $('.box').on("click",".contant2 .detailed_lst img",function(){
        if($(this).attr('src')=="{{asset('/school/images/xuanzhong.png')}}"){
            $(this).attr('src',"{{asset('/school/images/weixuanzhong.png')}}");
            $(this).attr('data','off');
        }else{
            $(this).attr('src',"{{asset('/school/images/xuanzhong.png')}}");
            $(this).attr('data','on');
        }
        var noneed=0;
        $('.box .contant2 .detailed_lst').each(function(){            
            if($(this).find('img').attr('data')=="on"){
                
                noneed=noneed+parseFloat($(this).find('p').find('span').html());
                $('.feibijiao').val(noneed.toFixed(2));
            }else{
                $('.feibijiao').val('0');
            }
            var a=parseFloat($('.bijiao').val())+parseFloat($('.feibijiao').val());            
            $('.jine').html(a);
        });
        var len_no='';
        $('.box .detailed .detailed_lst').each(function(){   
           
            if($(this).find('img').attr('data')=="on"){
                len_no=len_no+1;
                var lens=$(this).length;                
                $('.item').html(len_no.length);
            }     
            
        });



    });
    // 展开隐藏
    $('.box').on("click",".detailed_lst .jt",function(){
//        $('.detailed_lst').find('p').removeClass('xia');
//        $('.detailed_lst_child').hide();

        if($(this).hasClass('xia')){
            $(this).removeClass('xia');
            $(this).siblings('.detailed_lst_child').hide();
        }else{
            $(this).addClass('xia');
            $(this).siblings('.detailed_lst_child').show();
        }

        
    });
    $(document).ready(function(){
        
        $.post("{{url('/api/consumer/h5/order/show')}}",
        {
            out_trade_no:out_trade_no
        },function(res){
            console.log(res);
            $('.store_id').val(res.data.store_id);
            $('.batch_name').val(res.data.batch_name);
            $('.out_trade_no').val(res.data.out_trade_no);
            var str1="";     
            var str2="";     
            var sum=0;
            var bi=0;
            var fei=0;
            for(var i=0; i<res.data.all_item.length;i++){
                if(res.data.all_item[i].item_mandatory==="Y"){
                    str1+='<div class="detailed_lst" id="'+res.data.all_item[i].id+'" item_name="'+res.data.all_item[i].item_name+'" item_number="'+res.data.all_item[i].item_number+'" item_mandatory="'+res.data.all_item[i].item_mandatory+'" item_price="'+res.data.all_item[i].item_price+'" item_serial_number="'+res.data.all_item[i].item_serial_number+'" out_trade_no="'+res.data.all_item[i].out_trade_no+'" status="'+res.data.all_item["0"].status+'" status_desc="'+res.data.all_item[i].status_desc+'">';
                        str1+='<img src="{{asset('/school/images/xuanzhong.png')}}" data="on">';
                        str1+='<label>'+res.data.all_item[i].item_name+'</label>';
                        str1+='<p class="jt"><span>'+res.data.all_item[i].item_price+'</span>元</p>';

                        str1+='<div class="detailed_lst_child" style="display: none;overflow:hidden">';
                            str1+='<div class="detailed_lst_item">';
                                str1+='<label>缴费数量:&nbsp;</label>';
                                str1+='<span>'+res.data.all_item[i].item_number+'</span>';
                            str1+='</div>';
                            
                        str1+='</div>';
                    str1+='</div>';
                    $('.contant1').html('');
                    $('.contant1').append(str1);

                    bi=bi+parseFloat(res.data.all_item[i].item_number*res.data.all_item[i].item_price);
                    $('.bijiao').val(bi.toFixed(2));
                }else if(res.data.all_item[i].item_mandatory==="N"){
                    str2+='<div class="detailed_lst" id="'+res.data.all_item[i].id+'" item_name="'+res.data.all_item[i].item_name+'" item_number="'+res.data.all_item[i].item_number+'" item_mandatory="'+res.data.all_item[i].item_mandatory+'" item_price="'+res.data.all_item[i].item_price+'" item_serial_number="'+res.data.all_item[i].item_serial_number+'" out_trade_no="'+res.data.all_item[i].out_trade_no+'" status="'+res.data.all_item["0"].status+'" status_desc="'+res.data.all_item[i].status_desc+'">';
                        str2+='<img src="{{asset('/school/images/xuanzhong.png')}}" data="on">';
                        str2+='<label>'+res.data.all_item[i].item_name+'</label>';
                        str2+='<p class="jt"><span>'+res.data.all_item[i].item_price+'</span>元</p>';

                        str2+='<div class="detailed_lst_child" style="display: none;overflow:hidden">';
                            str2+='<div class="detailed_lst_item">';
                                str2+='<label>缴费数量:&nbsp;</label>';
                                str2+='<span>'+res.data.all_item[i].item_number+'</span>';
                            str2+='</div>';
                            
                        str2+='</div>';
                    str2+='</div>';
                    $('.contant2').html('');
                    $('.contant2').append(str2);

                    fei=fei+parseFloat(res.data.all_item[i].item_number*res.data.all_item[i].item_price);
                    $('.feibijiao').val(fei.toFixed(2));

                }

                
                sum=sum+parseFloat(res.data.all_item[i].item_number*res.data.all_item[i].item_price);
                $('.jine').html(sum.toFixed(2));
                $('.item').html(res.data.all_item.length);

            }
            
        },"json");
    });



   
    // $('.btn_confirm').click(function(){
        
    //     $('.box .detailed .detailed_lst').each(function(){   
    //         if($(this).find('img').attr('data')=='on'){
    //             id=$(this).attr('id');
    //             item_name=$(this).attr('item_name');
    //             item_number=$(this).attr('item_number');
    //             item_mandatory=$(this).attr('item_mandatory');
    //             item_price=$(this).attr('item_price');
    //             item_serial_number=$(this).attr('item_serial_number');
    //             out_trade_no=$(this).attr('out_trade_no');
    //             status=$(this).attr('status');
    //             status_desc=$(this).attr('status_desc');
                
    //             var data = {"id":id,"item_name":item_name,"item_number":item_number,"item_mandatory":item_mandatory,"item_price":item_price,"item_serial_number":item_serial_number,"out_trade_no":out_trade_no,"status":status,"status_desc":status_desc}
    //             arr.push(data)
    //         }          
            
    //     });
    //     console.log(arr);
    //     var tableJson=JSON.stringify(arr);//转化成json格式
    //     console.log(tableJson);



    //     $.post("{{url('/api/merchant/school_pay')}}",
    //     {
    //         store_id:$('.store_id').val(),
    //         ways_source:'weixin',
    //         ways_type:'2001',
    //         out_trade_no:out_trade_no,
    //         items:tableJson
    //     },function(back){
    //         console.log(back);
    //         if (back.status == 1) {
    //             var json_data = eval('(' + back.data + ')');
    //             WeixinJSBridge.invoke(
    //                 'getBrandWCPayRequest', json_data,
    //                 function (res) {
    //                     if (res.err_msg == "get_brand_wcpay_request:ok") {
    //                         alert('支付成功！');
    //                     }
    //                     // 支付失败  JS API的返回结果get_brand_wcpay_request:ok仅在用户成功完成支付时返回。由于前端交互复杂，get_brand_wcpay_request:cancel或者get_brand_wcpay_request:fail可以统一处理为用户遇到错误或者主动放弃，不必细化区分。
    //                     else {
    //                         alert('支付失败！');
    //                     }
    //                 }
    //             );
    //         }
    //         else {
    //             $('.mask').show();
    //             $('.tip').html(back.message);
    //             // alert('状态:'+back.status);
    //             // alert('data:'+back.data);
    //         }
            
    //     },"json");
    // });


    var arr=[];
    $(function () {
        function onBridgeReady() {
            $('.box .detailed .detailed_lst').each(function(){   
                if($(this).find('img').attr('data')=='on'){
                    id=$(this).attr('id');
                    item_name=$(this).attr('item_name');
                    item_number=$(this).attr('item_number');
                    item_mandatory=$(this).attr('item_mandatory');
                    item_price=$(this).attr('item_price');
                    item_serial_number=$(this).attr('item_serial_number');
                    out_trade_no=$(this).attr('out_trade_no');
                    status=$(this).attr('status');
                    status_desc=$(this).attr('status_desc');
                    
                    var data = {"id":id,"item_name":item_name,"item_number":item_number,"item_mandatory":item_mandatory,"item_price":item_price,"item_serial_number":item_serial_number,"out_trade_no":out_trade_no,"status":status,"status_desc":status_desc}
                    arr.push(data)
                }          
                
            });
            console.log(arr);
            var tableJson=JSON.stringify(arr);//转化成json格式
            console.log(tableJson);



            $.post("{{url('/api/merchant/school_pay')}}",
            {
                store_id:$('.store_id').val(),
                ways_source:'weixin',
                ways_type:'2001',
                out_trade_no:out_trade_no,
                items:tableJson,
                open_id:open_id,
                shop_name:$('.batch_name').val()
            },function(back){
                console.log(back);
                if (back.status == 1) {
                    var json_data = eval('(' + back.data + ')');
                    WeixinJSBridge.invoke(
                        'getBrandWCPayRequest', json_data,
                        function (res) {
                            if (res.err_msg == "get_brand_wcpay_request:ok") {
                                // alert('支付成功！');
                                location.href = "{{url('/school/paysucceed?out_trade_no=')}}"+out_trade_no;
                            }
                            // 支付失败  JS API的返回结果get_brand_wcpay_request:ok仅在用户成功完成支付时返回。由于前端交互复杂，get_brand_wcpay_request:cancel或者get_brand_wcpay_request:fail可以统一处理为用户遇到错误或者主动放弃，不必细化区分。
                            else {
                                alert('支付失败！');
                                setTimeout("window.location.reload()");
                            }
                        }
                    );
                }
                else {
                    $('.mask').show();
                    $('.tip').html(back.message);
                   
                }
                
            },"json");

        }
       

        // 触发支付事件
        $('.btn_confirm').click(function () {
            $('.load-hidden').addClass('loading');
            $('.load-hiddenbg').show();
            $(this).attr('disabled', 'disabled');
            
            if (typeof WeixinJSBridge == "undefined") {
                if (document.addEventListener) {
                    document.addEventListener('WeixinJSBridgeReady', onBridgeReady(), false);
                } else if (document.attachEvent) {
                    document.attachEvent('WeixinJSBridgeReady', onBridgeReady());
                    document.attachEvent('onWeixinJSBridgeReady', onBridgeReady());
                }
            } else {
                onBridgeReady();
            }
        })

    });

    $('.qr').click(function(){
        $('.mask').hide();
    })
</script>
</body>
</html>