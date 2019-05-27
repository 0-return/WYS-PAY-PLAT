<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/26
 * Time: 下午12:05
 */

namespace App\Api\Controllers\Merchant;


use App\Api\Controllers\BaseController;
use App\Api\Controllers\Device\YlianyunAopClient;
use App\Api\Controllers\Device\ZlbzController;
use App\Models\Device;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InfoController extends BaseController
{

    public function info_list(Request $request)
    {


        try {
            $merchant = $this->parseToken();
            $config_id = $merchant->config_id;
            $desc = '暂无消息';
            $updated_at = date('Y-m-d H:i:s', time());
            $desc1 = $desc;
            $updated_at1 = $updated_at;
            $notice = DB::table('notice_news')
                ->where('config_id', $config_id)
                ->where('type', 'notice')
                ->first();

            if ($notice) {
                $desc = $notice->title;
                $updated_at = $notice->updated_at;
            } else {
                $notice = DB::table('notice_news')
                    ->where('config_id', '1234')
                    ->where('type', 'notice')
                    ->first();
                if ($notice) {
                    $desc = $notice->title;
                    $updated_at = $notice->updated_at;
                }
            }

            $news = DB::table('notice_news')
                ->where('config_id', $config_id)
                ->where('type', 'news')
                ->first();
            if ($news) {
                $desc1 = $news->title;
                $updated_at1 = $news->updated_at;
            } else {
                $news = DB::table('notice_news')
                    ->where('config_id', '1234')
                    ->where('type', 'news')
                    ->first();
                if ($news) {
                    $desc1 = $news->title;
                    $updated_at1 = $news->updated_at;
                }
            }
            $data = [
                [

                    'title' => '系统公告',
                    'type' => 'notice',
                    'unread' => "0",
                    'desc' => $desc,
                    'updated_at' => $updated_at

                ],
                [
                    'title' => '消息通知',
                    'type' => 'news',
                    'unread' => "0",
                    'desc' => $desc1,
                    'updated_at' => $updated_at1

                ]
            ];
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);
        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

    public function notice_news(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $type = $request->get('type', 'news');

            $obj = DB::table('notice_news');
            $where = [];


            if ($type) {
                $where[] = ['type', '=', $type];
            }
            $obj = $obj->where($where);
            $this->t = $obj->count();
            $data = $this->page($obj)
                ->orderBy('created_at', 'desc')
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


    public function fuwu(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $obj = DB::table('merchant_fuwus');
            $where = [];
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = '';
            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
            }
            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
            }
            if ($merchant_id) {
                $where[] = ['merchant_id', '=', $merchant_id];

            }

            $obj = $obj->where($where);
            $this->t = $obj->count();
            $data = $this->page($obj)->orderBy('created_at', 'desc')
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


    //对账打印
    public function order_count_print(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $print_data = Cache::get($request->get('print_id'));
            $device_type = $request->get('device_type');

            if (!isset($print_data) && $print_data == "") {
                return json_encode([
                    'status' => 2,
                    'message' => '数据已过期，请重新筛选对账数据'
                ]);
            }


            $print_data = json_decode($print_data, true);
            $check_data = [
                'print_id' => '打印ID',
                'device_type' => '设备类型',
            ];


            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            $merchant_name = "全部收银员";
            $store_name = "全部门店";
            $store_id = '全部门店';

            if ($print_data['store_id']) {
                $store_id = $print_data['store_id'];
                $store = Store::where('store_id', $print_data['store_id'])
                    ->select('store_name')
                    ->first();


                if ($store) {
                    $store_name = $store->store_name;
                }

            }
            if ($print_data['merchant_id']) {
                $merchant = Merchant::where('id', $print_data['merchant_id'])
                    ->select('name')
                    ->first();

                if ($merchant) {
                    $merchant_name = $merchant->name;
                }
            }


            //如果不是pos机检测
            if ($device_type != "pos") {

                $this->print_data($print_data, $store_name, $merchant_name);

                return json_encode([
                    'status' => 1,
                    'message' => '出票请求成功',
                    'data' => '',
                ]);


            } else {
                $time_start = $print_data['time_start'];
                $time_end = $print_data['time_end'];

                $alipay_count = $print_data['alipay_total_count'];
                $alipay_sum = $print_data['alipay_total_amount'];
                $re_alipay_count = $print_data['alipay_refund_count'];
                $re_alipay_sum = $print_data['alipay_refund_amount'];
                $alipay_all = $print_data['alipay_get_amount'];


                $weixin_count = $print_data['weixin_total_count'];
                $weixin_sum = $print_data['weixin_total_amount'];
                $re_weixin_count = $print_data['weixin_refund_count'];
                $re_weixin_sum = $print_data['weixin_refund_amount'];
                $weixin_all = $print_data['weixin_get_amount'];


                $jd_count = $print_data['jd_total_count'];
                $jd_sum = $print_data['jd_total_amount'];
                $re_jd_count = $print_data['jd_refund_count'];
                $re_jd_sum = $print_data['jd_refund_amount'];
                $jd_all = $print_data['jd_get_amount'];


                $un_count = $print_data['un_total_count'];
                $un_sum = $print_data['un_total_amount'];
                $re_un_count = $print_data['un_refund_count'];
                $re_un_sum = $print_data['un_refund_amount'];
                $un_all = $print_data['un_get_amount'];


                $unqr_count = $print_data['unqr_total_count'];
                $unqr_sum = $print_data['unqr_total_amount'];
                $re_unqr_count = $print_data['unqr_refund_count'];
                $re_unqr_sum = $print_data['unqr_refund_amount'];
                $unqr_all = $print_data['unqr_get_amount'];


                $count = $print_data['total_count'];
                $sum = $print_data['total_amount'];


                $data = "门店：" . $store_name .
                    "\r\n门店编号：" . $store_id .
                    "\r\n开始时间：" . $time_start .
                    "\r\n结束时间：" . $time_end .
                    "\r\n收银员：" . $merchant_name .
                    "\r\n\r\n支付类型    当前设备/合计\r\n-------------支付宝-------------\r\n交易笔数：" . $alipay_count . "\r\n交易金额：" . $alipay_sum . "\r\n退款笔数：" . $re_alipay_count . "\r\n退款金额：" . $re_alipay_sum . "\r\n支付宝收入合计：" . $alipay_all . "\r\n\r\n
                 \r\n支付类型    当前设备/合计\r\n-------------微信支付-------------\r\n交易笔数：" . $weixin_count . "\r\n交易金额：" . $weixin_sum . "\r\n退款笔数：" . $re_weixin_count . "\r\n退款金额：" . $re_weixin_sum . "\r\n微信收入合计：" . $weixin_all . "\r\n\r\n
                 \r\n支付类型    当前设备/合计\r\n-------------京东支付-------------\r\n交易笔数：" . $jd_count . "\r\n交易金额：" . $jd_sum . "\r\n退款笔数：" . $re_jd_count . "\r\n退款金额：" . $re_jd_sum . "\r\n微信收入合计：" . $jd_all . "\r\n\r\n
                 \r\n支付类型    当前设备/合计\r\n-------------银联刷卡-------------\r\n交易笔数：" . $un_count . "\r\n交易金额：" . $un_sum . "\r\n退款笔数：" . $re_un_count . "\r\n退款金额：" . $re_un_sum . "\r\n刷卡收入合计：" . $un_all . "\r\n\r\n
                 \r\n支付类型    当前设备/合计\r\n-------------银联扫码-------------\r\n交易笔数：" . $unqr_count . "\r\n交易金额：" . $unqr_sum . "\r\n退款笔数：" . $re_unqr_count . "\r\n退款金额：" . $re_unqr_sum . "\r\n刷卡收入合计：" . $unqr_all . "\r\n\r\n
                  \r\n\r\n总计成功笔数：" . $count . "\r\n总计成功金额：" . $sum . "\r\n打印时间" . date('Y-m-d H:i:s', time()) . "
                ";
                return json_encode([
                    'status' => 1,
                    'message' => '小票出票成功',
                    'data' => $data,
                ]);

            }
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }


    //打印
    public function print_data($data, $store_name, $merchant_name)
    {
        try {

            $p = Device::where('store_id', $data['store_id'])
                ->where('merchant_id', $data['merchant_id'])
                ->where("type", "p")->get();

            //收银员未绑定走门店机器
            if ($p->isEmpty()) {
                $p = Device::where('store_id', $data['store_id'])
                    ->where('merchant_id', '')
                    ->where("type", "p")->get();
            }


            if (!$p->isEmpty()) {


                foreach ($p as $v) {
                    //智联
                    if ($v->device_type == "p_zlbz_1") {
                        $device_id = $v->device_no;
                        $this->print_send($device_id, $store_name, $merchant_name, $data);
                        try {

                        } catch (\Exception $exception) {
                            \Illuminate\Support\Facades\Log::info($exception);
                            continue;
                        }
                    }

                    //K4
                    if ($v->device_type == "p_yly_k4") {

                    }


                }
            }


        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);

        }

    }


    //智联博众发送打印
    public function print_send($device_id, $store_name, $merchant_name, $print_data)
    {
        try {
            $Zlbz = new ZlbzController();
            $secretkey = 'zlbz-cloud';
            $server = 'http://121.199.68.96/o2o-print/print.php';
            //时间戳
            $time = time();
            $querystring = "action=send&device_id={$device_id}&secretkey={$secretkey}&timestamp={$time}&";

            $time_start = $print_data['time_start'];
            $time_end = $print_data['time_end'];

            $alipay_count = $print_data['alipay_total_count'];
            $alipay_sum = $print_data['alipay_total_amount'];
            $re_alipay_count = $print_data['alipay_refund_count'];
            $re_alipay_sum = $print_data['alipay_refund_amount'];
            $alipay_all = $print_data['alipay_get_amount'];


            $weixin_count = $print_data['weixin_total_count'];
            $weixin_sum = $print_data['weixin_total_amount'];
            $re_weixin_count = $print_data['weixin_refund_count'];
            $re_weixin_sum = $print_data['weixin_refund_amount'];
            $weixin_all = $print_data['weixin_get_amount'];


            $jd_count = $print_data['jd_total_count'];
            $jd_sum = $print_data['jd_total_amount'];
            $re_jd_count = $print_data['jd_refund_count'];
            $re_jd_sum = $print_data['jd_refund_amount'];
            $jd_all = $print_data['jd_get_amount'];


            $un_count = $print_data['un_total_count'];
            $un_sum = $print_data['un_total_amount'];
            $re_un_count = $print_data['un_refund_count'];
            $re_un_sum = $print_data['un_refund_amount'];
            $un_all = $print_data['un_get_amount'];


            $unqr_count = $print_data['unqr_total_count'];
            $unqr_sum = $print_data['unqr_total_amount'];
            $re_unqr_count = $print_data['unqr_refund_count'];
            $re_unqr_sum = $print_data['unqr_refund_amount'];
            $unqr_all = $print_data['unqr_get_amount'];


            $count = $print_data['total_count'];
            $sum = $print_data['total_amount'];


            $data = "门店：" . $store_name .
                "\r\n门店编号：" . $print_data['store_id'] .
                "\r\n开始时间：" . $print_data['time_start'] .
                "\r\n结束时间：" . $print_data['time_end'] .
                "\r\n收银员：" . $merchant_name .
                "\r\n\r\n支付类型    当前设备/合计\r\n-------------支付宝-------------\r\n交易笔数：" . $alipay_count . "\r\n交易金额：" . $alipay_sum . "\r\n退款笔数：" . $re_alipay_count . "\r\n退款金额：" . $re_alipay_sum . "\r\n支付宝收入合计：" . $alipay_all . "\r\n\r\n
                 \r\n支付类型    当前设备/合计\r\n-------------微信支付-------------\r\n交易笔数：" . $weixin_count . "\r\n交易金额：" . $weixin_sum . "\r\n退款笔数：" . $re_weixin_count . "\r\n退款金额：" . $re_weixin_sum . "\r\n微信收入合计：" . $weixin_all . "\r\n\r\n
                 \r\n支付类型    当前设备/合计\r\n-------------京东支付-------------\r\n交易笔数：" . $jd_count . "\r\n交易金额：" . $jd_sum . "\r\n退款笔数：" . $re_jd_count . "\r\n退款金额：" . $re_jd_sum . "\r\n微信收入合计：" . $jd_all . "\r\n\r\n
                 \r\n支付类型    当前设备/合计\r\n-------------银联刷卡-------------\r\n交易笔数：" . $un_count . "\r\n交易金额：" . $un_sum . "\r\n退款笔数：" . $re_un_count . "\r\n退款金额：" . $re_un_sum . "\r\n刷卡收入合计：" . $un_all . "\r\n\r\n
                 \r\n支付类型    当前设备/合计\r\n-------------银联扫码-------------\r\n交易笔数：" . $unqr_count . "\r\n交易金额：" . $unqr_sum . "\r\n退款笔数：" . $re_unqr_count . "\r\n退款金额：" . $re_unqr_sum . "\r\n刷卡收入合计：" . $unqr_all . "\r\n\r\n
                  \r\n\r\n总计成功笔数：" . $count . "\r\n总计成功金额：" . $sum . "\r\n打印时间" . date('Y-m-d H:i:s', time()) . "
                ";


            $data = mb_convert_encoding($data, "GBK", "UTF-8");
            //base64加密一下打印内容
            $data = base64_encode($data . "\x0d\x0a");
            //sha1($querystring.$data) 生成请求签名
            $querystring .= "sign=" . sha1($querystring . $data);
            $url = $server . "?" . $querystring;
            $re = $Zlbz->PostData($url, $data);

        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }


}