<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/3/17
 * Time: 下午3:15
 */

namespace App\Api\Controllers\Device;


use Illuminate\Support\Facades\Log;

class ZlbzController
{

    //发送打印
    public function print_send($device_id, $pay_type, $store_name, $order_no, $price, $remark, $qr_url, $print_type = "")
    {
        try {

            $secretkey = 'zlbz-cloud';
            $server = 'http://121.199.68.96/o2o-print/print.php';
            //  $re = $this->QueryState($device_id, $secretkey, $server);
            //时间戳
            $time = time();
            $querystring = "action=send&device_id={$device_id}&secretkey={$secretkey}&timestamp={$time}&";
            $data = '';

            if ($print_type) {
                //酒店押金
                if ($print_type == "1") {
                    // 打印订单内容
                    $data .= "\x1B\x61\x01\x1b\x4d\x01\x1d\x21\x11酒店押金交易凭证\n\n";
                    $data .= "\x1B\x61\x00\x1b\x4d\x00\x1d\x21\x00商户: " . $store_name . "\n";
                    $data .= "支付方式: $pay_type\n";
                    $data .= "订单号: " . $order_no . "\n";
                    $data .= "支付时间: " . date('Y-m-d:H:i:s', $time) . "\n";
                    $data .= "支付金额: \x1B\x21\x30" . $price . "\n\n";
                    $data .= "\x1B\x21\x00备注:" . $remark . " \n";
                    $data .= "\n";
                    $data .= "\x1B\x21\x00-------------------------------\n";
                    // $data .= "\x1B\x61\x02商户存银\n";
                    // $data .= "\x1B\x61\x01\x1d\x5a\x02\x1b\x5a\x00\x4c\x06\x15\x00" . "" . $qr_url . "\n";
                    // echo($data);
                }

            } else {


                // 打印订单内容
                $data .= "\x1B\x61\x01\x1b\x4d\x01\x1d\x21\x11交易凭证\n\n";
                $data .= "\x1B\x61\x00\x1b\x4d\x00\x1d\x21\x00商户: " . $store_name . "\n";
                $data .= "支付方式: $pay_type\n";
                $data .= "订单号: " . $order_no . "\n";
                $data .= "支付时间: " . date('Y-m-d:H:i:s', $time) . "\n";
                $data .= "支付金额: \x1B\x21\x30" . $price . "\n\n";
                $data .= "\x1B\x21\x00备注:" . $remark . " \n";
                $data .= "\n";
                $data .= "\x1B\x21\x00-------------------------------\n";
                // $data .= "\x1B\x61\x02商户存银\n";
                // $data .= "\x1B\x61\x01\x1d\x5a\x02\x1b\x5a\x00\x4c\x06\x15\x00" . "" . $qr_url . "\n";
                // echo($data);
            }

            //这里做了一个转码，
            $data = mb_convert_encoding($data, "GBK", "UTF-8");
            //base64加密一下打印内容
            $data = base64_encode($data . "\x0d\x0a");
            //sha1($querystring.$data) 生成请求签名
            $querystring .= "sign=" . sha1($querystring . $data);
            $url = $server . "?" . $querystring;
            $re = $this->PostData($url, $data);
            Log::info($re);

        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }

    public function QueryState($device_id)
    {
        $secretkey = 'zlbz-cloud';
        $server = 'http://121.199.68.96/o2o-print/print.php';
        //时间戳
        $time = time();
        $querystring = "action=state&device_id={$device_id}&secretkey={$secretkey}&timestamp={$time}";
        $querystring .= "&sign=" . sha1($querystring);
        $url = $server . "?" . $querystring;
        return $this->PostData($url);
    }

    public function PostData($url, $data = "")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $handles = curl_exec($ch);
        curl_close($ch);

        return $handles;
    }

}