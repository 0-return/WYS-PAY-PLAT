<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/8/7
 * Time: 上午11:38
 */

namespace App\Api\Controllers\Newland;


use App\Api\Controllers\BaseController;
use App\Common\PaySuccessAction;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use MyBank\Tools;

class NotifyController extends BaseController
{

    // 表单提交字符集编码
    public $postCharset = "UTF-8";

    private $fileCharset = "UTF-8";


    public function pay_notify(Request $request)
    {

        try {
            $data = $request->all();
            //是否设置订单号
            if (isset($data['TxnLogId'])) {
                //交易成功
                if ($data['TxnStatus'] == "1") {
                    $order = Order::where('out_trade_no', $data['TxnLogId'])->first();
                    //订单号存在
                    if ($order) {
                        //订单状态不成功更新状态
                        if ($order->pay_status != 1) {
                            $trade_no = $data['ChannelId'];
                            $pay_time = date('Y-m-d H:i:s', strtotime($data['TxnTime']));
                            $buyer_pay_amount = $data['TxnAmt'];
                            $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
                            $in_data = [
                                'status' => '1',
                                'pay_status' => 1,
                                'pay_status_desc' => '支付成功',
                                'trade_no' => $trade_no,
                                'pay_time' => $pay_time,
                                'buyer_pay_amount' => $buyer_pay_amount,
                            ];

                            $order->update($in_data);
                            $order->save();

                            //支付成功后的动作
                            $data = [
                                'ways_type' => $order->ways_type,
                                'ways_type_desc' => $order->ways_type_desc,
                                'source_type' => '8000',//返佣来源
                                'source_desc' => '新大陆',//返佣来源说明
                                'total_amount' => $order->total_amount,
                                'out_trade_no' => $order->out_trade_no,
                                'rate' => $order->rate,
                                'merchant_id' => $order->merchant_id,
                                'store_id' => $order->store_id,
                                'user_id' => $order->user_id,
                                'config_id' => $order->config_id,
                                'store_name' => $order->store_name,
                                'ways_source' => $order->ways_source,
                                'pay_time' => $pay_time,

                            ];

                            PaySuccessAction::action($data);

                            $notify_url = $order->notify_url;


                            //判断是否是有异步地址
                            if ($notify_url) {
                                try {

                                    $in_data = [
                                        'status' => '1',
                                        'pay_status' => '1',
                                        //'pay_status_desc' => '支付成功',
                                        'other_no' => $order->other_no,
                                        'out_trade_no' => $order->out_trade_no,
                                        'trade_no' => $trade_no,
                                        'buyer_pay_amount' => $buyer_pay_amount,
                                    ];

                                    $key = $order->other_no;
                                    $string = $this->getSignContent($in_data) . '&key=' . $key;
                                    $in_data['sign'] = md5($string);
                                    if (strpos($notify_url, '?') !== false) {
                                        $notify_url = $notify_url . '&' . $this->getSignContent($in_data);
                                        $return = $this->curlPost_java(json_encode($in_data), $notify_url);
                                    } else {
                                        $notify_url = $notify_url . '?' . $this->getSignContent($in_data);
                                        $return = $this->curlPost_java(json_encode($in_data), $notify_url);
                                    }

                                    return json_encode([
                                        'RspCode' => '000000',
                                        'RspDes' => '处理成功',
                                    ]);

                                    // return $return;
                                } catch (\Exception $exception) {
                                }
                            }


                        }


                    } else {
                        try {
                            $url = $order->notify_url;
                            $return = Tools::curl($request->all(), $url);
                            return $return;
                        } catch (\Exception $exception) {

                        }
                    }


                }

                //刷卡


                return json_encode([
                    'RspCode' => '000000',
                    'RspDes' => '处理成功',
                ]);
            }

        } catch (\Exception $exception) {
            Log::info($exception);
        }

    }


    /**
     * curl post java对接  传输数据流
     * */
    public function curlPost_java($data, $Url)
    {
        $ch = curl_init($Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//$data JSON类型字符串
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    //参数拼接
    public function getSignContent($params)
    {
        ksort($params);

        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {


        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {

                $data = mb_convert_encoding($data, $targetCharset);
                //              $data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }


}