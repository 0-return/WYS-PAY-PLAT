<?php

namespace App\Api\Controllers\School;

use Illuminate\Support\Facades\DB;

/*
    学校  增删查改
*/

class OrderController extends \App\Api\Controllers\BaseController
{


    /*
        列表
    */
    public function lst()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $obj = new \App\Models\StuOrder;


            if (!empty($request->get('store_id'))) {
                $obj = $obj->where('store_id', $request->get('store_id'));
            } else {

                // 所有学校
                $get_all_store_id = \App\Models\MerchantStore::where('merchant_id', $loginer->merchant_id)->get();

                if ($get_all_store_id->isEmpty()) {
                    $this->message = '没有数据！';
                    return $this->format();
                }

                $all_store_id = [];
                foreach ($get_all_store_id as $v) {
                    $all_store_id[] = $v->store_id;
                }

                $obj = $obj->whereIn('store_id', $all_store_id);

            }

            if (!empty($request->get('stu_grades_no'))) {
                $obj = $obj->where('stu_grades_no', $request->get('stu_grades_no'));
            }


            if (!empty($request->get('stu_class_no'))) {
                $obj = $obj->where('stu_class_no', $request->get('stu_class_no'));
            }


            if (!empty($request->get('student_name'))) {
                $obj = $obj->where('student_name', 'like', '%' . $request->get('student_name') . '%');
            }


            if (!empty($request->get('stu_order_batch_no'))) {
                $obj = $obj->where('stu_order_batch_no', $request->get('stu_order_batch_no'));
            }


            if (!empty($request->get('student_no'))) {
                $obj = $obj->where('student_no', $request->get('student_no'));
            }

            if (!empty($request->get('school_name'))) {
                $obj = $obj->where('school_name', 'like', '%' . $request->get('school_name') . '%');
            }


            if (!empty($request->get('student_name'))) {
                $obj = $obj->where('student_name', 'like', '%' . $request->get('student_name') . '%');
            }


            if (!empty($request->get('pay_type_source'))) {
                $obj = $obj->where('pay_type', $request->get('pay_type_source'));
            }

            if (!empty($request->get('pay_status'))) {
                $obj = $obj->where('pay_status', $request->get('pay_status'));
            }

            if (!empty($request->get('start_time'))) {
                $obj = $obj->where('gmt_start', '>',$request->get('start_time'));
            }

            if (!empty($request->get('end_time'))) {
                $obj = $obj->where('gmt_end', '<',$request->get('end_time'));
            }
            // excel导出订单及明细
            if (!empty($request->get('excel'))) {
                $file_name = !empty($request->get('file_name')) ? $request->get('file_name') : '订单流水';
                return self::orderExport($obj, $file_name);
            }


            $this->t = $obj->count();

            $data = $this->page($obj)->orderBy('id', 'desc')->get();


            foreach ($data as &$v) {
                $v->pay_status_desc = \App\Logic\PrimarySchool\Order::status($v->pay_status);
            }

// 1 支付成功 ，2  等待支付 ，3  支付失败 ，4 关闭，5 退款中，6 已退款 7 有退款

            $this->status = 1;
            $this->message = 'ok';
            return $this->format($data);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }


    public static function orderExport($order, $file_name)
    {

        $data = $order->get();

        $excel_title = [
            '订单号',
            '缴费项目名称',
            '学校名称',
            '年级',
            '班级',
            '学生姓名',
            '家长姓名',
            '家长手机号',
            '项目总金融',
            '支付金额',
            '付款账户',
            '支付类型',
            '支付状态',
            '小项情况'

        ];
        $excel_data = [];

        foreach ($data as $v) {
            $item = \App\Models\StuOrderItem::where('out_trade_no', $v->out_trade_no)->get();
            // 小项情况统计
            $detail_str = '';
            foreach ($item as $vv) {

                $item_name = $vv->item_name;
                $item_mandatory = $vv->item_mandatory;
                $item_price = $vv->item_price;
                $status_desc = \App\Logic\PrimarySchool\Order::status($vv->status);


                $str = '%s-%s-%s元-%s；';

                // $detail_str.=sprintf($str,$item_name,$item_price,$item_mandatory,$status_desc);
                $detail_str .= sprintf($str, $item_name, $item_mandatory, $item_price, $status_desc);
            }
            $excel_data[] = [
                $v->out_trade_no,
                $v->batch_name,
                $v->school_name,
                $v->stu_grades_name,
                $v->stu_class_name,
                $v->student_name,
                $v->student_user_name,
                $v->student_user_mobile,
                $v->amount,
                $v->pay_amount,
                $v->buyer_logon_id,
                $v->pay_type_desc,
                \App\Logic\PrimarySchool\Order::status($v->pay_status),
                $detail_str
            ];

        }

        \App\Common\Excel\Excel::downExcel($excel_title, $excel_data, $file_name);


    }


