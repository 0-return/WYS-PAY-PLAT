<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/14
 * Time: 下午3:36
 */

namespace App\Api\Controllers\AlipayOpen;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayEbppInvoiceTitleDynamicGetRequest;
use Alipayopen\Sdk\Request\AlipayEbppInvoiceTitleListGetRequest;
use App\Models\AlipayIsvConfig;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends BaseController
{


    public function get_invoice_title(Request $request)
    {


        try {
            //1.接入参数初始化
            $data = $request->all();
      //   return $data;
            //验证签名
//            $check = $this->check_md5($data);
//            if ($check['return_code'] == 'FALL') {
//                return $this->return_data($check);
//            }


            $bar_code = $data['bar_code'];
            $user_id = "";
            $store_id = $request->get('store_id', '2018061205492993161');


            $store = Store::where('store_id', $store_id)->select('config_id')->first();

            if (!$store) {
                $re_data['return_code'] = 'FALL';
                $re_data['return_msg'] = '门店ID不存在';
                return $this->return_data($re_data);
            }
            $config_id = $store->config_id;
            //配置取缓存
            $config = AlipayIsvConfig::where('config_id', $config_id)
                ->where('config_type', '01')
                ->first();

            $c = new AopClient();
            $c->signType = "RSA2";//升级算法
            $c->gatewayUrl = $config->alipay_gateway;
            $c->appId = $config->app_id;//'2018091461382442';
            $c->rsaPrivateKey = $config->rsa_private_key;//'MIIEpAIBAAKCAQEAxFloZhA942dfea0YN7o/gkX2wIPzFOqRfvZLoNHuZ81wXDZBMRd0UWlIbB+3S3oMR7DAxn95FUxvTDG9qQKqBZSpYUEzzeG2gnbh9GrhRoTnzL14bJ0JFPjF3nZmt7rALPqlmRINCZ07eWFah0Ux4l5ph1krI0HuhKQbzlZcXBRZC7CCoxvYDJEL2KS5318C1F6KZD8nU7CFqhitYR7wRgD4xpJkZ5y4/9yewcWLDVsD1rDHEktLZuiw76/G4yqKdMteGtR7kRtaYKPWRO94431wp5geokupxVHJZryhcVhjzkf4KQx5GJ7tCUwAjZqmD4yc1/MowgOORkxplUEreQIDAQABAoIBAEzmx6DR06txHU/Gn6mT5LPdOxuDkn0qsfmL9WrzTfCR4eP7y+SyTvhjx92xPlTtZEnfzGDyJDZXTXAGO0YqqPSumPhiVSvZr2XBshXMlpbMDw19V+ESUDBKjxTmQqzLE+GJ+bnN/BU748NSgeZhD3ydha9hGSgh9o00yT8zS9Ez509KghtMLMfAHIFT872nS+UBq4sb4sjdO2fLlsFn9HyEivEIJPTsoBrfdqKvV5v2cVtZkRvzbEv0z8dVCkcmP/3t/mmkegPvkUXnQhn5tyHvPBgbZQ8nqXq0pexJsCiJNBV49b910ma5xRoAR20Q3QZ5VvZByl/zFk5vYBzNOgECgYEA5d6KEzeyLQlAv7kzW++1v83HkjViCxOeVraEJiCp21YtlVkB/AvVKmSbju8+VBP1as2hu/Q3s09PItnel+HdCeAkrD3OS3nniVVWG2QJ7IStGWyRqrLz+Hfpe4IMC27qr45j+sdeyxol2ksETlMAelaBVZURLUl0HFnWBEuS8RECgYEA2qtliJ1WW8i9hlBZLjT3eoPr0Y7QhlxlR6zwGX5OU2f57nFnLWJtES9D60xD2cnnCm+tTGisTlWVh0MMfW9TQW03TqaKPZIZolBGbekd3h3h9CPugTvukMPfGjihdjEXSZYNINfZTh+LG2cO1T4R86NwE4XRQqoYGXajfYqnk+kCgYEAtZwmtpwwV0iSMWde3mn6zDkGTcuDVIEBfjzhF0aDLFlf1jjmSn4GwmGOTVVThxXltaYU++ws/avROBWtuY2nFyBRmQuTqyn69hPH3gghlw4TvJx8UcLk4g/LFdtfLMFLBbyX3RAbIpfcBCV0l3UYUY96y2Tkl0ULSULoxaf3xiECgYEAmsVJfiJup1Qz4Mcp9+yBZXPOC2rb6N2oycx7vG+LnLkxSzV048iQjeM4XWiDdbjUEKzuqfBEVMV6qlwokPeko+Bbjw2NNvsbrajH0K949meMMDLmcw4qUshwNqzyiyc/5lOQQzjDk+n7mY+eDgx6xElf4FGMxXSCzjMMkKT861ECgYBPHfsywzmI+lUgs5KXAfaHvSrGaybIIc1Y35jVX7CkR+sFeJFo2nMGyEEbG66C08T28rF1yNo/m6mOZ9mK5mFtCTljVuMjeAzEVA0JIECh9Uda2qdEIv1G0mSX0VxGvG3o3jVFe8DkwfEyu8h3QQ6oxp9vFLlAYDUu4oqGURp67Q==';
            $c->format = "json";
            $c->charset = "GBK";
            $c->version = "2.0";


            if ($user_id) {
                //2.执行相应的接口获得相应的业务
                $obj = new AlipayEbppInvoiceTitleListGetRequest();
                $obj->setBizContent("{" .
                    "\"user_id\":\"" . $user_id . "\"" .
                    "  }");
            } else {
                //2.执行相应的接口获得相应的业务
                $obj = new AlipayEbppInvoiceTitleDynamicGetRequest();
                $obj->setBizContent("{" .
                    "\"bar_code\":\"" . $bar_code . "\"" .
                    "  }");
            }

            $result = $c->execute($obj,'','');
            $responseNode = str_replace(".", "_", $obj->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            if (!empty($resultCode) && $resultCode == 10000) {
                $err = [
                    'return_code' => 'SUCCESS',
                    'return_msg' => null,
                    'result_code' => 'SUCCESS',
                    'result_msg' => '获取成功',
                    'title' =>json_encode($result->$responseNode->title) ,
                ];
            } else {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '获取失败',
                ];
            }


            return $this->return_data($err);


        } catch (\Exception $exception) {
            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);

        }
    }


}