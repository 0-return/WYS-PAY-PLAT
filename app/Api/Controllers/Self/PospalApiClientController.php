<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/13
 * Time: 下午3:36
 */

namespace App\Api\Controllers\Self;


class PospalApiClientController
{

    //分类请求url
    public $category_api_url='https://area23-win.pospal.cn:443//pospal-api2/openapi/v1/productOpenApi/queryProductCategoryPages';



    function doApiRequest($url, $sendData, $key)
    {
        $jsondata = json_encode($sendData);
        $signature = strtoupper(md5($key . $jsondata));
        return $this->httpsRequest($url, $jsondata, $signature);
    }

// 模拟提交数据函数
    function httpsRequest($url, $data, $signature)
    {
        $time = time();
        $curl = curl_init();// 启动一个CURL会话
        // 设置HTTP头
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "User-Agent: openApi",
            "Content-Type: application/json; charset=utf-8",
            "accept-encoding: gzip,deflate",
            "time-stamp: " . $time,
            "data-signature: " . $signature
        ));
        curl_setopt($curl, CURLOPT_URL, $url);         // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);        // Post提交的数据包
      //  curl_setopt($curl, CURLOPT_PROXY, '127.0.0.1:8888');//设置代理服务器,此处用的是fiddler，可以抓包分析发送与接收的数据
        curl_setopt($curl, CURLOPT_POST, 1);        // 发送一个常规的Post请求

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);// 获取的信息以文件流的形式返回
        $output = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $output; // 返回数据
    }
}