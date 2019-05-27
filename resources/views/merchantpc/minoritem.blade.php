<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>模板小项</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
    <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
    
</head>
<body>

<div class="layui-fluid">
    <div class="layui-card">
      <div class="layui-card-body layui-row layui-col-space10">
        <div class="layui-form">
           
            <table class="layui-table">
              <colgroup>
                <col width="150">
                <col width="150">
                <col width="150">
                <col width="150">
                <col width="250">
              </colgroup>
              <thead>
                <tr>
                  <th>缴费名称</th>
                  <th>缴费金额</th>
                  <th>数量</th>
                  <th>状态</th>
                  <th>是否必交(Y或N)</th>
                </tr> 
              </thead>
              <tbody>            
                
              </tbody>
            </table>


            <div class="layui-form-item">
              <div class="layui-inline">
                <label class="layui-form-label">总金额:</label>
                <div class="layui-form-mid layui-word-aux"><span class="amount">0.00</span>&nbsp;&nbsp;&nbsp;必缴<span class="mustpay">0.00</span>&nbsp;&nbsp;&nbsp;非必缴<span class="nomustpay">0.00</span></div>
              </div>
            </div>

            
        </div>        
      </div>
    </div>
    
    
</div>

<input type="hidden" class="store_id" value="">
<input type="hidden" class="gradeid" value="">
<input type="hidden" class="classid" value="">
<input type="hidden" class="templateid" value="">
<input type="hidden" class="student_code" value="">

<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
<script>
    var token = localStorage.getItem("token");
    var str=location.search;
    var out_trade_no=str.split('?')[1];

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
            ,formSelects = layui.formSelects;

        $.ajax({
            url : "{{url('/api/school/teacher/order/show')}}",
            data : {token:token,out_trade_no:out_trade_no},
            type : 'post',
            success : function(res) {
                console.log(res);
                var sum=0;
                var sum2=0;
                var str="";
                for(var j=0;j<res.data.all_item.length;j++){
                    
                    str+='<tr>';
                      str+='<td>'+res.data.all_item[j].item_name+'</td>';
                      str+='<td>'+res.data.all_item[j].item_price+'</td>';
                      str+='<td>'+res.data.all_item[j].item_number+'</td>';
                      if(res.data.all_item[j].status==1){
                        str+='<td style="color: #009688;">成功</td>';
                      }else if(res.data.all_item[j].status==2){
                        str+='<td>等待支付</td>';
                      }else if(res.data.all_item[j].status==3){
                        str+='<td>失败</td>';
                      }else if(res.data.all_item[j].status==3){
                        str+='<td>关闭</td>';
                      }else if(res.data.all_item[j].status==3){
                        str+='<td>退款中</td>';
                      }else if(res.data.all_item[j].status==3){
                        str+='<td>已退款</td>';
                      }


                      
                      str+='<td>'+res.data.all_item[j].item_mandatory+'</td>';
                    str+='</tr>';

                    
                    if(res.data.all_item[j].item_mandatory=='Y'){
                        sum=sum+parseFloat(res.data.all_item[j].item_number*res.data.all_item[j].item_price);
                    }else{
                        sum2=sum2+parseFloat(res.data.all_item[j].item_number*res.data.all_item[j].item_price);
                    }
                    // console.log(sum+sum2);
                    $('.amount').html((sum+sum2).toFixed(2));
                    $('.mustpay').html(sum.toFixed(2));
                    $('.nomustpay').html(sum2.toFixed(2));

                }
                
                $('tbody').html('');
                $('tbody').append(str);
            },
            error : function(data) {
                alert('查找板块报错');
            }
        });   

     

    });
</script>
</body>
</html>
