<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>服务商管理后台</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
  <link rel="stylesheet" href="{{asset('/layuiadmin/layui/css/layui.css')}}" media="all">
  <link rel="stylesheet" href="{{asset('/layuiadmin/style/admin.css')}}" media="all">
 
</head>
<body class="layui-layout-body">
  
  <div id="LAY_app">
    <div class="layui-layout layui-layout-admin">
      <div class="layui-header">
        <!-- 头部区域 -->
        <ul class="layui-nav layui-layout-left">
          <li class="layui-nav-item layadmin-flexible" lay-unselect>
            <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
              <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
            </a>
          </li>
          
          <li class="layui-nav-item" lay-unselect>
            <a href="javascript:;" layadmin-event="refresh" title="刷新">
              <i class="layui-icon layui-icon-refresh-3"></i>
            </a>
          </li>
          <!-- <li class="layui-nav-item layui-hide-xs" lay-unselect>
            <input type="text" placeholder="搜索..." autocomplete="off" class="layui-input layui-input-search" layadmin-event="serach" lay-action="template/search.html?keywords="> 
          </li> -->
        </ul>
        <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">
          
          <li class="layui-nav-item layui-hide-xs" lay-unselect>
            <a href="javascript:;" layadmin-event="fullscreen">
              <i class="layui-icon layui-icon-screen-full"></i>
            </a>
          </li>
          <li class="layui-nav-item" lay-unselect>
            <a class="seetixian" lay-href="" data=''>
              <cite class="store_name"></cite>
            </a>
            <!-- <dl class="layui-nav-child">
              <dd><a lay-href="set/user/info.html">基本资料</a></dd>
              <dd><a lay-href="set/user/password.html">修改密码</a></dd>              
            </dl> -->
          </li>

          <!-- <li class="layui-nav-item layui-hide-xs" lay-unselect>
            <a target="_blank" href="https://fchelp.cloud.alipay.com/help.htm?tntInstId=EBBYLCCN&helpCode=SCE_00000888" layadmin-event="" class="">帮助中心</a>
          </li> -->
          <li class="layui-nav-item layui-hide-xs" lay-unselect>
            <a href="javascript:;" layadmin-event="logout" class="logout"><embed src="{{asset('/layuiadmin/img/tuichudenglu.svg')}}" width="14px" height="14px" type="image/svg+xml"/>退出</a>
          </li>
          <li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-unselect>
            <a href="javascript:;" layadmin-event="more"><i class="layui-icon layui-icon-more-vertical"></i></a>
          </li>
        </ul>
      </div>
      
      <!-- 侧边菜单 -->
      <div class="layui-side layui-side-menu" style="background-color: #2F4056 !important;">
        <div class="layui-side-scroll">
          <div class="layui-logo">
            <span>服务商管理后台</span>
          </div>
          
          <ul class="layui-nav layui-nav-tree box" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
            <li data-name="home" class="layui-nav-item layui-nav-itemed item_daili" data="代理商管理">
              <a href="javascript:;" lay-tips="代理商管理" lay-direction="2">
                <i class="layui-icon layui-icon-home"></i>
                <cite>代理商管理</cite>
              </a>
              <dl class="layui-nav-child">                
                <dd data-name="console"><a lay-href="{{url('/user/agentlist')}}" data="代理商列表">代理商列表</a></dd>
                <dd data-name="grid"><a lay-href="{{url('/user/qrcode')}}" data="我的激活码">我的激活码</a></dd>              
              </dl>
            </li>
            <li data-name="template" class="layui-nav-item item_mendian" data='门店管理'>
              <a href="javascript:;" lay-tips="门店管理" lay-direction="2">
                <i class="layui-icon layui-icon-layer"></i>
                <cite>门店管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/storelist')}}" data="门店列表">门店列表</a></dd>
                <dd><a lay-href="{{url('/user/tradelist')}}" data="交易流水">交易流水</a></dd>
                <dd><a lay-href="{{url('/user/reconciliation?user_id=&user_name=')}}" data="对账查询">对账查询</a></dd>
                <dd><a lay-href="{{url('/user/flowerlist')}}" data="花呗分期流水">花呗分期流水</a></dd>
                <dd><a lay-href="{{url('/user/makemoney')}}" data="商户打款记录">商户打款记录</a></dd>
              </dl>
            </li>
            <li data-name="template" class="layui-nav-item item_huodong" data='行业解决方案'>
              <a href="javascript:;" lay-tips="行业解决方案" lay-direction="2">
                <i class="layui-icon layui-icon-template"></i>
                <cite>行业解决方案</cite>
              </a>
              <dl class="layui-nav-child">                
                <dd data-name="classmanage">
                  <a href="javascript:;" data='教育缴费'>教育缴费</a>
                  <dl class="layui-nav-child">
                    <dd><a lay-href="{{url('/user/waterlist')}}" data='交易流水'>交易流水</a></dd>
                    <dd><a lay-href="{{url('/user/schoollist')}}" data='学校列表'>学校列表</a></dd>
                  </dl>
                </dd> 
                <dd data-name="classmanage">
                  <a href="javascript:;" data='押金支付'>押金支付</a>
                  <dl class="layui-nav-child">
                    <dd><a lay-href="{{url('/user/depositwater')}}" data='押金流水'>押金流水</a></dd>
                    <dd><a lay-href="{{url('/user/depositacount')}}" data='对账账单'>对账账单</a></dd>
                  </dl>
                </dd>    
              </dl>
            </li>
            <li data-name="template" class="layui-nav-item item_zhifu" data='交易数据管理'>
              <a href="javascript:;" lay-tips="交易数据管理" lay-direction="2">
                <i class="layui-icon layui-icon-form"></i>
                <cite>交易数据管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/transactionlist')}}" data='交易榜'>交易榜</a></dd>                
              </dl>
            </li>
            <li data-name="template" class="layui-nav-item item_huodong" data='活动管理'>
              <a href="javascript:;" lay-tips="活动管理" lay-direction="2">
                <i class="layui-icon layui-icon-template"></i>
                <cite>活动管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/alipayred')}}" data='支付宝红包'>支付宝红包</a></dd>
                <dd data-name="classmanage" class="layui-nav-itemed">
                  <a href="javascript:;" data='京东白条'>京东白条</a>
                  <dl class="layui-nav-child">
                    <dd><a lay-href="{{url('/user/jdwhitebar')}}" data='白条数据录入'>白条数据录入</a></dd>
                  </dl>
                </dd>    
              </dl>
            </li>            
            <li data-name="template" class="layui-nav-item item_shangjin" data='赏金管理'>
              <a href="javascript:;" lay-tips="赏金管理" lay-direction="2">
                <i class="layui-icon layui-icon-rmb"></i>
                <cite>赏金管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/reward')}}" data='赏金列表'>赏金列表</a></dd>
                <dd><a lay-href="{{url('/user/putforward')}}" data='提现记录'>提现记录</a></dd>
                <dd><a lay-href="{{url('/user/settlement')}}" data='赏金结算'>赏金结算</a></dd>
                <dd><a lay-href="{{url('/user/cashset')}}" data='提现设置'>提现设置</a></dd>
                <dd><a lay-href="{{url('/user/settlerecord')}}" data='结算记录'>结算记录</a></dd>
              </dl>              
            </li>
            <li data-name="template" class="layui-nav-item item_shangjin" data='赏金管理'>
              <a href="javascript:;" lay-tips="代付管理" lay-direction="2">
                <i class="layui-icon layui-icon-dollar"></i>
                <cite>代付管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/yytong')}}" data='银盈通'>银盈通</a></dd>
                
              </dl>              
            </li>
            <li data-name="template" class="layui-nav-item item_ad" data='广告管理'>
              <a href="javascript:;" lay-tips="广告管理" lay-direction="2">
                <i class="layui-icon layui-icon-note"></i>
                <cite>广告管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/ad')}}" data='广告列表'>广告列表</a></dd>
              </dl>              
            </li>
            <li data-name="template" class="layui-nav-item item_code" data='二维码统一管理'>
              <a href="javascript:;" lay-tips="二维码统一管理" lay-direction="2">
                <i class="layui-icon layui-icon-table"></i>
                <cite>二维码统一管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/qrcodemanage')}}" data='商户收款空码'>商户收款空码</a></dd>
                <dd><a lay-href="{{url('/user/percode')}}" data='个人码合并'>个人码合并</a></dd>
              </dl>              
            </li>
            <li data-name="template" class="layui-nav-item item_xinxi" data='信息管理'>
              <a href="javascript:;" lay-tips="信息管理" lay-direction="2">
                <i class="layui-icon layui-icon-notice"></i>
                <cite>信息管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/appmsg')}}" data='app消息'>app消息</a></dd>
                <dd><a lay-href="{{url('/user/bannerlist')}}" data='banner列表'>banner列表</a></dd>
              </dl>              
            </li>
            <li data-name="template" class="layui-nav-item item_juese" data='角色权限管理'>
              <a href="javascript:;" lay-tips="角色权限管理" lay-direction="2">
                <i class="layui-icon layui-icon-group"></i>
                <cite>角色权限管理</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/role')}}" data='角色管理'>角色管理</a></dd>
                <dd><a lay-href="{{url('/user/power')}}" data='权限管理'>权限管理</a></dd>
              </dl>
            </li>
            <li data-name="template" class="layui-nav-item item_zhifu" data='支付配置'>
              <a href="javascript:;" lay-tips="支付配置" lay-direction="2">
                <i class="layui-icon layui-icon-template"></i>
                <cite>支付配置</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/alipayconfirm')}}" data='支付宝应用配置'>支付宝应用配置</a></dd>
                <dd><a lay-href="{{url('/user/wechatconfirm')}}" data='微信应用配置'>微信应用配置</a></dd>
                <dd><a lay-href="{{url('/user/jdconfigure')}}" data='京东金融配置'>京东金融配置</a></dd>
                <dd><a lay-href="{{url('/user/newworld')}}" data='新大陆配置'>新大陆配置</a></dd>
                <dd><a lay-href="{{url('/user/hrtconfig')}}" data='和融通配置'>和融通配置</a></dd>
              </dl>
            </li>
            <li data-name="template" class="layui-nav-item item_xitong" data='系统配置'>
              <a href="javascript:;" lay-tips="系统配置" lay-direction="2">
                <i class="layui-icon layui-icon-download-circle"></i>
                <cite>系统配置</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/user/updata')}}" data='系统更新'>系统更新</a></dd>
                <dd><a lay-href="{{url('/user/appconfig')}}" data='APP配置'>APP配置</a></dd>
                <dd><a lay-href="{{url('/user/pushconfig')}}" data='推送配置'>推送配置</a></dd>
                <dd><a lay-href="{{url('/user/msgconfig')}}" data='短信配置'>短信配置</a></dd>                
                <dd><a lay-href="{{url('/user/storeconfig')}}" data='门店配置'>门店配置</a></dd>
                <dd><a lay-href="{{url('/user/devicemanage')}}" data='设备列表'>设备列表</a></dd>
                <dd><a lay-href="{{url('/user/deviceconfig')}}" data='设备配置'>设备配置</a></dd>                 
                <!-- <dd>
                  <a lay-href="javascript:;">设备管理</a>
                  <dl class="layui-nav-child" style="padding-left:15px;">
                    <dd><a lay-href="{{url('/user/devicemanage')}}">设备列表</a></dd>
                    <dd><a lay-href="{{url('/user/deviceconfig')}}">设备配置</a></dd>                    
                  </dl>
                </dd>  --> 
                <dd><a lay-href="{{url('/user/mqtt')}}" data='MQTT推送'>MQTT推送</a></dd>              
              </dl>
            </li>
            
            
            
          </ul>
        </div>
      </div>

      <!-- 页面标签 -->
      <div class="layadmin-pagetabs" id="LAY_app_tabs">
        <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
        <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
        <div class="layui-icon layadmin-tabs-control layui-icon-down">
          <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
            <li class="layui-nav-item" lay-unselect>
              <a href="javascript:;"></a>
              <dl class="layui-nav-child layui-anim-fadein">
                <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
              </dl>
            </li>
          </ul>
        </div>
        <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
          <ul class="layui-tab-title" id="LAY_app_tabsheader">
            <li lay-id="{{url('/user/agentlist')}}" lay-attr="{{url('/user/agentlist')}}" class="layui-this">代理商列表</li>
          </ul>
        </div>
      </div>
      
      
      <!-- 主体内容 -->
      <div class="layui-body" id="LAY_app_body">
        <div class="layadmin-tabsbody-item layui-show">
          <iframe src="{{url('/user/agentlist')}}" frameborder="0" class="layadmin-iframe"></iframe>
        </div>
      </div>
      
      <!-- 辅助元素，一般用于移动设备下遮罩 -->
      <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
  </div>

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script>

  layui.config({
    base: '../../layuiadmin/' //静态资源所在路径
  }).extend({
    index: 'lib/index' //主入口模块
  }).use(['index','element'], function(){
    var $ = layui.$;

    var token = localStorage.getItem("Usertoken");
    var s_agentname = localStorage.getItem("s_agentname");
    var level = localStorage.getItem("level");
    var l_user_id = localStorage.getItem("l_user_id");
    $('.store_name').html(s_agentname);
    $('.seetixian').attr('data',l_user_id)
    // console.log(token);

    $('.logout').click(function(){
      localStorage.removeItem("Usertoken");
      localStorage.clear();
      window.location.reload();
    });
    
    // 未登录,跳转登录页面
    $(document).ready(function(){
        var token = localStorage.getItem("Usertoken");
        var admin = localStorage.getItem("admin");
        // var level = localStorage.getItem("level");
        if(token==null){
          window.location.href="{{url('/user/login')}}"; 
        }
     

    })

    var permissions = localStorage.getItem("permissions");
    var str=JSON.parse(permissions);
    var arr=[]
    for(var i=0;i<str.length;i++){
      var aa=str[i].name
      arr.push(aa)
    }
    console.log(arr)


    if(level != 0){
      // 权限管理+++++++++++++
      $('ul.box li').each(function(index,item){
        // console.log($(this).attr('data'))
        // if($.inArray($(this).attr('data'),arr)==-1){
        //   $(this).hide()
        // }
      })
      $('ul.box li dl dd').each(function(index,item){
        // console.log($(this).find('a').attr('data'))

        // console.log($.inArray($(this).find('a').attr('data'),arr))

        if($.inArray($(this).find('a').attr('data'),arr)==-1){
          $(this).find('a').hide()
        }
        
      })
    }



    $('.seetixian').click(function(){
      $(this).attr('lay-href','{{url('/user/cashwithdrawal?')}}'+$(this).attr('data'))
    })



   

    
       


  });
  </script>
</body>
</html>


