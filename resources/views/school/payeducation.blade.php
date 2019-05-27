<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="renderer" content="webkit">
    <meta name="description" content="">
    <title>缴费教育</title>
    <link rel="stylesheet" href="{{asset('/school/css/style.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/school/css/mobileSelect.css')}}">
    <script type="text/javascript" src="{{asset('/school/js/Screen.js')}}"></script>
    
    <style>
    	.cur{background-color: #eee !important;}
    </style>
</head>
<body style="background-color: #f2f4f5;">
<div class="box">
    <div class="ad"><img src="images/banner.png"></div>
    <div class="item" style="margin-top:.2rem;">
        <label>学校</label>
        <span class="school">{{$_GET['school_name']}}</span>
    </div>
    <div class="item">
        <label><i>*</i>年级</label>
        <span class="jt grades" id="grades">选择年级</span>
    </div>
    <div class="item">
        <label><i>*</i>班级</label>
        <span class="jt" id="class">选择班级</span>
    </div>
    <div class="item" style="margin-top:.2rem;">
        <label><i>*</i>学生姓名</label>
        <input type="text" class="s_name" placeholder="请输入学生姓名">
    </div>
    <div class="item">
        <label><i>*</i>家长手机号</label>
        <input type="tel" class="s_phone" placeholder="请输入联系电话">
    </div>
    <div class="btn_jf">进行缴费</div>
</div>
<div class="server"></div>

<input type="hidden" class="stu_grades_no" value="{{$_GET['stu_grades_no']}}">
<input type="hidden" class="stu_class_no" value="{{$_GET['stu_class_no']}}">



<div class="mobileSelect con1">
	<div class="grayLayer"></div>
	<div class="content">
		<div class="btnBar">
			<div class="fixWidth">
				<div class="cancel">取消</div>
				<div class="title">选择年级</div>
				<div class="ensure">确认</div>
			</div>
		</div>
		<div class="panel">
			<div class="fixWidth">
				<div class="wheels">
					<div class="wheel" style="width: 100%;">
						<ul class="selectContainer ul_con1" style="height: 160px;overflow-y: scroll;padding-top: .3rem;">
						</ul>
					</div>
				</div>
				<!-- <div class="selectLine"></div>
				<div class="shadowMask"></div> -->
			</div>
		</div>
	</div>
</div>
<div class="mobileSelect con2">
	<div class="grayLayer"></div>
	<div class="content">
		<div class="btnBar">
			<div class="fixWidth">
				<div class="cancel">取消</div>
				<div class="title">选择班级</div>
				<div class="ensure">确认</div>
			</div>
		</div>
		<div class="panel">
			<div class="fixWidth">
				<div class="wheels">
					<div class="wheel" style="width: 100%;">
						<ul class="selectContainer ul_con2" style="height: 160px;overflow-y: scroll;padding-top: .3rem;">
							
						</ul>
					</div>
				</div>
				<!-- <div class="selectLine"></div>
				<div class="shadowMask"></div> -->
			</div>
		</div>
	</div>
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

<script type="text/javascript" src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>
<!-- <script type="text/javascript" src="{{asset('/school/js/mobileSelect.js')}}"></script> -->
<script type="text/javascript" src="{{asset('/school/js/fastclick.js')}}"></script>
<script>$(function() {FastClick.attach(document.body);});</script>
<script>

	var store_id="{{$_GET['store_id']}}";
	var stu_grades_no="{{$_GET['stu_grades_no']}}";
	var stu_class_no="{{$_GET['stu_class_no']}}";
	var open_id="{{$_GET['open_id']}}";

    localStorage.setItem('pay_open_id', open_id);
    localStorage.setItem('pay_store_id', store_id);
    localStorage.setItem('pay_stu_grades_no', stu_grades_no);
	localStorage.setItem('pay_stu_class_no', stu_class_no);
	var h=$(window).height();//计算设备的高度 resize();//对浏览器窗口调整大小进行计数：
	 $(window).resize(function() {
	    if($(window).height()<h){
	        $('.server').hide();
	    }
	    if($(window).height()>=h){
	        $('.server').show();
    	}  
	});
	$('.qr').click(function(){
		$('.mask').hide();
	})
	$('.cancel').click(function(){
		$('.mobileSelect').removeClass('mobileSelect-show');
	});
	$('.ensure').click(function(){
		$('.mobileSelect').removeClass('mobileSelect-show');
	});


	$('.ul_con1').on("click","li",function(){//选择年级
		$('.ul_con1 li').removeClass('cur');
		$(this).addClass('cur');
		$('.stu_grades_no').val($(this).attr('data-id'));
		$('#grades').html($(this).html());
		$('.mobileSelect').removeClass('mobileSelect-show');

		// 班级
        $.post("{{url('/api/consumer/h5/class/lst')}}",
        {
        	store_id:store_id,
            stu_grades_no:$(this).attr('data-id')
        },function(res){
            console.log(res);
            var str2="";
            for(var n=0;n<res.data.length;n++){
            	if(stu_class_no==res.data[n].stu_class_no){
            		$('#class').html(res.data[n].stu_class_name);
            	}

            	
            	str2+='<li data-id="'+res.data[n].stu_class_no+'">'+res.data[n].stu_class_name+'</li>';
            	$('.ul_con2').html('');
            	$('.ul_con2').append(str2);

            }
            
        },"json");

	});
	$('.ul_con2').on("click","li",function(){//选择班级
		$('.ul_con2 li').removeClass('cur');
		$(this).addClass('cur');
		$('.stu_class_no').val($(this).attr('data-id'));
		$('#class').html($(this).html());
		$('.mobileSelect').removeClass('mobileSelect-show');
	});
	// ------------------------------------------

	$('#grades').click(function(){//展开年级列表
		$('.con1').addClass('mobileSelect-show');
	});
	$('#class').click(function(){//展开班级列表
		$('.con2').addClass('mobileSelect-show');
	});
	// ---------------------------------------
	


    $(document).ready(function(){
        // 年级
        $.post("{{url('/api/consumer/h5/grade/lst')}}",
        {
            store_id:store_id
        },function(res){
            console.log(res);
            var str1="";
        	for(var m=0;m<res.data.length;m++){
            	if(stu_grades_no==res.data[m].stu_grades_no){
            		$('#grades').html(res.data[m].stu_grades_name);
            	}
            	console.log(res.data.length);
            	
            	str1+='<li data-id="'+res.data[m].stu_grades_no+'">'+res.data[m].stu_grades_name+'</li>';
            	$('.ul_con1').html('');
           		$('.ul_con1').append(str1);
            }
            

            
        },"json");

        // 班级
        $.post("{{url('/api/consumer/h5/class/lst')}}",
        {
        	store_id:store_id,
            stu_grades_no:stu_grades_no
        },function(res){
            console.log(res);
            var str2="";
            for(var n=0;n<res.data.length;n++){
            	if(stu_class_no==res.data[n].stu_class_no){
            		$('#class').html(res.data[n].stu_class_name);
            	}

            	
            	str2+='<li data-id="'+res.data[n].stu_class_no+'">'+res.data[n].stu_class_name+'</li>';
            	$('.ul_con2').html('');
            	$('.ul_con2').append(str2);

            }
            
        },"json");

        // 公司信息
        $.post("{{url('/api/basequery/alipay_isv_info')}}",
        {
          store_id:store_id,                
        },function(res){
            console.log(res);

            if(res.status==1){
              $('.server').html(res.data.isv_name)
            }else{
                // layer.msg(res.message, {
                //     offset: '15px'
                //     ,icon: 2
                //     ,time: 3000
                // });

            }

        },"json");

    });

    $('.btn_jf').click(function(){
    	$.post("{{url('/api/consumer/h5/info/check')}}",
        {
        	store_id:store_id,
            stu_grades_no:$('.stu_grades_no').val(),
            stu_class_no:$('.stu_class_no').val(),
            student_name:$('.s_name').val(),
            student_user_mobile:$('.s_phone').val(),
        },function(res){
            console.log(res);
            localStorage.setItem('pay_name', $('.s_name').val());
            localStorage.setItem('pay_phone', $('.s_phone').val());


            if(res.status==1){
                location.href = res.data.url;
               
            }else if(res.status==2){
            	$('.mask').show();
            	$('.tip').html(res.message);
            }
        

        },"json");
    })

    
</script>
</body>
</html>