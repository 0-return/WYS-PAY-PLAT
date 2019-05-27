<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:17
 */

namespace App\Api\Controllers\DfPay;


use App\Models\DfOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MyBank\Tools;

class IndexController extends \App\Api\Controllers\BaseController
{


    public function info_import(Request $request)
    {
        try {

            $public = $this->parseToken();
            $config_id = $public->config_id;
            $pay_password = $request->get('pay_password');
            $check_data = [
                'pay_password' => '支付密码',
            ];

            $check = $this->check_required($request->all(), $check_data);
            if ($check) {
                $this->status = 2;
                $this->message = $check;
                return $this->format();
            }

            if (empty($_FILES)) {
                $this->status = 2;
                $this->message = '请上传xlsx表格！';
                return $this->format();
            }

            $file_arr = array_shift($_FILES);

            if ($file_arr['error'] !== 0) {

                $this->status = 2;
                $this->message = '请上传xlsx表格！';
                return $this->format();
            }
            // var_dump($file_arr);die;
            $file = $file_arr['tmp_name'];


            $excel_data = \App\Common\Excel\Excel::_readExcel($file);

            foreach ($excel_data as $k => $v) {

                if ($k == 0) {
                    continue;
                }

                if (trim($v[0]) == "") {
                    continue;
                }

                if (trim($v[1]) == "") {
                    continue;
                }
                if (trim($v[2]) == "") {
                    continue;
                }
                if (trim($v[3]) == "") {
                    continue;
                }

                //1.入库
                $order_number = date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                $merchant_number = 'SHID20190220190';

                $in_data = [
                    'config_id' => $config_id,
                    'order_number' => $order_number,
                    'merchant_number' => $merchant_number,
                    'order_id' => '',
                    'in_order_id' => "",
                    'deal_time' => '',
                    'amount' => $v[3],
                    'pay_status' => '2',
                    'pay_status_desc' => "等待划款",
                    'account_number' => $v[1],
                    'customer_name' => $v[0],
                    'issue_bank_name' => $v[2],
                    'memo' => isset($v[4]) && $v[4] ? $v[4] : "",
                ];

                $DfOrder = DfOrder::create($in_data);
                $DfOrder_id = $DfOrder->id;


                $config = [
                    'aid' => '8a179b8c67bcf908016938901d3628ae',//应用ID
                    'key' => 'shudh|m2|20190301',//密钥
                    'api_id' => array('PAYING' => 'eb_trans@agent_for_paying', 'RESULT' => 'eb_trans@get_order_deal_result'),//接口ID
                    'debug' => 'false',//是否调试模式
                    'url' => 'https://api.gomepay.com/CoreServlet',//10步模式接口地址
                    'mode' => '0',
                    'url_ac' => 'https://api.gomepay.com/CoreServlet',//url_ac：4步模式的M2服务地址，4步模式时必填项。
                    'url_ac_token' => 'https://api.gomepay.com/access_token',//url_ac_token：4步模式的得到访问令牌的M2服务地址，4步模式时必填项。
                    'max_token' => 2,//获取令牌最大次数
                    'is_data_sign' => '1',//是否对数据包执行签名 1是  0否
                    'memcache_open' => false,//是否使用memcache
                    'memcached_server' => '127.0.0.1:11211',//memcache地址
                ];


                $data = [
                    'req_no' => time(),
                    'app_code' => 'apc_02000004943',
                    'app_version' => '1.0.0',
                    'plat_form' => '01',
                    'merchant_number' => $merchant_number,//
                    'order_number' => $order_number,
                    'service_code' => 'sne_00000000002',
                    'wallet_id' => '0100851892326086',
                    'asset_id' => '260d65fb2d33445ba26c087c9a556902',
                    'business_type' => '1',
                    'money_model' => '1',
                    'source' => '0',
                    'password_type' => '02',
                    'encrypt_type' => '02',
                    'pay_password' => md5($pay_password),
                    'customer_type' => '01',
                    'customer_name' => $v[0],
                    'account_number' => $v[1],
                    'issue_bank_name' => $v[2],
                    'currency' => 'CNY',
                    'amount' => $v[3],
                    'async_notification_addr' => url('/api/dfpay/pay_notify'),
                    'memo' => $v[4],

                ];
                $obj = new \App\Api\Controllers\DfPay\BaseController($config);
                $data = $obj->url_data('PAYING', $data, "POST");
                $res = json_decode($data, true);


                //请求成功
                if ($res['op_ret_code'] == '000') {


                } else {
                    $DfOrder = DfOrder::where('id', $DfOrder_id)
                        ->update([
                            'pay_status' => '3',
                            'pay_status_desc' => $res['op_err_msg'],
                        ]);
                }
            }


            return json_encode([
                'status' => 1,
                'data' => [],
                'message' => '导入成功'
            ]);

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

    //代付列表
    public function order_list(Request $request)
    {
        try {
            $public = $this->parseToken();

            $obj = DB::table('df_order');

            $this->t = $obj->count();

            $data = $this->page($obj)
                ->orderBy('updated_at', 'desc')
                ->get();


            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }
}