// https://pay.umxnt.com/api/school/teacher/order/lst?token=xxx&stu_order_batch_no=123456&excel=1&file_name=xxx


    /*
        单条
    */
    public function show()
    {
        /*$str="{" .
                        "      \"users\":[{" .
                        "        \"user_mobile\":\"17314822364\"," .
                        "\"user_name\":\"张三\"," .
                        "\"user_relation\":\"1\"," .
                        "\"user_change_mobile\":\"17002582596\"" .
                        "        }]," .

                        "\"school_pid\":\"2088102175631431\"," .//学校授权pid
                        "\"school_no\":\"36010300000008\"," .//支付宝学校编号
                        "\"child_name\":\"张晓晓\"," .
                        "\"grade\":\"高一\"," .
                        "\"class_in\":\"3班\"," .
                        "\"student_code\":\"888888\"," .
                        "\"student_identify\":\"321084199103042611\"," .
                        "\"out_trade_no\":\"20160121236564523\"," .
                        "\"charge_bill_title\":\"有梦想科技大学培训费\"," .
                        "\"charge_type\":\"M\"," .
                        "      \"charge_item\":[{" .
                        "        \"item_name\":\"c++培训\"," .
                        "\"item_price\":1.2," .
                        "\"item_serial_number\":1," .
                        "\"item_maximum\":1," .
                        "\"item_mandatory\":\"N\"" .
                        "        }]," .
                        "\"amount\":1.2," .
                        "\"gmt_end\":\"2018-06-09 13:13:13\"," .
                        "\"end_enable\":\"Y\"," .
                        "\"partner_id\":\"2088102175631431\"" .
                        "  }";


                 $d=json_decode($str,true);
        echo var_export($d,true);
        die;
         print_r($d,true);

                 die;
        */
        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $obj = new \App\Models\StuOrder;

            $obj = $obj
                ->where('out_trade_no', $request->get('out_trade_no'));


            $data = $obj->first();

            if (empty($data)) {
                $this->message = '订单不存在！';
                return $this->format();
            }

            $all_item = \App\Models\StuOrderItem::where('out_trade_no', $data->out_trade_no)->get();


            foreach ($all_item as &$v) {
                switch ($v->status) {
                    case 1:
                        $v->status_desc = $v->status_desc = '支付成功';
                        break;
                    case 2:
                        $v->status_desc = $v->status_desc = '等待支付';
                        break;
                    case 3:
                        $v->status_desc = $v->status_desc = '支付失败';
                        break;
                    case 4:
                        $v->status_desc = $v->status_desc = '关闭';
                        break;
                    case 5:
                        $v->status_desc = $v->status_desc = '退款中';
                        break;
                    case 6:
                        $v->status_desc = $v->status_desc = '已退款';
                        break;
                    case 7:
                        $v->status_desc = $v->status_desc = '有退款';
                        break;
                }
            }


            $data->all_item = $all_item;

            $this->status = 1;
            $this->message = 'ok';
            return $this->format($data);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }


    public function sendOrder()
    {
        $request = app('request');

        $stu_order_batch_no = $request->get('stu_order_batch_no');
        if (empty($stu_order_batch_no)) {
            $this->status = 2;
            $this->message = '缴费项目编号不能为空！';
            return $this->format();
        }
        /*
                $batch  = \App\Models\StuOrderBatch::where('stu_order_batch_no',$stu_order_batch_no)->first();

                if(empty($batch))
                {
                    $this->status=2;
                    $this->message='缴费项目不存在！';
                    return $this->format();
                }
        */
        $batch = \App\Models\StuOrderBatch::where('stu_order_batch_no', $stu_order_batch_no)->first();
        if ($batch->status != 1) {

            $this->status = 2;
            $this->message = '请等待缴费项目审核通过！';
            return $this->format();
        }


        $not_sync_ali_order = \App\Models\StuOrder::where('stu_order_batch_no', $stu_order_batch_no)->where('alipay_status', 2)->get();

        $return = [];
        foreach ($not_sync_ali_order as $v) {
            $return = $this->_sendOrder($v->out_trade_no);
        }

        if (empty($return)) {
            $this->status = 1;
            $this->message = '已全部同步';
            return $this->format();
        }

        return $return;

    }


    /*
        发送账单到支付宝-----一个订单发送多次，支付宝返回的订单号不变！
    */
    public function _sendOrder($out_trade_no)
    {

        try {
            // $request=app('request');
            // $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            // $out_trade_no=$request->get('out_trade_no');
            $order = \App\Models\StuOrder::where('out_trade_no', $out_trade_no)->first();
            if (empty($order)) {
                $this->message = '订单不存在！';
                return $this->format();
            }

            if ($order->alipay_status == 1) {

                $this->message = '订单已经发送到支付宝了，请不要重复操作！';
                return $this->format();
            }

            if ($order->pay_status != 2) {

                $this->message = '当前订单状态无法下单到支付宝！';
                return $this->format();
            }
            /*
                        $template = \App\Models\StuOrderType::where('stu_order_type_no',$order->stu_order_type_no)->first();
                        if(empty($template))
                        {
                            $this->message='缴费模板不存在！';
                            return $this->format();
                        }
            */
            $pay_item = \App\Models\StuOrderBatch::where('stu_order_batch_no', $order->stu_order_batch_no)->first();
            if (empty($pay_item)) {
                $this->message = '缴费项目不存在！';
                return $this->format();
            }


// 默认流程1
            if ($order->order_create_type == 1) {

                $charge_item = json_decode($pay_item->batch_item, true);


                $item = [];
                $can_choose = false;
                if (!empty($charge_item)) {
                    foreach ($charge_item as $v) {

                        $item[] = [
                            'item_serial_number' => $v['item_serial_number'],//项目编号
                            'item_name' => $v['item_name'] . '(共' . $v['item_number'] . '件)',

                            // 'item_price'=>$v['item_price'],
                            // 'item_maximum'=>$v['item_number'],//子项最大的件数
                            'item_price' => $v['item_price'] * $v['item_number'],
                            'item_maximum' => 1,//子项最大的件数

                            'item_mandatory' => $v['item_mandatory']
                        ];


                        if (!$can_choose && $v['item_mandatory'] == 'N') {
                            $can_choose = true;
                        }
                    }

                }

// 流程2
            } else {
                $can_choose = false;


                $item = [];

                $get_all_item = \App\Models\StuOrderItem::where('out_trade_no', $order->out_trade_no)->get();

                foreach ($get_all_item as $v) {
                    $item[] = [

                        'item_serial_number' => $v->item_serial_number,//项目编号
                        'item_name' => $v->item_name . '(共' . $v->item_number . '件)',

                        // 'item_price'=>$v['item_price'],
                        // 'item_maximum'=>$v['item_number'],//子项最大的件数
                        'item_price' => $v->item_price * $v->item_number,
                        'item_maximum' => 1,//子项最大的件数

                        'item_mandatory' => $v->item_mandatory
                    ];

                    if ($v->item_mandatory == 'N') {
                        $can_choose = true;
                    }
                }


            }


            $student = \App\Models\StuStudent::where('student_no', $order->student_no)->first();

            if (empty($student)) {
                $this->message = '学生信息不存在！';
                return $this->format();

            }

            //年级

            $grade = \App\Models\StuGrade::where('stu_grades_no', $student->stu_grades_no)->first();

            if (empty($grade)) {
                $this->message = '学生年级信息不存在！';
                return $this->format();

            }


            //班级

            $class = \App\Models\StuClass::where('stu_class_no', $student->stu_class_no)->first();

            if (empty($class)) {
                $this->message = '学生班级信息不存在！';
                return $this->format();

            }


            $info_school = $cur_school = \App\Models\StuStore::where('store_id', $order->store_id)->first();

            if (empty($cur_school)) {
                $this->message = '学校信息不存在！';
                return $this->format();
            }

//分校
            if (!empty($cur_school->pid)) {
                /*
                    分校一个条件     分校同步一个条件    分校授权一个条件

                    如果没有同步到学校 或者么有授权 就是公用主校的
                */
                $cur_ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id', $cur_school->store_id)->first();
                if ($cur_school->alipay_status != 1 || empty($cur_ali_app_auth_user)) {
                    // 使用学校资料
                    $info_school = \App\Models\StuStore::where('store_id', $cur_school->pid)->first();
                    if (empty($info_school)) {
                        $this->message = '主校信息不存在！';
                        return $this->format();
                    }

                    // 其他情况使用自己的学校资料
                } else {
                    $info_school = $cur_school;
                }

            }


// 配置检测--------start
            $ali_config = \App\Models\AlipayIsvConfig::where('config_id', $info_school->config_id)->first();

            //pid
            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }


            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id', $info_school->store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }

// 配置检测--------end


            $merchant_token = $ali_app_auth_user->app_auth_token;//学校授权支付宝的token


            $ali_data = array(
                'users' =>
                    array(
                        array(
                            'user_mobile' => $student->student_user_mobile,//家长信息
                            'user_name' => $student->student_user_name,
                            'user_relation' => $student->student_user_relation,
                            // 'user_change_mobile' => '17002582596',//家长更换手机号
                        ),
                    ),
                'school_pid' => $ali_app_auth_user->alipay_user_id,//学校授权id
                'school_no' => $info_school->school_no,//支付宝  学校 编号
                'child_name' => $student->student_name,
                'grade' => $grade->stu_grades_name,
                'class_in' => $class->stu_class_name,
                'student_code' => $student->student_no,//学号
                // 'student_identify' => $student->student_identify,
                'out_trade_no' => $order->out_trade_no,
                'charge_bill_title' => $pay_item->batch_name . '-' . $cur_school->school_name,//缴费项目名称
                'charge_type' => $can_choose ? 'M' : 'N',//N表示不可选  M可选
                /*  'charge_item' => $item
                  array (
                    array (
                      'item_name' => 'c++培训',
                      'item_price' => 1.2,
                      'item_serial_number' => 1,
                      'item_maximum' => 1,
                      'item_mandatory' => 'N',
                    ),
                  ),*/
                'amount' => $order->amount,
                'gmt_end' => $order->gmt_end,
                'end_enable' => 'Y',//Y过期后不能缴费  N过期后可以缴费
                'partner_id' => $ali_config->alipay_pid,
            );


            if (!empty($item)) {
                $ali_data['charge_item'] = $item;


            }

            \App\Common\Log::write($ali_data, 'send_order.txt');


            //支付宝接口创建
            try {

                $aop = \App\Logic\Common\InitAliAop::aop($ali_config);

                $aop->method = 'alipay.eco.edu.kt.billing.send';


                $ali_request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtBillingSendRequest ();

                $ali_request->setBizContent(json_encode($ali_data));

                // $result = $aop->execute ( $ali_request,null,'201806BB29e8474b68124daab7b7c07e4fb73X43'); 
                $result = $aop->execute($ali_request, null, $merchant_token);
                \App\Common\Log::write($result, 'ali_order.txt');
                $responseNode = str_replace(".", "_", $ali_request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;


                if (!empty($resultCode) && $resultCode == 10000) {
                    // status  Y 表示成功  N  表示失败
                    // school_no  学校在支付宝的编号
                    /*
                    object(stdClass)#364 (2) {
                      ["alipay_eco_edu_kt_billing_send_response"]=>
                      object(stdClass)#358 (3) {
                        ["code"]=>
                        string(5) "10000"
                        ["msg"]=>
                        string(7) "Success"
                        ["order_no"]=>
                        string(24) "5b1902c970935d5aa95ea7c9"
                      }
                      ["sign"]=>
                      string(344) "Gi33GJtd2o6Mki5UTGKCuKXJjvblgCG8Oy1oscOcm0wH4PxdornV+l0oDNy8XsEd8TLO51ahRnvnDbKjvx7XPZPbf1qyjigM3HcWjTKLng9eFoYFR7nJKyBsUj4/avOEPQu/oPlrPBULyqTEcR+EES2JgyLLUkTH7I4o990W0XN8mSdIuC8wDwAEL1NC5PUOYkfjlOvjdLJ5ADHOXWZSyqUW1KJmHXIT4CiL7g9c3vlEzyLz8b9lWq7PdgiZGWE6IKLQLS5aHgYh2eEXybGaERSN0Nz98qNhFBVag4bt78CAv3DIR/3tBLPRMDslIHmN8+MlHDSDw/SLunBWINxltg=="
                    }


                    */
                    $third_data = ['status' => 1, 'order_no' => $result->$responseNode->order_no];
                    // echo "成功";
                } else {
                    $msg = isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
                    $msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';
                    $third_data = ['status' => 2, 'message' => $msg];
                }

                // echo "失败";

            } catch (\Exception $e) {
                \App\Common\Log::write($e->getMessage(), 'ali_order.txt');
                //支付宝接口错误
                // $third_data =  ['status'=>2,'message'=>$e->getMessage().$e->getLine()];
                $third_data = ['status' => 2, 'message' => '支付宝接口错误'];
            }

// 成功
            if ($third_data['status'] == 1) {

                $order->trade_no = $third_data['order_no'];
                $order->alipay_status = 1;
                $order->alipay_status_desc = '账单成功发送到支付宝！';
                $order->update();

                $this->status = 1;
                $this->message = '账单成功发送到支付宝！';
                return $this->format();

// 失败
            } else {
                $order->alipay_status = 2;
                $order->alipay_status_desc = '账单发送支付宝失败：' . $third_data['message'];
                $order->update();

                $this->status = 2;
                $this->message = '账单发送支付宝失败：' . $third_data['message'];
                return $this->format();

            }


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }

    }


    /*
        发送账单到支付宝-----一个订单发送多次，支付宝返回的订单号不变！
    */
    public function sendOneOrder()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $out_trade_no = $request->get('out_trade_no');

            return $this->_sendOrder($out_trade_no);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }

    }


    /*
        发送账单到支付宝-----一个订单发送多次，支付宝返回的订单号不变！
    */
    public function sendOneOrder_bak()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $out_trade_no = $request->get('out_trade_no');
            $order = \App\Models\StuOrder::where('out_trade_no', $out_trade_no)->first();


            $batch = \App\Models\StuOrderBatch::where('stu_order_batch_no', $order->stu_order_batch_no)->first();
            if ($batch->status != 1) {

                $this->status = 2;
                $this->message = '请等待缴费项目审核通过！';
                return $this->format();
            }


            if (empty($order)) {
                $this->message = '订单不存在！';
                return $this->format();
            }

            if ($order->alipay_status == 1) {

                $this->message = '订单已经发送到支付宝了，请不要重复操作！';
                return $this->format();
            }

            $template = \App\Models\StuOrderType::where('stu_order_type_no', $order->stu_order_type_no)->first();
            if (empty($template)) {
                $this->message = '缴费模板不存在！';
                return $this->format();
            }

            $pay_item = \App\Models\StuOrderBatch::where('stu_order_batch_no', $order->stu_order_batch_no)->first();
            if (empty($pay_item)) {
                $this->message = '缴费项目不存在！';
                return $this->format();
            }

            $charge_item = json_decode($template->charge_item, true);

            $item = [];
            if (!empty($charge_item)) {
                foreach ($charge_item as $v) {

                    $item[] = [
                        'item_name' => $v['item_name'],
                        'item_price' => $v['item_price'],
                        'item_serial_number' => $v['item_number']
                    ];
                }

            }


            $student = \App\Models\StuStudent::where('student_no', $order->student_no)->first();

            if (empty($student)) {
                $this->message = '学生信息不存在！';
                return $this->format();

            }

            //年级

            $grade = \App\Models\StuGrade::where('stu_grades_no', $student->stu_grades_no)->first();

            if (empty($grade)) {
                $this->message = '学生年级信息不存在！';
                return $this->format();

            }


            //班级

            $class = \App\Models\StuClass::where('stu_class_no', $student->stu_class_no)->first();

            if (empty($class)) {
                $this->message = '学生班级信息不存在！';
                return $this->format();

            }


            $school = \App\Models\StuStore::where('store_id', $order->store_id)->first();

            if (empty($school)) {
                $this->message = '学校信息不存在！';
                return $this->format();

            }


