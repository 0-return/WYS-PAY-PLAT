<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
        $api->any('chencai/test',function(){


            // $data = base64_decode('b3JkZXJObz01YjdhOGVhYTdiYjkwYTI5Yjc0YzgxZGUmaXN2T3JkZXJObz0yMDE4MDgyMDE3MzAxMDQ4NDQ1Jml0ZW1zPTEtMXwyLTE=');
            // var_dump($data);die;

            $url='http://www.baidu.com';
            $data=[];


die;
$url = 'https://yh.yihoupay.com/api/school/pay/notify';

$data=array (
  'gmt_create' => '2018-08-20 22:41:17',
  'charset' => 'GBK',
  'seller_email' => '17060100808@163.com',
  'subject' => iconv('utf-8','gbk',  '学杂费'),
  'sign' => 'Zznhinsr0IwWAuqVCbC87v/tnSYjkxmYU3+eBdOvStpc3iYOu31cJcflH3ePSLDV+LMg/04lXJ6AP2Cg3ElXQmDstqD2aRVV45EAvj3Cf2z4h4niS8GK9C7VF37FmtiCr5i2PkyyE+FW780ATB7I485GFI151ghbqjpZC4WyMtU=',
  'buyer_id' => '2088512989071309',
  'invoice_amount' => '2.20',
  'notify_id' => 'ac77b9fa2ac0c3bcd1859fb16eae71eibh',
  'fund_bill_list' => '[{"amount":"2.20","fundChannel":"ALIPAYACCOUNT"}]',
  'notify_type' => 'trade_status_sync',
  'trade_status' => 'TRADE_SUCCESS',
  'receipt_amount' => '2.20',
  'buyer_pay_amount' => '2.20',
  'app_id' => '2016112803504802',
  'sign_type' => 'RSA',
  'seller_id' => '2088031453330839',
  'gmt_payment' => '2018-08-20 22:41:18',
  'notify_time' => '2018-08-20 22:41:18',
  'passback_params' => 'b3JkZXJObz01YjdhOGVhYTdiYjkwYTI5Yjc0YzgxZGUmaXN2T3JkZXJObz0yMDE4MDgyMDE3MzAxMDQ4NDQ1Jml0ZW1zPTEtMXwyLTE=',
  'version' => '1.0',
  'out_trade_no' => '5b7ad2f52d305c2888e634d3',
  'total_amount' => '2.20',
  'trade_no' => '2018082021001004300564206580',
  'auth_app_id' => '2018030402315233',
  'buyer_logon_id' => '156****1999',
  'point_amount' => '0.00',
);

            $return  = \App\Common\Tool\Send::curl($url,$data);

            var_dump($return);

            die;

            echo base64_decode('b3JkZXJObz01YjdhOGVhYTdiYjkwYTI5Yjc0YzgxZGUmaXN2T3JkZXJObz0yMDE4MDgyMDE3MzAxMDQ4NDQ1Jml0ZW1zPTEtMXwyLTE=');die;
/*
$arr=[
    1,9,'a','B','A',2,6,5
];

sort($arr);

var_dump($arr);die;
*/

/*
            $ali_config = \App\Models\AlipayIsvConfig::where('config_id','15731958618')->first();

            var_dump($ali_config->toArray());


die;*/





// $json='{"addField":"V1.0.1","amount":"1","characterSet":"00","mercId":"800290000007906","opSys":"3","orgNo":"11658","payChannel":"ALIPAY","signType":"MD5","total_amount":"1","tradeNo":1533538076,"trmNo":"XB006439","trmTyp":"T","txnTime":"20180806024756","version":"V1.0.0","signValue":"7d8d52383f33a5987428b969f8139d45"}';

// $data=json_decode($json,true);




$aop = new \App\Common\XingPOS\Aop();
$aop->key = '9FF13E7726C4DFEB3BED750779F59711';

$aop->op_sys = '3';//操作系统
// $aop->latitude = '31';//纬度
// $aop->longitude = '118';//精度
$aop->org_no = '11658';//机构号
$aop->merc_id = '800290000007906';//商户号
$aop->trm_no = 'xx209831';//设备号
// $aop->opr_id = '8972';//操作员
$aop->trm_typ = 'T';//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
$aop->trade_no = '88888';//商户单号
$aop->txn_time = '201808100217';//设备交易时间
// $aop->add_field = 'V1.0.1';
$aop->version = 'V1.0.0';

$aop->env = true;

$data = [
    'amount' => '1',
    'total_amount' => '1',
    'payChannel' => 'ALIPAY',
];

$request_obj_pay = new  \App\Common\XingPOS\Request\XingPaySaoMaZhiFuKeHuZhuSao();
$request_obj_pay->setBizContent($data);
$return = $aop->execute($request_obj_pay);

echo '<hr><pre>对方返回值：';

print_r($return);//die;
 






die;
/*
$str='%E9%AA%8C%E8%AF%81%E7%AD%BE%E5%90%8D%E5%A4%B1%E8%B4%A5';
$str='%E9%AA%8C%E8%AF%81%E7%AD%BE%E5%90%8D%E5%A4%B1%E8%B4%A5';
$str='%E5%AE%89%E5%85%A8%E4%B8%AD%E5%BF%83%E7%94%9F%E6%88%90%E5%AF%86%E9%92%A5%E5%A4%B1%E8%B4%A5';

$str='%E5%AE%89%E5%85%A8%E4%B8%AD%E5%BF%83%E7%94%9F%E6%88%90%E5%AF%86%E9%92%A5%E5%A4%B1%E8%B4%A5';
echo base64_decode($str);
echo '<hr>';
echo urldecode($str);

die;*/


$aop = new \App\Common\XingPOS\Aop();
$aop->key = 'DCC5865D21BAFF74FB9BEC79A42D5343';
$aop->op_sys = '3';//操作系统
$aop->latitude = '0';//纬度
$aop->longitude = '0';//精度
$aop->org_no = '4557';//机构号
$aop->merc_id = '800290000005247';//商户号
$aop->trm_no = '209831';//设备号
$aop->opr_id = '8972';//操作员
$aop->trm_typ = 'T';//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
$aop->trade_no = time();//商户单号
$aop->txn_time = date('Ymdhis', time());//设备交易时间
$aop->add_field = 'V1.0.1';
$aop->version = 'V1.0.1';

$aop->env = true;


$data = [
'authCode'=>'201815464156464',
    'Amount' => '1',
    'total_amount' => '1',
    'payChannel' => 'ALIPAY',
];

$request_obj_pay = new  \App\Common\XingPOS\Request\XingPaySaoMaZhiFuKeHuZhuSao();
$request_obj_pay->setBizContent($data);
$return = $aop->execute($request_obj_pay);
echo '<pre>';
print_r($return);





die;
/*
$aop = new \App\Common\XingPOS\Aop();
$aop->key = '9773BCF5BAC01078C9479E67919157B8';
$aop->org_no = '518';
$aop->merc_id = '800641000000249';
$aop->url = 'http://sandbox.starpos.com.cn/emercapp';//测试地址


$data = [
    'log_no' => time(),
    'stoe_id'=>time(),
    'imgTyp'=>6,
    'imgNm'=>'门头照.png',
    // 'imgFile'=>base64_encode(file_get_contents(dirname(public_path()).'/test.png'))
    'imgFile'=>'@'.dirname(public_path()).'/test.png'
];


$request_obj = new  \App\Common\XingPOS\Request\XingStoreTuPianShangChuan();
    $request_obj->setBizContent($data);
$return = $aop->execute($request_obj);

var_dump(
    $return);die;
dd($return);


*/




/*
//商户查询没问题
    $aop = new \App\Common\XingPOS\Aop();
    $aop->key = '9773BCF5BAC01078C9479E67919157B8';
    $aop->version = 'V1.0.1';
    $aop->org_no = '518';
    $aop->merc_id = '800641000000249';
    $aop->url = 'http://sandbox.starpos.com.cn/emercapp';//测试地址

    $data = [
    ];

    $request_obj = new \App\Common\XingPOS\Request\XingStoreShangHuChaXun();
    $request_obj->setBizContent($data);
    $return = $aop->execute($request_obj);
    var_dump($return);
*/


 


die;
            // 接口 2.1   商户查询

            $config=[
                'url'=>'http://sandbox.starpos.com.cn/emercapp',
                'version'=>'V1.0.1'
            ];
            $aop = new \App\Common\XingPOS\Aop();
                $aop -> key = '';
                $aop -> version = 'V1.0.1';
                $aop -> org_no = '';
                $aop -> merc_id = '';
                $aop -> mer333c_id = '';
                $aop -> url = 'http://sandbox.starpos.com.cn/emercapp';//测试地址

            $store_search_obj = new \App\Common\XingPOS\Request\XingStoreShangHuChaXun();
            $store_search_obj->setBizContent($data=[]);
            $data = $aop->execute($store_search_obj);

            var_dump($data);
        });


