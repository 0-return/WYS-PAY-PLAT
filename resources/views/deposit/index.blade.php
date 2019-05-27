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
  <link rel="stylesheet" href="{{asset('/deposit/deposit.css')}}" media="all">

</head>
<body class="layui-layout-body">
  
  <div id="LAY_app">
    <div class="layui-layout layui-layout-admin">
      <div class="layui-header" style='background-color: #1E9FFF;'>
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
            <a href="javascript:;">
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
            <a href="javascript:;" layadmin-event="logout" class="logout"><!-- <embed src="{{asset('/layuiadmin/img/tuichudenglu.svg')}}" width="14px" height="14px" type="image/svg+xml"/> -->退出</a>
          </li>
          <li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-unselect>
            <a href="javascript:;" layadmin-event="more"><i class="layui-icon layui-icon-more-vertical"></i></a>
          </li>
        </ul>
      </div>
      
      <!-- 侧边菜单 -->
      <div class="layui-side layui-side-menu"><!-- background-color: #0085E8 !important; -->
        <div class="layui-side-scroll">
          <div class="layui-logo" style='background-color: #0085E8 !important;'>
            <span>服务商管理后台</span>
          </div>
          
          <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">
            
            <!-- <li data-name="component" class="layui-nav-item layui-nav-itemed">
              <a lay-href="{{url('/d/home')}}" lay-tips="对账统计" lay-direction="2">
                <i class="layui-icon layui-icon-component"></i>
                <cite>对账统计</cite>
              </a>
            </li>

            
            <li data-name="home" class="layui-nav-item">
              <a href="javascript:;" lay-tips="押金流水" lay-direction="2">
                <i class="layui-icon layui-icon-layer"></i>
                <cite>押金流水</cite>
              </a>
              <dl class="layui-nav-child">
                
                <dd data-name="console">
                  <a lay-href="{{url('/d/cashflow')}}">对账统计</a>
                  <a lay-href="{{url('/d/cashflow')}}">押金流水</a>
                </dd>                              
              </dl>
            </li>
 -->
            <li data-name="component" class="layui-nav-item item_huodong layui-nav-itemed">
              <a href="javascript:;" lay-tips="押金流水" lay-direction="2">
                <i class="layui-icon layui-icon-list"></i>
                <cite>押金流水</cite>
              </a>
              <dl class="layui-nav-child">
                <dd><a lay-href="{{url('/d/cashflow')}}">押金流水</a></dd>
                <dd data-name="classmanage" class="layui-nav-itemed">
                  <a href="javascript:;">对账统计<span class="layui-nav-more"></span></a>
                  <dl class="layui-nav-child">
                    <dd><a lay-href="{{url('/d/home')}}">交易流水对账</a></dd>
                    <dd><a lay-href="{{url('/d/depositwater')}}">押金流水对账</a></dd>
                  </dl>
                </dd>    
              </dl>
            </li>  
            

          </ul>
            
            
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
            <li lay-id="{{url('/d/depositwater')}}" lay-attr="{{url('/d/depositwater')}}" class="layui-this">押金流水对账</li>
          </ul>
        </div>
      </div>
      
      
      <!-- 主体内容 -->
      <div class="layui-body" id="LAY_app_body">
        <div class="layadmin-tabsbody-item layui-show">
          <iframe src="{{url('/d/depositwater')}}" frameborder="0" class="layadmin-iframe"></iframe>
        </div>
      </div>
      
      <!-- 辅助元素，一般用于移动设备下遮罩 -->
      <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
  </div>

  <script src="{{asset('/layuiadmin/layui/layui.js')}}"></script> 
  <script src="{{asset('/deposit/deposit.js')}}"></script> 
  <script>

  layui.config({
    base: '../../layuiadmin/' //静态资源所在路径
  }).extend({
    index: 'lib/index' //主入口模块
  }).use(['index','element'], function(){
    var $ = layui.$;

    var token = localStorage.getItem("Deposittoken");
   

    $('.logout').click(function(){
      localStorage.removeItem("Deposittoken");
      localStorage.clear();
      window.location.reload();
    });
    
    // 未登录,跳转登录页面
    $(document).ready(function(){
        var token = localStorage.getItem("Deposittoken");
        // var admin = localStorage.getItem("admin");
        // var level = localStorage.getItem("level");
        if(token==null){
          window.location.href="{{url('/d/login')}}"; 
        }
    })

   

    
       


  });
  </script>
</body>
</html>