// 配置检测--------start
            $ali_config = \App\Models\AlipayIsvConfig::where('config_id', $school->config_id)->first();

            //pid
            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }


            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id', $school->store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }

// 配置检测--------end


            $merchant_token = $ali_app_auth_user->app_auth_token;//学校授权支付宝的token


            $ali_data = array(
                'users' =>
                    array(
                        array(
                            'user_mobile' => $student->student_user_mobile,//家长信息
                            'user_name' => $student->student_user_name,
                            'user_relation' => $student->student_user_relation,
                            // 'user_change_mobile' => '17002582596',//家长更换手机号
                        ),
                    ),
                'school_pid' => $ali_app_auth_user->alipay_user_id,//学校授权id
                'school_no' => $school->school_no,//支付宝  学校 编号
                'child_name' => $student->student_name,
                'grade' => $grade->stu_grades_name,
                'class_in' => $class->stu_class_name,
                'student_code' => $student->student_no,//学号
                'student_identify' => $student->student_identify,
                'out_trade_no' => $order->out_trade_no,
                'charge_bill_title' => $template->charge_name,//缴费项目名称
                'charge_type' => 'N',//N表示不可选  M可选
                /*  'charge_item' => $item
                  array (
                    array (
                      'item_name' => 'c++培训',
                      'item_price' => 1.2,
                      'item_serial_number' => 1,
                      'item_maximum' => 1,
                      'item_mandatory' => 'N',
                    ),
                  ),*/
                'amount' => $template->amount,
                'gmt_end' => $pay_item->gmt_end,
                'end_enable' => 'Y',//Y过期后不能缴费  N过期后可以缴费
                'partner_id' => $ali_config->alipay_pid,
            );


            if (!empty($item)) {
                $ali_data['charge_item'] = $item;
            }


            //支付宝接口创建
            try {

                $aop = \App\Logic\Common\InitAliAop::aop($ali_config);


                $ali_request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtBillingSendRequest ();

                $ali_request->setBizContent(json_encode($ali_data));

                // $result = $aop->execute ( $ali_request,null,'201806BB29e8474b68124daab7b7c07e4fb73X43'); 
                $result = $aop->execute($ali_request, null, $merchant_token);
                \App\Common\Log::write($result, 'ali_order.txt');
                $responseNode = str_replace(".", "_", $ali_request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;


                if (!empty($resultCode) && $resultCode == 10000) {
                    // status  Y 表示成功  N  表示失败
                    // school_no  学校在支付宝的编号
                    /*
                    object(stdClass)#364 (2) {
                      ["alipay_eco_edu_kt_billing_send_response"]=>
                      object(stdClass)#358 (3) {
                        ["code"]=>
                        string(5) "10000"
                        ["msg"]=>
                        string(7) "Success"
                        ["order_no"]=>
                        string(24) "5b1902c970935d5aa95ea7c9"
                      }
                      ["sign"]=>
                      string(344) "Gi33GJtd2o6Mki5UTGKCuKXJjvblgCG8Oy1oscOcm0wH4PxdornV+l0oDNy8XsEd8TLO51ahRnvnDbKjvx7XPZPbf1qyjigM3HcWjTKLng9eFoYFR7nJKyBsUj4/avOEPQu/oPlrPBULyqTEcR+EES2JgyLLUkTH7I4o990W0XN8mSdIuC8wDwAEL1NC5PUOYkfjlOvjdLJ5ADHOXWZSyqUW1KJmHXIT4CiL7g9c3vlEzyLz8b9lWq7PdgiZGWE6IKLQLS5aHgYh2eEXybGaERSN0Nz98qNhFBVag4bt78CAv3DIR/3tBLPRMDslIHmN8+MlHDSDw/SLunBWINxltg=="
                    }


                    */
                    $third_data = ['status' => 1, 'order_no' => $result->$responseNode->order_no];
                    // echo "成功";
                } else {
                    $msg = isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
                    $msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';
                    $third_data = ['status' => 2, 'message' => $msg];
                }

                // echo "失败";

            } catch (\Exception $e) {
                \App\Common\Log::write($e->getMessage(), 'ali_order.txt');
                //支付宝接口错误
                // $third_data =  ['status'=>2,'message'=>$e->getMessage().$e->getLine()];
                $third_data = ['status' => 2, 'message' => '支付宝接口错误'];
            }

// 成功
            if ($third_data['status'] == 1) {

                $order->trade_no = $third_data['order_no'];
                $order->alipay_status = 1;
                $order->alipay_status_desc = '账单成功发送到支付宝！';
                $order->update();

                $this->status = 1;
                $this->message = '账单成功发送到支付宝！';
                return $this->format();

// 失败
            } else {
                $order->alipay_status = 2;
                $order->alipay_status_desc = '账单发送支付宝失败：' . $third_data['message'];
                $order->update();

                $this->status = 2;
                $this->message = '账单发送支付宝失败：' . $third_data['message'];
                return $this->format();

            }


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }

    }


    public function make()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            $merchant_id = $loginer->merchant_id;

