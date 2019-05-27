<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="">
    <title>待缴费用</title>
    <link rel="stylesheet" href="{{asset('/school/css/style.css')}}" media="all">
    <script type="text/javascript" src="{{asset('/school/js/Screen.js')}}"></script>
</head>
<body style="background-color: #f2f4f5;">
<div class="box">
    
</div>


<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/fastclick.js')}}"></script>
<script>$(function() {FastClick.attach(document.body);});</script>
<script>
	var student_id="{{$_GET['student_id']}}";
    var open_id = localStorage.getItem("pay_open_id");
    var pay_store_id = localStorage.getItem("pay_store_id");
    var pay_stu_grades_no = localStorage.getItem("pay_stu_grades_no");
    var pay_stu_class_no = localStorage.getItem("pay_stu_class_no");
    var pay_name = localStorage.getItem("pay_name");
	var pay_phone = localStorage.getItem("pay_phone");

	$(document).ready(function(){
        
        $.post("{{url('/api/consumer/h5/order/lst')}}",
        {
            student_id:student_id
        },function(res){
            console.log(res);
            var html='';
        	if(res.status==1){
        		for(var i=0;i<res.data.length;i++){
        			html+='<div class="fee_list">';
				        html+='<img class="shoukuan" src="{{asset('/school/images/banjishoukuan.png')}}">';
				        html+='<div class="fee_detail">';
				            html+='<p>'+res.data[i].batch_name+'</p>';
				            html+='<p>￥'+res.data[i].amount+'</p>';
				            html+='<p class="overday">'+res.data[i].gmt_end+'</p>';
				        html+='</div>';
				        html+='<img class="jiaofei" src="{{asset('/school/images/jiaofei-button.png')}}"  data="'+res.data[i].out_trade_no+'">';
				    html+='</div>';
				    $('.box').html('');
				    $('.box').append(html);
        		}
        	}
            if(res.data.length==0){
                window.location.href="{{url('/qr?qr_type=')}}"+'school'+"&store_id="+pay_store_id+"&stu_grades_no="+pay_stu_grades_no+"&stu_class_no="+pay_stu_class_no+"&student_name="+pay_name+"&student_user_mobile="+pay_phone+"&open_id="+open_id;
               
            }

        },"json");
    });
    $('.box').on("click",".jiaofei",function(){
    	var out_trade_no=$(this).attr('data');
    	location.href = "{{url('/api/weixin/paydetails?out_trade_no=')}}"+out_trade_no+"&open_id="+open_id;
    })
</script>
</body>
</html>