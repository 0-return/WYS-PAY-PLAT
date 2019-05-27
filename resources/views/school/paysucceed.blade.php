<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="">
    <title>缴费状态</title>
    <link rel="stylesheet" href="{{asset('/school/css/style.css')}}" media="all">
    <script type="text/javascript" src="{{asset('/school/js/Screen.js')}}"></script>
</head>
<body style="background-color: #f2f4f5;">


<div class="box">
    <div class="box_con1">
        <img src="{{asset('/school/images/chenggong.png')}}">
        <p class="jin"><span>-</span></span><span class="pay_jine">600.00</span></p>
        <p>支付成功</p>
    </div>
    <div class="box_con2">
        <div class="con_list">
            <label>缴费方式</label>
            <span></span>
        </div>
        <div class="con_list">
            <label>缴费时间</label>
            <span></span>
        </div>
        <div class="con_list">
            <label>订单号</label>
            <span></span>
        </div>
        <div class="con_list">
            <label>缴费备注</label>
            <span></span>
        </div>
    </div>
    <div class="box_con2">
        <div class="con_list">
            <label>学校</label>
            <span></span>
        </div>
        <div class="con_list">
            <label>年级</label>
            <span></span>
        </div>
        <div class="con_list">
            <label>班级</label>
            <span></span>
        </div>
        <div class="con_list">
            <label>学生姓名</label>
            <span></span>
        </div>
        <div class="con_list">
            <label>学号</label>
            <span></span>
        </div>
        <div class="con_list">
            <label>家长手机号</label>
            <span></span>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<script type="text/javascript" src="{{asset('/school/js/fastclick.js')}}"></script>
<script>$(function() {FastClick.attach(document.body);});</script>
<script>
    var out_trade_no="{{$_GET['out_trade_no']}}";
    $(document).ready(function(){
        
        $.post("{{url('/api/consumer/h5/order/show')}}",
        {
            out_trade_no:out_trade_no
        },function(res){
            console.log(res);
            $('.box .box_con2 .con_list').eq(0).find('span').html(res.data.pay_type_desc);
            $('.box .box_con2 .con_list').eq(1).find('span').html(res.data.pay_time);
            $('.box .box_con2 .con_list').eq(2).find('span').html(res.data.out_trade_no);
            $('.box .box_con2 .con_list').eq(3).find('span').html(res.data.batch_name);
            $('.box .box_con2 .con_list').eq(4).find('span').html(res.data.school.school_name);
            $('.box .box_con2 .con_list').eq(5).find('span').html(res.data.grade.stu_grades_name);
            $('.box .box_con2 .con_list').eq(6).find('span').html(res.data.class.stu_class_name);
            $('.box .box_con2 .con_list').eq(7).find('span').html(res.data.student_name);
            $('.box .box_con2 .con_list').eq(8).find('span').html(res.data.student_no);
            $('.box .box_con2 .con_list').eq(9).find('span').html(res.data.student_user_mobile);
            $('.pay_jine').html(res.data.pay_amount);

        },"json");
    });
</script>
</body>
</html>