//merchant表邓旒   需要token
    $api->group(['namespace' => 'App\Api\Controllers\School', 'prefix' => 'school', 'middleware' => 'merchant.api'], function ($api) {

      // 公告
        $api->any('teacher/announce/add','AnnounceController@add');
        $api->any('teacher/announce/save','AnnounceController@save');
        $api->any('teacher/announce/del','AnnounceController@del');
        $api->any('teacher/announce/lst','AnnounceController@lst');
        $api->any('teacher/announce/cate/lst','AnnounceController@cateLst');
        $api->any('teacher/announce/show','AnnounceController@show');


    	//创建学校
        $api->any('teacher/typelst','IndexController@typeLst');
        $api->any('teacher/add','IndexController@add');
        $api->any('teacher/save','IndexController@save');
        $api->any('teacher/lst','IndexController@lst');
        $api->any('teacher/show','IndexController@show');
        $api->any('teacher/check','IndexController@check');
        $api->any('teacher/sync','IndexController@sync');
        $api->any('teacher/ali/auth/url','IndexController@aliAuth');

        //年级
        $api->any('teacher/grade/add','GradeController@add');
        $api->any('teacher/grade/lst','GradeController@lst');
        $api->any('teacher/grade/show','GradeController@show');
        $api->any('teacher/grade/save','GradeController@save');
        $api->any('teacher/grade/del','GradeController@del');



        //班级
        $api->any('teacher/class/add','ClassController@add');
        $api->any('teacher/class/lst','ClassController@lst');
        $api->any('teacher/class/show','ClassController@show');
        $api->any('teacher/class/save','ClassController@save');
        $api->any('teacher/class/del','ClassController@del');



        //学生
        $api->any('teacher/stu/import','StudentController@importExcel');
        $api->any('teacher/stu/add','StudentController@add');
        $api->any('teacher/stu/lst','StudentController@lst');
        $api->any('teacher/stu/show','StudentController@show');
        $api->any('teacher/stu/save','StudentController@save');
        $api->any('teacher/stu/del','StudentController@del');

        //缴费模板
        $api->any('teacher/template/add','PayTemplateController@add');
        $api->any('teacher/template/lst','PayTemplateController@lst');
        $api->any('teacher/template/show','PayTemplateController@show');
        $api->any('teacher/template/save','PayTemplateController@save');
        $api->any('teacher/template/check','PayTemplateController@check');
        $api->any('teacher/template/del','PayTemplateController@del');


        //缴费项目
        // $api->any('teacher/payitem/add','PayItemController@add');//newAdd
        $api->any('teacher/payitem/add','PayItemController@newAdd');//newAdd
        $api->any('teacher/payitem/lst','PayItemController@lst');
        $api->any('teacher/payitem/show','PayItemController@show');
        $api->any('teacher/payitem/save','PayItemController@save');
        $api->any('teacher/payitem/check','PayItemController@check');
        $api->any('teacher/payitem/del','PayItemDelController@del');



        $api->any('teacher/payitem/remind','PayItemController@remind');
        $api->any('teacher/payitem/remind/one','PayItemController@remindOne');

        //订单
        $api->any('teacher/order/lst','OrderController@lst');
        $api->any('teacher/order/show','OrderController@show');
        $api->any('teacher/order/send','OrderController@sendOrder');//按照项目发送支付宝账单
        $api->any('teacher/order/send/one','OrderController@sendOneOrder');//根据单号发送支付宝账单


        // 新流程：直接excel导入订单和项目
        $api->any('teacher/excel/order/import','ExcelOrderController@import');



        // 教师管理
/*        $api->any('teacher/ter/add','TeacherController@add');
        $api->any('teacher/ter/show','TeacherController@show');
        $api->any('teacher/ter/save','TeacherController@save');
        $api->any('teacher/ter/del','TeacherController@del');*/
        $api->any('teacher/ter/lst','TeacherController@lst');
        $api->any('teacher/ter/typelst','TeacherController@typelst');
        $api->any('teacher/ter/relate','TeacherController@relate');


        // 
        $api->any('teacher/login/info','TeacherController@loginerInfo');
        $api->any('teacher/login/class/lst','TeacherController@loginerClassLst');

        $api->any('teacher/ter/class/unbind','TeacherController@unbind');




        //教育缴费情况统计
        $api->any('teacher/stat/pay','StatPayController@pay');




    	$api->any('teacher/make/order','OrderController@make');
    		$api->any('test',function(){echo 8888;});
    });

    $api->group(['namespace' => 'App\Api\Controllers\School', 'prefix' => 'school'], function ($api) {


        //学校添加异步通知
        $api->any('pay/notify','NotifyController@index');
    });









//users表   需要token
    $api->group(['namespace' => 'App\Api\Controllers\School\Agent', 'prefix' => 'school/agent', 'middleware' => 'user.api'], function ($api) {

        $api->any('typelst','SchoolController@typeLst');

        $api->any('save','AgentSchoolController@save');
        $api->any('show','AgentSchoolController@show');
        $api->any('sync','AgentSchoolController@sync');



        $api->any('check','AgentSchoolController@check');
        $api->any('lst','SchoolController@lst');
        $api->any('grade/lst','GradeController@lst');
        $api->any('class/lst','ClassController@lst');
        $api->any('batch/lst','PayItemController@lst');
        $api->any('order/lst','OrderController@lst');
        $api->any('order/show','OrderController@show');

        // $api->any('order/show','OrderController@show');

        // $api->any('make/order','OrderController@make');


            // $api->any('test',function(){echo 8888;});
    });









});