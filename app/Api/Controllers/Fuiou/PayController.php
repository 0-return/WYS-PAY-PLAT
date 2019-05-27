<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/24
 * Time: 6:39 PM
 */

namespace App\Api\Controllers\Fuiou;


use function EasyWeChat\Kernel\Support\get_client_ip;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{

    public $scpay_url = "https://fundwx.fuiou.com/micropay";
    public $order_query = "https://fundwx.fuiou.com/commonQuery";
    public $qr_create = "https://fundwx.fuiou.com/wxPreCreate";
    public $get_openid = "https://fundwx.fuiou.com/oauth2/getOpenid";

    //扫一扫 0-系统错误 1-成功 2-正在支付 3-失败
    public function scan_pay($data)
    {
        try {
            $pem = $data['pem'];
            $url = $this->scpay_url;
            $obj = new \App\Api\Controllers\Fuiou\BaseController();
            $request = [
                'version' => isset($data['version']) ? $data['version'] : '1',//版本
                'ins_cd' => isset($data['ins_cd']) ? $data['ins_cd'] : '',//机构号
                'mchnt_cd' => isset($data['mchnt_cd']) ? $data['mchnt_cd'] : '',//商户号
                'term_id' => isset($data['term_id']) ? $data['term_id'] : '88888888',//终端号
                'random_str' => time() . $data['ins_cd'],//随机字符串
                'order_type' => isset($data['order_type']) ? $data['order_type'] : '',//订单类型
                'goods_des' => isset($data['goods_des']) ? $data['goods_des'] : '',//商品描述
                'mchnt_order_no' => isset($data['mchnt_order_no']) ? $data['mchnt_order_no'] : '',//商户订单号
                'order_amt' => isset($data['order_amt']) ? $data['order_amt'] : '',//总金额 分
                'term_ip' => get_client_ip(),//终端IP,
                'txn_begin_ts' => date('YmdHis', time()),//交易开始时间,
                'auth_code' => isset($data['auth_code']) ? $data['auth_code'] : ''//付款码,
            ];

            $request['goods_detail'] = "";
            $request['addn_inf'] = "";
            $request['curr_type'] = "";
            $request['goods_tag'] = "";
            $request['sence'] = "";

            $str = $obj->getSignContent($request);
            $request['sign'] = $obj->sign($str, $pem);

            $request['reserved_expire_minute'] = "1440";
            $request['reserved_sub_appid'] = "";
            $request['reserved_limit_pay'] = "";
            $request['reserved_fy_term_id'] = "";
            $request['reserved_fy_term_type'] = "";
            $request['reserved_fy_term_sn'] = "";

            $re = $obj->send($request, $url);


            //用户输入密码
            if ($re['result_code'] == "030010") {
                return [
                    'status' => 2,
                    'message' => '请用户输入密码',
                    'data' => $re,
                ];
            } elseif ($re['result_code'] == "000000") {
                //交易成功
                return [
                    'status' => 1,
                    'message' => '交易成功',
                    'data' => $re,
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => $re['result_msg'],
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }

    }


    //查询订单 0-系统错误 1-成功 2-正在支付 3-失败 4.已经退款 5 退款中
    public function order_query($data)
    {

        try {
            $pem = $data['pem'];
            $url = $this->order_query;
            $obj = new \App\Api\Controllers\Fuiou\BaseController();
            $request = [
                'version' => isset($data['version']) ? $data['version'] : '1',//版本
                'ins_cd' => isset($data['ins_cd']) ? $data['ins_cd'] : '',//机构号
                'mchnt_cd' => isset($data['mchnt_cd']) ? $data['mchnt_cd'] : '',//商户号
                'term_id' => isset($data['term_id']) ? $data['term_id'] : '88888888',//终端号
                'order_type' => isset($data['order_type']) ? $data['order_type'] : '',//付款码,
                'mchnt_order_no' => isset($data['mchnt_order_no']) ? $data['mchnt_order_no'] : '',//商户订单号
                'random_str' => time() . $data['ins_cd'],//随机字符串
            ];

            $str = $obj->getSignContent($request);
            $request['sign'] = $obj->sign($str, $pem);


            $re = $obj->send($request, $url);
            dd($re);
            //系统错误
            if ($re['resultCode'] == "fail") {
                return [
                    'status' => 0,
                    'message' => $re['errDes'],

                ];
            }

            //业务成功
            if ($re['resultCode'] == "success") {

                //交易成功
                if ($re['orderStatus'] == "success") {
                    return [
                        'status' => 1,
                        'message' => '交易成功',
                        'data' => $re,
                    ];
                }

                //用户输入密码
                if ($re['orderStatus'] == "paying") {
                    return [
                        'status' => 2,
                        'message' => '请用户输入密码',
                        'data' => $re,
                    ];
                }

                //已经取消
                if ($re['orderStatus'] == "cancel") {
                    return [
                        'status' => 3,
                        'message' => '用户取消支付',
                        'data' => $re,
                    ];
                }


                //已退款
                if ($re['orderStatus'] == "refund") {
                    return [
                        'status' => 4,
                        'message' => '用户已退款',
                        'data' => $re,
                    ];
                }

                //退款中
                if ($re['orderStatus'] == "refunding") {
                    return [
                        'status' => 5,
                        'message' => '退款中',
                        'data' => $re,
                    ];
                }


            } else {
                return [
                    'status' => 0,
                    'message' => $re['errDes'],
                ];
            }


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
            $trade_no = $data['trade_no'];
            $trade_no = str_replace('112121', '', $trade_no);
            $url = $data['request_url'];
            $md_key = $data['md_key'];
            $outRefundNo = $data['outRefundNo'];
            $amount = $data['amount'];
            $mid = $data['mid'];
            $orgNo = $data['orgNo'];
            //请求数据
            $data = [
                'mid' => $mid,
                'nonceStr' => $trade_no,
                'transactionId' => $trade_no,
                'orgNo' => $orgNo,
            ];

            //和融通交易接口
            $this->md_key = $md_key;
            $re = $this->execute($data, $url);
            //系统错误
            if ($re['resultCode'] == "fail") {
                return [
                    'status' => 0,
                    'message' => $re['errDes'],

                ];
            }
            //业务成功
            if ($re['resultCode'] == "success") {


                return [
                    'status' => 1,
                    'message' => '退款成功',
                    'data' => $re,
                ];


                //退款成功
                if ($re['orderStatus'] == "success") {
                    return [
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $re,
                    ];
                }

                //退款中
                if ($re['orderStatus'] == "refunding") {
                    return [
                        'status' => 0,
                        'message' => '退款中',
                        'data' => $re,
                    ];
                }


            } else {
                return [
                    'status' => 0,
                    'message' => $re['errDes'],
                ];
            }


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

        try {
            $url = $data['request_url'];
            $md_key = $data['md_key'];
            $outRefundNo = $data['outRefundNo'];
            $mid = $data['mid'];
            $orgNo = $data['orgNo'];
            //请求数据
            $data = [
                'mid' => $mid,
                'nonceStr' => $outRefundNo,
                'refundtransactionId' => $outRefundNo,
                'orgNo' => $orgNo,
            ];

            //和融通交易接口
            $this->md_key = $md_key;
            $re = $this->execute($data, $url);
            //系统错误
            if ($re['resultCode'] == "fail") {
                return [
                    'status' => 0,
                    'message' => $re['errDes'],

                ];
            }
            //业务成功
            if ($re['resultCode'] == "success") {

                //退款成功
                if ($re['orderStatus'] == "success") {
                    return [
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $re,
                    ];
                }

                //退款中
                if ($re['orderStatus'] == "refunding") {
                    return [
                        'status' => 2,
                        'message' => '退款中',
                        'data' => $re,
                    ];
                }


            } else {
                return [
                    'status' => 0,
                    'message' => $re['errDes'],
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //生成动态二维码-公共
    public function send_qr($data)
    {
        try {
            $out_trade_no = $data['out_trade_no'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $notify_url = $data['notify_url'];
            $url = $data['request_url'];
            $returnParams = $data['return_params'];//原样返回
            $md_key = $data['md_key'];
            $mid = $data['mid'];
            $orgNo = $data['orgNo'];
            $payChannel = $data['payChannel'];
            //请求数据

            $data = [
                'mid' => $mid,
                'totalFee' => $total_amount,
                'outTradeNo' => $out_trade_no,
                'nonceStr' => $out_trade_no,
                'payChannel' => $payChannel,
                'notifyUrl' => $notify_url,
                'orgNo' => $orgNo,
            ];

            //和融通交易接口
            $this->md_key = $md_key;
            $re = $this->execute($data, $url);
            //系统错误
            if ($re['resultCode'] == "fail") {
                return [
                    'status' => 0,
                    'message' => $re['errDes'],

                ];
            }

            if ($re['resultCode'] == "success") {
                return [
                    'status' => 1,
                    'code_url' => $re['codeUrl'],
                    'message' => $re,

                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }

    }


    //静态码提交
    public function qr_submit($data)
    {
        try {

            $pem = $data['pem'];
            $url = $this->qr_create;
            $obj = new \App\Api\Controllers\Fuiou\BaseController();
            $request = [
                'version' => isset($data['version']) ? $data['version'] : '1',//版本
                'ins_cd' => isset($data['ins_cd']) ? $data['ins_cd'] : '',//机构号
                'mchnt_cd' => isset($data['mchnt_cd']) ? $data['mchnt_cd'] : '',//商户号
                'term_id' => isset($data['term_id']) ? $data['term_id'] : '88888888',//终端号
                'random_str' => time() . $data['ins_cd'],//随机字符串
                'goods_des' => isset($data['goods_des']) ? $data['goods_des'] : '',//商品描述
                'mchnt_order_no' => isset($data['mchnt_order_no']) ? $data['mchnt_order_no'] : '',//商户订单号
                'order_amt' => isset($data['order_amt']) ? $data['order_amt'] : '',//总金额 分
                'term_ip' => get_client_ip(),//终端IP,
                'txn_begin_ts' => date('YmdHis', time()),//交易开始时间,
                'notify_url' => isset($data['notify_url']) ? $data['notify_url'] : url('/api/fuiou/pay_notify'),
                'trade_type' => isset($data['trade_type']) ? $data['trade_type'] : ''//付款码,
            ];

            $request['goods_detail'] = "";
            $request['goods_tag'] = "";
            $request['product_id'] = "";
            $request['addn_inf'] = "";
            $request['curr_type'] = "";
            $request['limit_pay'] = "";
            $request['openid'] = "";
            $request['sub_openid'] = isset($data['openid']) ? $data['openid'] : "";
            $request['sub_appid'] = "";

            if ($data['trade_type']=="JSAPI") {
                $request['sub_appid'] = isset($data['sub_appid']) ? $data['sub_appid'] : 'wx2421b1c4370ec43b';

            }


            $str = $obj->getSignContent($request);
            $request['sign'] = $obj->sign($str, $pem);

            $request['reserved_expire_minute'] = "1440";
            $request['reserved_fy_term_id'] = "";
            $request['reserved_fy_term_type'] = "";
            $request['reserved_txn_bonus'] = "";
            $request['reserved_fy_term_sn'] = "";


            $re = $obj->send($request, $url);


            //用户输入密码
            if ($re['result_code'] == "030010") {
                return [
                    'status' => 2,
                    'message' => '请用户输入密码',
                    'data' => $re,
                ];
            } elseif ($re['result_code'] == "000000") {
                //交易成功
                return [
                    'status' => 1,
                    'message' => '交易成功',
                    'data' => $re,
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => $re['result_msg'],
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //get_openid
    public function get_openid($data, $sub_info)
    {
        try {
            $pem = $data['pem'];
            $url = $this->get_openid;
            $obj = new \App\Api\Controllers\Fuiou\BaseController();
            $request = [
                'ins_cd' => isset($data['ins_cd']) ? $data['ins_cd'] : '',//机构号
                'mchnt_cd' => isset($data['mchnt_cd']) ? $data['mchnt_cd'] : '',//商户号
                'redirect_uri' => isset($data['redirect_uri']) ? $data['redirect_uri'] : url('/api/fuiou/weixin/pay_view?data=' . $sub_info),
                'type' => isset($data['type']) ? $data['type'] : '1',//付款码,
                'appid' => isset($data['appid']) ? $data['appid'] : '',
            ];

            $str = $obj->getSignContent($request);
            $sign = $obj->sign($str, $pem);
            $request['sign'] = urlencode($sign);
            $url = $url . '?sign=' . $request['sign'] . '&' . $str;

            return [
                'status' => 1,
                'message' => '链接返回成功',
                'data' => [
                    'url' => $url
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