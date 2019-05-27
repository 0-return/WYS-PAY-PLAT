<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/20
 * Time: 11:01 AM
 */

namespace App\Api\Controllers\Newland;


use function EasyWeChat\Kernel\Support\get_client_ip;
use function EasyWeChat\Kernel\Support\get_server_ip;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{


    //扫一扫 0-系统错误 1-成功 2-正在支付 3-失败
    public function scan_pay($data)
    {

        try {
            $out_trade_no = $data['out_trade_no'];
            $code = $data['code'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $key = $data['key'];
            $org_no = $data['org_no'];
            $merc_id = $data['merc_id'];
            $trm_no = $data['trm_no'];
            $op_sys = $data['op_sys'];
            $opr_id = $data['opr_id'];
            $trm_typ = $data['trm_typ'];
            $payChannel = $data['payChannel'];

            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $key;
            $aop->op_sys = $op_sys;//操作系统
            // $aop->character_set = '01';
            //  $aop->latitude = '0';//纬度
            //  $aop->longitude = '0';//精度
            $aop->org_no = $org_no;//机构号
            $aop->merc_id = $merc_id;//商户号
            $aop->trm_no = $trm_no;//设备号
            $aop->opr_id = $opr_id;//操作员
            $aop->trm_typ = $trm_typ;//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
            $aop->trade_no = $out_trade_no;//商户单号
            $aop->txn_time = date('Ymdhis', time());//设备交易时间
            $aop->add_field = 'V1.0.1';
            $aop->version = 'V1.0.0';

            $data = [
                'amount' => number_format($total_amount * 100, 0, '.', ''),
                'total_amount' => number_format($total_amount * 100, 0, '.', ''),
                'payChannel' => $payChannel,
                'authCode' => $code,
            ];

            $request_obj_pay = new  \App\Common\XingPOS\Request\XingPaySaoMaZhiFuShangHuZhuSao();
            $request_obj_pay->setBizContent($data);
            $return = $aop->execute($request_obj_pay);
            //不成功 系统报错
            if ($return['returnCode'] != "000000") {
                return [
                    'status' => 0,
                    'message' => $return['message'],
                ];
            }

            //交易成功
            if ($return['result'] == "S") {
                return [
                    'status' => 1,
                    'data' => $return,
                    'message' => '交易成功',
                ];
            }

            //用户输入密码
            if ($return['result'] == "A") {
                return [
                    'status' => 2,
                    'message' => '等待用户输入密码',
                ];
            }
            //交易失败
            if ($return['result'] == "F") {
                return [
                    'status' => 3,
                    'message' => '交易失败',
                ];
            }
            //交易失败
            if ($return['result'] == "Z") {
                return [
                    'status' => 3,
                    'message' => '交易失败',
                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //查询订单 0-系统错误 1-成功 2-正在支付 3-失败 4.已经退款
    public function order_query($data)
    {

        try {
            $out_trade_no = $data['out_trade_no'];
            $key = $data['key'];
            $org_no = $data['org_no'];
            $merc_id = $data['merc_id'];
            $trm_no = $data['trm_no'];
            $op_sys = $data['op_sys'];
            $opr_id = $data['opr_id'];
            $trm_typ = $data['trm_typ'];

            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $key;
            $aop->op_sys = $op_sys;//操作系统
            // $aop->character_set = '01';
            //  $aop->latitude = '0';//纬度
            //  $aop->longitude = '0';//精度
            $aop->org_no = $org_no;//机构号
            $aop->merc_id = $merc_id;//商户号
            $aop->trm_no = $trm_no;//设备号
            $aop->opr_id = $opr_id;//操作员
            $aop->trm_typ = $trm_typ;//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
            $aop->trade_no = $out_trade_no;//商户单号
            $aop->txn_time = date('Ymdhis', time());//设备交易时间
            $aop->add_field = 'V1.0.1';
            $aop->version = 'V1.0.0';



            $data = [
                'qryNo' => $out_trade_no,
            ];

            $request_obj_pay = new  \App\Common\XingPOS\Request\XingPayDingDanChaXun();
            $request_obj_pay->setBizContent($data);
            $return = $aop->execute($request_obj_pay);


            //不成功 系统报错
            if ($return['returnCode'] != "000000") {
                return [
                    'status' => 0,
                    'message' => $return['message'],
                ];
            }
            //交易成功
            if ($return['result'] == "S") {
                return [
                    'status' => 1,
                    'data' => $return,
                    'message' => '交易成功',
                ];
            }

            //用户输入密码
            if ($return['result'] == "A") {
                return [
                    'status' => 2,
                    'message' => '等待用户输入密码',
                ];
            }
            //交易失败
            if ($return['result'] == "F") {
                return [
                    'status' => 3,
                    'message' => '交易失败',
                ];
            }
            //交易失败
            if ($return['result'] == "Z") {
                return [
                    'status' => 3,
                    'message' => '交易失败',
                ];
            }

            //交易撤销
            if ($return['result'] == "D") {
                return [
                    'status' => 3,
                    'message' => '用户交易撤销',
                ];
            }

            return [
                'status' => 0,
                'message' => '其他错误',

            ];


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //退款 0-系统错误 1-成功
    public function refund($data)
    {

        try {
            $out_trade_no = $data['out_trade_no'];
            $trade_no = $data['trade_no'];
            $key = $data['key'];
            $org_no = $data['org_no'];
            $merc_id = $data['merc_id'];
            $trm_no = $data['trm_no'];
            $op_sys = $data['op_sys'];
            $opr_id = $data['opr_id'];
            $trm_typ = $data['trm_typ'];

            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $key;
            $aop->op_sys = $op_sys;//操作系统
            // $aop->character_set = '01';
            //  $aop->latitude = '0';//纬度
            //  $aop->longitude = '0';//精度
            $aop->org_no = $org_no;//机构号
            $aop->merc_id = $merc_id;//商户号
            $aop->trm_no = $trm_no;//设备号
            $aop->opr_id = $opr_id;//操作员
            $aop->trm_typ = $trm_typ;//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
            $aop->trade_no = $out_trade_no;//商户单号
            $aop->txn_time = date('Ymdhis', time());//设备交易时间
            $aop->add_field = 'V1.0.1';
            $aop->version = 'V1.0.0';



            $data = [
                'orderNo' => $trade_no,
            ];

            $request_obj_pay = new  \App\Common\XingPOS\Request\XingPayTuiKuan();
            $request_obj_pay->setBizContent($data);
            $return = $aop->execute($request_obj_pay);

            //不成功 系统报错
            if ($return['returnCode'] != "000000") {
                return [
                    'status' => 0,
                    'message' => $return['message'],
                ];
            }
            //退款成功
            if ($return['result'] == "S") {
                return [
                    'status' => 1,
                    'data' => $return,
                    'message' => '退款成功',
                ];
            }

            //退款失败
            if ($return['result'] == "F") {
                return [
                    'status' => 0,
                    'message' => '退款失败',
                ];
            }
            //退款失败
            if ($return['result'] == "Z") {
                return [
                    'status' => 0,
                    'message' => '退款失败',
                ];
            }


            return [
                'status' => 0,
                'message' => '其他错误',

            ];

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //退款查询 0-系统错误 1-成功 2-正在退款 3-失败
    public function refund_query($data)
    {


    }


    //生成动态二维码-公共
    public function send_qr($data)
    {
        try {
            $out_trade_no = $data['out_trade_no'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $key = $data['key'];
            $org_no = $data['org_no'];
            $merc_id = $data['merc_id'];
            $trm_no = $data['trm_no'];
            $op_sys = $data['op_sys'];
            $opr_id = $data['opr_id'];
            $trm_typ = $data['trm_typ'];
            $payChannel = $data['payChannel'];

            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $key;
            $aop->op_sys = $op_sys;//操作系统
            // $aop->character_set = '01';
            //  $aop->latitude = '0';//纬度
            //  $aop->longitude = '0';//精度
            $aop->org_no = $org_no;//机构号
            $aop->merc_id = $merc_id;//商户号
            $aop->trm_no = $trm_no;//设备号
            $aop->opr_id = $opr_id;//操作员
            $aop->trm_typ = $trm_typ;//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
            $aop->trade_no = $out_trade_no;//商户单号
            $aop->txn_time = date('Ymdhis', time());//设备交易时间
            $aop->add_field = 'V1.0.1';
            $aop->version = 'V1.0.0';


            $data = [
                'amount' => number_format($total_amount * 100, 0, '.', ''),
                'total_amount' =>number_format($total_amount * 100, 0, '.', ''),
                'payChannel' => $payChannel,
            ];
            $request_obj_pay = new  \App\Common\XingPOS\Request\XingPaySaoMaZhiFuKeHuZhuSao();
            $request_obj_pay->setBizContent($data);
            $return = $aop->execute($request_obj_pay);

            //不成功 系统报错
            if ($return['returnCode'] != "000000") {
                return [
                    'status' => 0,
                    'message' => $return['message'],
                ];
            }

            //生成成功
            if ($return['result'] == "S") {
                return [
                    'status' => 1,
                    'message' => '返回成功',
                    'code_url' => $return['payCode'],
                    'data' => $return
                ];
            }

            //生成失败
            if ($return['result'] == "F") {
                return [
                    'status' => 0,
                    'data' => $return,
                    'message' => '生成失败',
                ];
            }
            //未知失败
            if ($return['result'] == "Z") {
                return [
                    'status' => 0,
                    'data' => $return,
                    'message' => '生成失败',
                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }

    }


    //静态码提交-公共 0  1
    public function qr_submit($data)
    {
        try {

            $out_trade_no = $data['out_trade_no'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $key = $data['key'];
            $org_no = $data['org_no'];
            $merc_id = $data['merc_id'];
            $trm_no = $data['trm_no'];
            $op_sys = $data['op_sys'];
            $opr_id = $data['opr_id'];
            $trm_typ = $data['trm_typ'];
            $payChannel = $data['payChannel'];
            $paysuccurl = $data['paysuccurl'];
            $data = [
                'opsys' => $op_sys,
                'characterset' => '00',
                'orgno' => $org_no,
                'mercid' => $merc_id,
                'trmno' => $trm_no,
                'tradeno' => $out_trade_no,
                'trmtyp' => $trm_typ,
                'txntime' => date('Ymdhis', time()),//设备交易时间
                'signtype' => 'MD5',
                'version' => 'V1.0.0',
                'amount' => number_format($total_amount * 100, 0, '.', ''),
                'total_amount' =>number_format($total_amount * 100, 0, '.', ''),
                'paychannel' => $payChannel,
                'paysuccurl' => $paysuccurl

            ];
            ksort($data);
            $stringToBeSigned = "";
            $i = 0;
            foreach ($data as $k => $v) {
                $stringToBeSigned .= $v;
                $i++;

            }
            $url = 'https://gateway.starpos.com.cn/sysmng/bhpspos4/5533020.do';
            unset ($k, $v);
            $stringToBeSigned = $stringToBeSigned . $key;
            $data['signvalue'] = md5($stringToBeSigned);
            $i1 = 0;
            $stringToBeSigned1 = '';
            foreach ($data as $k => $v) {
                // 转换成目标字符集
                $v = $this->characet($v, $this->postCharset);

                if ($i1 == 0) {
                    $stringToBeSigned1 .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned1 .= "&" . "$k" . "=" . "$v";
                }
                $i1++;
            }


            unset ($k, $v);
            $url = $url . '?' . $stringToBeSigned1;
            return [
                'status' => 1,
                'message' => '返回成功',
                'data' => [
                    'url' => $url,
                ]
            ];


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }

}