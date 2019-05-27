<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/24
 * Time: 6:39 PM
 */

namespace App\Api\Controllers\Huiyuanbao;


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
            $notify_url = $data['notify_url'];
            $url = $data['request_url'];
            $pi_type = $data['pay_type'];
            $mid = $data['mid'];
            $orgNo = $data['orgNo'];
            $returnParams = $data['return_params'];//原样返回
            $md_key = $data['md_key'];


            //和融通交易接口
            $this->md_key = $md_key;


            $data = [
                'mid' => $mid,
                'totalFee' => $total_amount,
                'outTradeNo' => $out_trade_no,
                'nonceStr' => $out_trade_no,
                'channel' => $pi_type,
                'authCode' => $code,
                'orgNo' => $orgNo,

            ];
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

                //交易成功
                if ($re['payCode'] == "success") {
                    return [
                        'status' => 1,
                        'message' => '交易成功',
                        'data' => $re,
                    ];
                }

                //用户输入密码
                if ($re['payCode'] == "paying") {
                    return [
                        'status' => 2,
                        'message' => '请用户输入密码',
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


    //查询订单 0-系统错误 1-成功 2-正在支付 3-失败 4.已经退款 5 退款中
    public function order_query($data)
    {

        try {
            Log::info('和融通查询请求');
            Log::info($data);

            $out_trade_no = $data['out_trade_no'];
            $url = $data['request_url'];
            $md_key = $data['md_key'];
            $mid = $data['mid'];
            $orgNo = $data['orgNo'];

            //请求数据
            $data = [
                'mid' => $mid,
                'nonceStr' => $out_trade_no,
                'outTradeNo' => $out_trade_no,
                'orgNo' => $orgNo,
            ];

            //和融通交易接口
            $this->md_key = $md_key;
            $re = $this->execute($data, $url);
            Log::info('和融通查询返回');
            Log::info($re);
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


    //静态码提交-公共
    public function qr_submit($data)
    {
        try {
            $out_trade_no = $data['out_trade_no'];
            $userId = $data['userId'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $notify_url = $data['notify_url'];
            $url = $data['request_url'];
            $returnParams = $data['return_params'];//原样返回
            $md_key = $data['md_key'];
            $mid = $data['mid'];
            $orgNo = $data['orgNo'];
            $payChannel = $data['payChannel'];
            $app_id = $data['app_id'];
            $callBackUrl = $data['callBackUrl'];
            //请求数据

            $data = [
                'mid' => $mid,
                'totalFee' => $total_amount,
                'outTradeNo' => $out_trade_no,
                'nonceStr' => $out_trade_no,
                'payChannel' => $payChannel,
                'notifyUrl' => $notify_url,
                'orgNo' => $orgNo,
                'callBackUrl' => $callBackUrl,
            ];

            if ($app_id) {
                $data['appid'] = $app_id;
            }
            if ($userId) {
                $data['userid'] = $userId;
            }

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
                    'data' => $re,

                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }

}