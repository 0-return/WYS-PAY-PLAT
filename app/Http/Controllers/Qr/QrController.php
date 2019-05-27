<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/2/17
 * Time: 11:45
 */

namespace App\Http\Controllers\Qr;


use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\PayWaysController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Http\Controllers\Controller;
use App\Models\QrCodeHb;
use App\Models\QrPayInfo;
use App\Models\Store;
use App\Models\StorePayWay;
use App\Models\WeixinConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class QrController extends Controller
{
    //多码合一
    public function qr(Request $request)
    {

        //可以传门店、收银员、支付渠道、
        $no = $request->get('no');//码编号
        $store_id = $request->get('store_id', '');//门店ID
        $merchant_id = $request->get('merchant_id', '');//收银员
        $company = $request->get('company', '');//支付渠道公司
        $other_no = $request->get('other_no', '');//收银员
        $notify_url = $request->get('notify_url', '');//收银员

        $url = $request->url();
        $is_https = substr($url, 0, 5);
        if ($is_https != "https") {
            $url = $request->server();
            return redirect('https://' . $url['SERVER_NAME'] . $url['REQUEST_URI']);
        }

        //门店ID 为空 编号不能为空
        if ($store_id == "") {
            //编号不能为空
            if ($no == "") {
                $message = "空码编号或者门店ID不能为空";
                return view('errors.page_errors', compact('message'));
            }

            $qr = QrPayInfo::where('code_number', $no)
                ->select('store_id', 'merchant_id')
                ->first();

            if (!$qr) {
                $message = "码没有绑定激活";
                return view('errors.page_errors', compact('message'));
            }

            $store_id = $qr->store_id;
            $merchant_id = $qr->merchant_id;
        }


        if ($store_id) {
            $pay_type = "other";
            //判断是不是微信
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
                $pay_type = 'weixin';
            }
            //判断是不是支付宝
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false) {
                $pay_type = 'alipay';
            }
            //判断是不是翼支付
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Bestpay') !== false) {
                $pay_type = 'Bestpay';
            }
            //判断是不是京东
            if (strpos($_SERVER['HTTP_USER_AGENT'], 'WalletClient') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'JDJR-App') !== false) {
                $pay_type = 'jd';
            }

            if (strpos($_SERVER['HTTP_USER_AGENT'], 'WalletClient') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'jdapp') !== false) {
                $pay_type = 'jd';
            }

            $where = [];
            $where[] = ['ways_source', '=', $pay_type];
            $where[] = ['store_id', '=', $store_id];


            $store = Store::where('store_id', $store_id)
                ->select('config_id', 'pid', 'merchant_id', 'store_name', 'store_address')
                ->first();
            //
            if (!$store) {
                $message = "门店不存在";
                return view('errors.page_errors', compact('message'));

            }
            $store_pid = $store->pid;
            $obj_ways = new PayWaysController();
            $StorePayWay = $obj_ways->ways_source($pay_type, $store_id, $store_pid);

            if ($company) {
                $StorePayWay = $obj_ways->company_ways_source($company, $pay_type, $store_id, $store_pid);
            }
            if (!$StorePayWay) {
                $message = "没有找到合适的支付方式";
                return view('errors.page_errors', compact('message'));
            }

            $ways_type = $StorePayWay->ways_type;


            //商户id为空
            if ($merchant_id == "") {
                //读取
                $merchant_id = $store->merchant_id;
            }


            //配置
            $state = [
                'store_id' => $store_id,
                'store_name' => $store->store_name,
                'store_address' => $store->store_address,
                'config_id' => $store->config_id,
                'merchant_id' => $merchant_id,
                'auth_type' => '02',
                'other_no' => $other_no,
                'notify_url' => $notify_url,
                'store_pid' => $store_pid,
            ];

            //支付宝
            if ($pay_type == 'alipay') {
                $isvconfig = new AlipayIsvConfigController();
                $config = $isvconfig->AlipayIsvConfig($store->config_id);
                //官方支付宝
                if ($ways_type == 1000) {
                    $state['bank_type'] = 'alipay';
                    $state = \App\Common\TransFormat::encode($state);
                    $app_auth_url = $config->alipay_app_authorize;
                    $code_url = $app_auth_url . '?app_id=' . $config->app_id . "&redirect_uri=" . $config->callback . '&scope=auth_base&state=' . $state;
                    return redirect($code_url);

                }
                //网商支付宝
                if ($ways_type == 3001) {
                    $state['bank_type'] = 'mbalipay';
                    $state = \App\Common\TransFormat::encode($state);
                    $app_auth_url = $config->alipay_app_authorize;
                    $code_url = $app_auth_url . '?app_id=' . $config->app_id . "&redirect_uri=" . $config->callback . '&scope=auth_base&state=' . $state;
                    return redirect($code_url);

                }

                //京东支付支付宝
                if ($ways_type == 6001) {
                    $state['bank_type'] = 'jdalipay';
                    $state = \App\Common\TransFormat::encode($state);
                    $app_auth_url = $config->alipay_app_authorize;
                    $code_url = $app_auth_url . '?app_id=' . $config->app_id . "&redirect_uri=" . $config->callback . '&scope=auth_base&state=' . $state;
                    return redirect($code_url);

                }


                //新大陆支付宝
                if ($ways_type == 8001) {
                    $state['bank_type'] = 'nlalipay';
                    $state = \App\Common\TransFormat::encode($state);
                    $app_auth_url = $config->alipay_app_authorize;
                    $code_url = $app_auth_url . '?app_id=' . $config->app_id . "&redirect_uri=" . $config->callback . '&scope=auth_base&state=' . $state;
                    return redirect($code_url);
                }

                //会员宝支付宝
                if ($ways_type == 9001) {
                    $state['bank_type'] = 'halipay';
                    $state = \App\Common\TransFormat::encode($state);
                    $app_auth_url = $config->alipay_app_authorize;
                    $code_url = $app_auth_url . '?app_id=' . $config->app_id . "&redirect_uri=" . $config->callback . '&scope=auth_base&state=' . $state;
                    return redirect($code_url);
                }


                //联拓富支付支付宝
                if ($ways_type == 10001) {
                    $state['bank_type'] = 'lftalipay';
                    $state = \App\Common\TransFormat::encode($state);
                    $app_auth_url = $config->alipay_app_authorize;
                    $code_url = $app_auth_url . '?app_id=' . $config->app_id . "&redirect_uri=" . $config->callback . '&scope=auth_base&state=' . $state;
                    return redirect($code_url);
                }


                //富有支付宝
                if ($ways_type == 11001) {
                    $state['bank_type'] = 'fuioualipay';
                    $state = \App\Common\TransFormat::encode($state);
                    $app_auth_url = $config->alipay_app_authorize;
                    $code_url = $app_auth_url . '?app_id=' . $config->app_id . "&redirect_uri=" . $config->callback . '&scope=auth_base&state=' . $state;
                    return redirect($code_url);
                }

            }


            //微信
            if ($pay_type == 'weixin') {

                $config = new WeixinConfigController();
                $WeixinConfig = $config->weixin_config_obj($store->config_id);

                if (!$WeixinConfig) {
                    $message = "微信配置不存在~";
                    return view('errors.page_errors', compact('message'));

                }
                $config_type = $WeixinConfig->config_type;

                $code_url = '';
                //官方微信支付
                if ($ways_type == 2000) {
                    $state['bank_type'] = 'weixin';
                    $state['scope_type'] = 'snsapi_base';
                    $state['config_id'] = $store->config_id;

                    //开放平台代替授权
                    if ($config_type == "2") {
                        $state['authorizer_appid'] = $WeixinConfig->authorizer_appid;
                        $state['authorizer_refresh_token'] = $WeixinConfig->authorizer_refresh_token;
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/weixinopen/oauth?state=' . $state);
                        return redirect($code_url);

                    } //服务商特约
                    else {
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/weixin/oauth?state=' . $state);
                        return redirect($code_url);

                    }
                }

                //网商微信支付
                if ($ways_type == 3002) {

                    $array = [
                        'https://dl.shouqianloupay.cn',
                        'https://x.xdspay.com',
                        'https://pay.ycfgd.com',
                        'https://pay.umxnt.com',
                        'https://pay.shouqupay.com',
                        'https://ss.tonlot.com',
                        'https://yb.xiangyongpay.com',
                        'https://pay.fuya18.com',
                        'https://pay.mynkj.cn',
                    ];

                    if (in_array(url(''), $array)) {
                        //看下配置是不是想用网商的是的话就去想用那边拿openID
                        $config_type = '3';//需要使用想用网商微信的其他独立平台
                    }


                    $state['bank_type'] = 'mybank_weixin';
                    $state['scope_type'] = 'snsapi_base';
                    $state['config_id'] = $store->config_id;

                    //开放平台代替授权
                    if ($config_type == "2") {
                        $state['authorizer_appid'] = $WeixinConfig->authorizer_appid;
                        $state['authorizer_refresh_token'] = $WeixinConfig->authorizer_refresh_token;
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/weixinopen/oauth?state=' . $state);
                        return redirect($code_url);

                    } elseif ($config_type == "3") {
                        //跳转到想用去获取openid
                        $state['callback_url'] = url('/api/mybank/weixin/pay_view');
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = 'https://x.umxnt.com/api/mybank/weixin/oauth_openid?state=' . $state;

                        return redirect($code_url);

                    } //服务商特约
                    else {
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/mybank/weixin/oauth?state=' . $state);
                        return redirect($code_url);
                    }


                }

                //京东微信支付
                if ($ways_type == 6002) {
                    $state['bank_type'] = 'jd_weixin';
                    $state['scope_type'] = 'snsapi_base';
                    $state['config_id'] = $store->config_id;

                    //开放平台代替授权
                    if ($config_type == "2") {
                        $state['authorizer_appid'] = $WeixinConfig->authorizer_appid;
                        $state['authorizer_refresh_token'] = $WeixinConfig->authorizer_refresh_token;
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/weixinopen/oauth?state=' . $state);
                        return redirect($code_url);

                    } //服务商特约
                    else {
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/jd/weixin/oauth?state=' . $state);
                        return redirect($code_url);
                    }


                }

                //新大陆微信支付
                if ($ways_type == 8002) {
                    $state['bank_type'] = 'nl_weixin';
                    $state['scope_type'] = 'snsapi_base';
                    $state['config_id'] = $store->config_id;

                    //开放平台代替授权
                    if ($config_type == "2") {
                        $state['authorizer_appid'] = $WeixinConfig->authorizer_appid;
                        $state['authorizer_refresh_token'] = $WeixinConfig->authorizer_refresh_token;
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/weixinopen/oauth?state=' . $state);
                        return redirect($code_url);

                    } //服务商特约
                    else {
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/newland/weixin/oauth?state=' . $state);
                        return redirect($code_url);
                    }
                }

                //和融通微信支付
                if ($ways_type == 9002) {
                    $state['bank_type'] = 'h_weixin';
                    $state['scope_type'] = 'snsapi_base';
                    $state['config_id'] = $store->config_id;

                    //开放平台代替授权
                    if ($config_type == "2") {
                        $state['authorizer_appid'] = $WeixinConfig->authorizer_appid;
                        $state['authorizer_refresh_token'] = $WeixinConfig->authorizer_refresh_token;
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/weixinopen/oauth?state=' . $state);
                        return redirect($code_url);

                    } //服务商特约
                    else {
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/huiyuanbao/weixin/oauth?state=' . $state);
                        return redirect($code_url);
                    }
                }


                //微信支付
                if ($ways_type == 10002) {
                    $state['bank_type'] = 'ltf_weixin';
                    $state['scope_type'] = 'snsapi_base';
                    $state['config_id'] = $store->config_id;

                    //开放平台代替授权
                    if ($config_type == "2") {
                        $state['authorizer_appid'] = $WeixinConfig->authorizer_appid;
                        $state['authorizer_refresh_token'] = $WeixinConfig->authorizer_refresh_token;
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/weixinopen/oauth?state=' . $state);
                        return redirect($code_url);

                    } //服务商特约
                    else {
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/ltf/weixin/oauth?state=' . $state);
                        return redirect($code_url);
                    }
                }

                //  富友微信支付
                if ($ways_type == 11002) {
                    $state['bank_type'] = 'fuioy_weixin';
                    $state['scope_type'] = 'snsapi_base';
                    $state['config_id'] = $store->config_id;

                    //开放平台代替授权
                    if ($config_type == "2") {
                        $state['authorizer_appid'] = $WeixinConfig->authorizer_appid;
                        $state['authorizer_refresh_token'] = $WeixinConfig->authorizer_refresh_token;
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/weixinopen/oauth?state=' . $state);
                        return redirect($code_url);

                    } //服务商特约
                    else {
                        $state = \App\Common\TransFormat::encode($state);
                        $code_url = url('api/fuiou/weixin/oauth?state=' . $state);
                        return redirect($code_url);
                    }
                }


            }

            //京东
            if ($pay_type == 'jd') {
                //京东金融京东支付
                if ($ways_type == 6003) {
                    $data = $state;
                    return view('jd.jd', compact('data'));
                }

            }


            if ($pay_type == "other") {
                $message = "您使用的客户端与要求不符~";
                return view('errors.page_errors', compact('message'));

            }


        } else {
            $message = "  二维码没有绑定激活";
            return view('errors.page_errors', compact('message'));

        }
    }


    public function qr_code_hb(Request $request)
    {
        $pay_type = "";
        $id = $request->get('id', '');//门店ID

        //判断是不是微信
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            $pay_type = 'weixin';
        }
        //判断是不是支付宝
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false) {
            $pay_type = 'alipay';
        }
        //判断是不是翼支付
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Bestpay') !== false) {
            $pay_type = 'Bestpay';
        }
        //判断是不是京东
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'WalletClient') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'JDJR-App') !== false) {
            $pay_type = 'jd';
        }

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'WalletClient') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'jdapp') !== false) {
            $pay_type = 'jd';
        }

        $QrCodeHb = QrCodeHb::where('id', $id)->first();
        if (!$QrCodeHb) {
            $message = "  二维码ID不正确";
            return view('errors.page_errors', compact('message'));
        }

        if ($pay_type == "alipay") {
            return redirect($QrCodeHb->ali_url);
        } elseif ($pay_type == "weixin") {
            $url = $url = $QrCodeHb->wx_url;
            $url = "http://qr.topscan.com/api.php?text=" . $url;
            return redirect($url);
        } else {
            $url = url('/qr_code_hb?id=') . $id;

            //方式2
            $url = url('/qr_code_hb?id=') . $id;
            $url = "http://qr.topscan.com/api.php?text=" . $url;
            return redirect($url);
        }
    }
}