// 配置检测--------start
            $ali_config = \App\Models\AlipayIsvConfig::where('config_id', $loginer->config_id)->first();

            //pid
            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }

// 配置检测--------end


            //支付宝接口创建
            try {

                $aop = \App\Logic\Common\InitAliAop::aop($ali_config);


                $ali_request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtBillingSendRequest ();
                $ali_request->setBizContent("{" .
                    "      \"users\":[{" .
                    "        \"user_mobile\":\"17314822364\"," .
                    "\"user_name\":\"张三\"," .
                    "\"user_relation\":\"1\"," .
                    // "\"user_change_mobile\":\"17002582596\"" .
                    "        }]," .
                    "\"school_pid\":\"2088102175631431\"," .//学校授权pid
                    "\"school_no\":\"36010300000008\"," .//支付宝学校编号
                    "\"child_name\":\"张晓晓\"," .
                    "\"grade\":\"高一\"," .
                    "\"class_in\":\"3班\"," .
                    "\"student_code\":\"888888\"," .
                    "\"student_identify\":\"321084199103042611\"," .
                    "\"out_trade_no\":\"20160121236564523\"," .
                    "\"charge_bill_title\":\"有梦想科技大学培训费\"," .
                    "\"charge_type\":\"M\"," .
                    "      \"charge_item\":[{" .
                    "        \"item_name\":\"c++培训\"," .
                    "\"item_price\":1.2," .
                    "\"item_serial_number\":1," .
                    "\"item_maximum\":1," .
                    "\"item_mandatory\":\"N\"" .
                    "        }]," .
                    "\"amount\":1.2," .
                    "\"gmt_end\":\"2018-06-09 13:13:13\"," .
                    "\"end_enable\":\"Y\"," .
                    "\"partner_id\":\"2088102175631431\"" .
                    "  }");
                $result = $aop->execute($ali_request, null, '201806BB29e8474b68124daab7b7c07e4fb73X43');

                var_dump($result);
                die;
                $responseNode = str_replace(".", "_", $ali_request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;


                if (!empty($resultCode) && $resultCode == 10000 && $result->$responseNode->status == 'Y') {
                    // status  Y 表示成功  N  表示失败
                    // school_no  学校在支付宝的编号
                    /*
                    object(stdClass)#364 (2) {
                      ["alipay_eco_edu_kt_billing_send_response"]=>
                      object(stdClass)#358 (3) {
                        ["code"]=>
                        string(5) "10000"
                        ["msg"]=>
                        string(7) "Success"
                        ["order_no"]=>
                        string(24) "5b1902c970935d5aa95ea7c9"
                      }
                      ["sign"]=>
                      string(344) "Gi33GJtd2o6Mki5UTGKCuKXJjvblgCG8Oy1oscOcm0wH4PxdornV+l0oDNy8XsEd8TLO51ahRnvnDbKjvx7XPZPbf1qyjigM3HcWjTKLng9eFoYFR7nJKyBsUj4/avOEPQu/oPlrPBULyqTEcR+EES2JgyLLUkTH7I4o990W0XN8mSdIuC8wDwAEL1NC5PUOYkfjlOvjdLJ5ADHOXWZSyqUW1KJmHXIT4CiL7g9c3vlEzyLz8b9lWq7PdgiZGWE6IKLQLS5aHgYh2eEXybGaERSN0Nz98qNhFBVag4bt78CAv3DIR/3tBLPRMDslIHmN8+MlHDSDw/SLunBWINxltg=="
                    }


                    */
                    return ['status' => 1, 'order_no' => $result->$responseNode->order_no];
                    // echo "成功";
                } else {
                    $msg = isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
                    $msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';
                    $third_data = ['status' => 2, 'message' => $msg];
                }

                // echo "失败";

            } catch (\Exception $e) {
                //支付宝接口错误
                // $third_data =  ['status'=>2,'message'=>$e->getMessage().$e->getLine()];
                $third_data = ['status' => 2, 'message' => '支付宝接口错误'];
            }


            $this->status = 1;
            $this->message = '订单创建成功';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }


    }


}
