

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>layuiAdmin 主页示例模板二</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
</head>
<body>

  <div class="layui-fluid">
    <div class="layui-row layui-col-space15">
      
      <div class="layui-col-sm6 layui-col-md3">
        <div class="layui-card">
          <div class="layui-card-header">
            今日收款
            <span class="layui-badge layui-bg-blue layuiadmin-badge">今</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font today"></p>
            
          </div>
        </div>
      </div>
      <div class="layui-col-sm6 layui-col-md3">
        <div class="layui-card">
          <div class="layui-card-header">
            昨日收款
            <span class="layui-badge layui-bg-cyan layuiadmin-badge">昨</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font yesterday"></p>
            
          </div>
        </div>
      </div>
      <div class="layui-col-sm6 layui-col-md3">
        <div class="layui-card">
          <div class="layui-card-header">
            本周收款
            <span class="layui-badge layui-bg-green layuiadmin-badge">周</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">
            <p class="layuiadmin-big-font week"></p>
            
          </div>
        </div>
      </div>
      <div class="layui-col-sm6 layui-col-md3">
        <div class="layui-card">
          <div class="layui-card-header">
            本月收款
            <span class="layui-badge layui-bg-orange layuiadmin-badge">月</span>
          </div>
          <div class="layui-card-body layuiadmin-card-list">

            <p class="layuiadmin-big-font month"></p>
            
          </div>
        </div>
      </div>   
      <div class="layui-col-sm12">
        <div class="layui-card">
          <div class="layui-card-header">
            
            <!-- <div class="layui-btn-group layuiadmin-btn-group">
              <a href="javascript:;" class="layui-btn layui-btn-primary layui-btn-xs">去年</a>
              <a href="javascript:;" class="layui-btn layui-btn-primary layui-btn-xs">今年</a>
            </div> -->
          </div>
          <div class="layui-card-body">
            <div class="layui-row">
              <div class="layui-col-sm8">
                  <div id="mainbox" style="width: 100%;height:332px;"></div>
              </div>
              <!-- ------------------------------------------------------------- -->
              <div class="layui-col-sm4">
                <div class="layuiadmin-card-list">
                  <p class="layuiadmin-normal-font">本周收款</p>
                  <span>同上周增长</span>
                  <div class="layui-progress layui-progress-big" lay-showPercent="yes">
                    <div class="layui-progress-bar"><span class="layui-progress-text one">30%</span></div>
                  </div>
                </div>
                <div class="layuiadmin-card-list">
                  <p class="layuiadmin-normal-font">本月收款</p>
                  <span>同上月增长</span>
                  <div class="layui-progress layui-progress-big" lay-showPercent="yes">
                    <div class="layui-progress-bar"><span class="layui-progress-text two">30%</span></div>
                  </div>
                </div>
                <div class="layuiadmin-card-list">
                  <p class="layuiadmin-normal-font">今年收款</p>
                  <span>同去年增长</span>
                  <div class="layui-progress layui-progress-big" lay-showPercent="yes">
                    <div class="layui-progress-bar"><span class="layui-progress-text three">30%</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

     
        
      </div>
    </div>
  </div>
  <div id="main" style="width: 600px;height:400px;"></div>
<script src="{{asset('/layuiadmin/layui/layui.js')}}"></script>  
<script src="{{asset('/school/js/jquery-2.1.4.js')}}"></script>   
<script src="{{asset('/layuiadmin/echarts.min.js')}}"></script>   
<script>
  var token = localStorage.getItem("token");
  var myChart = echarts.init(document.getElementById('mainbox'));
  $.post("{{url('/api/merchant/get_data')}}",
    {
      token:token,
      type:'school'
    }, 
    function(res){
      console.log(res);
      $('.today').html(res.data.today_total_amount);
      $('.yesterday').html(res.data.yesterday_total_amount);
      $('.week').html(res.data.week_total_amount);
      $('.month').html(res.data.month_total_amount);

      $('.one').html(res.data.week_increase);
      $('.one').parent().css('width',res.data.week_increase);
      $('.two').html(res.data.month_increase);
      $('.two').parent().css('width',res.data.month_increase);
      $('.three').html(res.data.year_increase);
      $('.three').parent().css('width',res.data.year_increase);

      var data=res.data.detail.date;//月
      var total=res.data.detail.total;//总
      var alipay=res.data.detail.alipay;//支付宝
      var weixin=res.data.detail.weixin;//微信
      
      
      var dataNew=[];
      var datatotal=[];
      var dataalipay=[];
      var dataweixin=[];
      for(var i=0;i<data.length;i++){
        var d=data[i];        
        var t=total[i];        
        var a=alipay[i];        
        var w=weixin[i];        
        dataNew.push(d);     
        datatotal.push(t);     
        dataalipay.push(a);     
        dataweixin.push(w);     
      }
      // console.log(dataNew);//月份




      option = {
        title: {
            text: ''
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data:['总和','支付宝','微信']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        
        xAxis: {
            type: 'category',
            // boundaryGap: false,
            data: dataNew
        },
        yAxis: {
            // type: 'value',
            axisLabel: {
              formatter: '{value} 元'
            }
            // name: '元'
        },
        series: [
            {
                name:'总和',
                type:'line',
                data:datatotal
            },
            {
                name:'支付宝',
                type:'line',
                itemStyle : {  
                  normal : {  
                      lineStyle:{  
                          color:'#25abee'  
                      }  
                  }  
                }, 
                data:dataalipay
            },
            {
                name:'微信',
                type:'line',
                itemStyle : {  
                  normal : {  
                      lineStyle:{  
                          color:'#00b700'  
                      }  
                  }  
                }, 
                data:dataweixin
            }
        ]
      };
      myChart.setOption(option);
  },"json");

  

  </script>
</body>
</html>