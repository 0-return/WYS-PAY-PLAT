<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/10/24
 * Time: 下午1:35
 */

namespace App\Api\Controllers\user;


use App\Api\Controllers\BaseController;
use App\Models\QrCodeHb;
use App\Models\QrList;
use App\Models\QrListInfo;
use App\Models\QrPayInfo;
use App\Models\Store;
use App\Models\User;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Comodojo\Zip\Zip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Zxing\QrReader;
use PHPZxing\PHPZxingDecoder;


class QrController extends BaseController
{

    //
    public function QrLists(Request $request)
    {
        try {

            //下个版本去掉


            $QrList = QrList::all();

            foreach ($QrList as $k => $v) {
                $cno = $v->cno;
                $count = QrListInfo::where('cno', $cno)
                    ->where('code_type', 1)
                    ->count('id');

                QrList::where('cno', $cno)->update([
                    's_num' => $count
                ]);
            }


            ////下个版本去掉结束


            $user = $this->parseToken();
            $obj = DB::table('qr_lists');


            $obj = $obj->where('user_id', $user->user_id)->orderBy('created_at', 'desc');
            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }

    //查看绑定列表
    public function QrListinfos(Request $request)
    {
        try {
            $user = $this->parseToken();
            $cno = $request->get('cno');
            $code_type = $request->get('code_type', 0);
            $code_number = $request->get('code_number', '');


            //已经绑定了
            if ($code_type) {
                $where = [];

                if ($cno) {
                    $where[] = ['qr_pay_infos.cno', $cno];
                }
                if ($code_type) {
                    $where[] = ['qr_pay_infos.code_type', $code_type];

                }

                if ($user->user_id) {
                    $where[] = ['qr_pay_infos.user_id', $user->user_id];
                }

                if ($code_number) {
                    $where[] = ['code_number', $code_number];
                }

                $obj = DB::table('qr_pay_infos');
                $obj->join('stores', 'qr_pay_infos.store_id', 'stores.store_id');
                $obj = $obj->where($where)
                    ->select('qr_pay_infos.*', 'stores.store_short_name')
                    ->orderBy('qr_pay_infos.created_at', 'desc');

                $this->t = $obj->count();
                $data = $this->page($obj)->get();
                $this->status = 1;
                $this->message = '数据返回成功';
                return $this->format($data);
            } else {
                $where = [];
                if ($code_number) {
                    $where[] = ['code_number', $code_number];
                }
                if ($cno) {
                    $where[] = ['cno', $cno];
                }


                $obj = DB::table('qr_list_infos');
                $obj = $obj->where('user_id', $user->user_id)
                    ->where('code_type', $code_type)
                    ->where($where)
                    ->orderBy('created_at', 'desc');
                $this->t = $obj->count();
                $data = $this->page($obj)->get();
                $this->status = 1;
                $this->message = '数据返回成功';
                return $this->format($data);

            }


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }

    }

    //绑定空码
    public function bindQr(Request $request)
    {

        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $code_number = $request->get('code_number', '');
            $merchant_id = $request->get('merchant_id', '');
            $QrListInfo = QrListInfo::where('code_number', $code_number)->first();
            if (!$QrListInfo) {
                return json_encode(['status' => 2, 'message' => '二维码不存在']);
            }

            $store = Store::where('store_id', $store_id)
                ->select('id', 'merchant_id')
                ->first();
            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店ID不存在']);
            }

            if ($QrListInfo->code_type) {
                $store_id = $QrListInfo->store_id;
                return json_encode(['status' => 2, 'message' => '二维码已经被' . $store_id . '绑定']);
            }

            $datainfo = $QrListInfo->toArray();
            $datainfo['store_id'] = $store_id;
            $datainfo['code_type'] = 1;
            if (!$merchant_id||$merchant_id=="NULL") {
                $merchant_id = $store->merchant_id;
            }
            $datainfo['merchant_id'] = $merchant_id;

            //开启事务
            try {
                DB::beginTransaction();

                QrPayInfo::create($datainfo);


                $QrListInfo->update(
                    [
                        'code_type' => 1,
                        'store_id' => $store_id,

                    ]
                );
                $QrListInfo->save();

                //已经使用加 1
                $QrList = QrList::where('cno', $QrListInfo->cno)->first();
                $s_num = $QrList->s_num;
                $QrList->s_num = $s_num + 1;
                $QrList->save();

                DB::commit();
            } catch (\Exception $e) {
                dd($e);
                DB::rollBack();
            }


            return json_encode(['status' => 1, 'message' => '绑定收款二维码成功']);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }

    }

    //解绑定空码
    public function unbindQr(Request $request)
    {

        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $code_number = $request->get('code_number', '');
            $QrListInfo = QrListInfo::where('code_number', $code_number)->first();
            if (!$QrListInfo) {
                return json_encode(['status' => 2, 'message' => '二维码不存在']);
            }

            if ($QrListInfo->code_type == 0) {
                return json_encode(['status' => 2, 'message' => '二维码未绑定任何门店']);
            }


            //开启事务
            try {
                DB::beginTransaction();

                //删除空码
                QrPayInfo::where('code_number', $code_number)
                    ->where('store_id', $store_id)
                    ->delete();

                $QrListInfo->update(
                    [
                        'code_type' => 0,
                        'store_id' => '',

                    ]
                );
                $QrListInfo->save();

                //已经使用加 1
                $QrList = QrList::where('cno', $QrListInfo->cno)->first();
                $s_num = $QrList->s_num;
                $QrList->s_num = $s_num - 1;
                $QrList->save();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }


            return json_encode(['status' => 1, 'message' => '收款二维码解绑成功']);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }

    }

    public function DownloadQr(Request $request)
    {
        try {
            $user = $this->parseToken();

            $cno = $request->get('cno');
            $list = QrList::where('cno', $cno)->first();
            if (!$list) {
                return json_encode(['status' => 2, 'message' => '没有任何相关数据']);
            }
            $zip = Zip::create($cno . '.zip');;
            $zip->add(public_path() . '/QrCode/' . $cno . '/', true);
            return json_encode(['status' => 1, 'data' => url('/' . $cno . '.zip')]);

        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }

    public function createQr(Request $request)
    {
        try {
            $user = $this->parseToken();
            //生成的批次
            $cno = date("Ymdhis", time()) . rand(1000, 9999);
            //生成数量
            $num = $request->get('num', 100);
            if ($num > 500) {
                return json_encode([
                    'status' => 2,
                    'message' => '生成数量控制在500以内'
                ]);
            }
            //
            try {
                QrList::create([
                    'cno' => $cno,
                    'user_id' => $user->user_id,
                    'num' => $num,
                    's_num' => 0,

                ]);
            } catch (\Exception $exception) {
                return json_encode([
                    'status' => 2,
                    'message' => '插入数据库表qrLsitsinfo失败'
                ]);
            }

            for ($i = 1; $i <= $num; $i++) {
                $code_number = 'NO_' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));//编号
                $url = url('/qr?no=' . $code_number);//生成的url准备生成二维码;
                try {
                    QrListInfo::create([
                        'user_id' => $user->user_id,
                        'code_number' => $code_number,
                        'code_type' => 0,//空码
                        'cno' => $cno
                    ]);
                } catch (\Exception $exception) {
                    return json_encode([
                        'status' => 2,
                        'message' => '插入数据库表PingancqrLsitsinfo失败'
                    ]);
                }
                try {
                    //生成二维码文件
                    if (!is_dir(public_path('QrCode/' . $cno . '/'))) {
                        mkdir(public_path('QrCode/' . $cno . '/'), 0777);
                    }
                    $renderer = new ImageRenderer(new RendererStyle(400),
                        new Image\ImagickImageBackEnd());

                    $writer = new Writer($renderer);
                    $writer->writeFile($url, public_path('QrCode/' . $cno . '/' . $code_number . '.png'));
                } catch (\Exception $exception) {
                    return json_encode([
                        'status' => 2,
                        'message' => $exception->getMessage()
                    ]);

                }
            }
            return json_encode([
                'status' => 1,
                'message' => '生成二维码成功'
            ]);

        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }

//个人码合并列表
    public function qr_code_hb_list(Request $request)
    {
        try {
            $user = $this->parseToken();
            $obj = DB::table('qr_code_hbs');
            $code_name = $request->get('code_name');
            $where = [];

            if ($code_name) {
                $where[] = ['code_name', 'like', '%' . $code_name . '%'];
            }

            if ($user->user_id) {
                $where[] = ['user_id', '=', $user->user_id];
            }

            $obj = $obj->where($where)->orderBy('created_at', 'desc');
            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }


    //个人码合并列表
    public function qr_code_hb_add(Request $request)
    {
        try {

            return json_encode([
                'status' => 2,
                'message' => '暂不对外开放'
            ]);

            $user = $this->parseToken();
            $code_name = $request->get('code_name');
            $ali_code_url = $request->get('ali_code_url');
            $wx_code_url = $request->get('wx_code_url');
            $user_id = $user->user_id;

            $ali = $request->get('ali_code_url');
            $wx = $request->get('wx_code_url');

            if (!$ali_code_url || !$wx_code_url || !$code_name) {
                return json_encode([
                    'status' => 1,
                    'message' => '信息填写完整'
                ]);
            }


            $ali_code_url = explode('/', $ali_code_url);
            $ali_code_url = end($ali_code_url);
            $ali_code_url = public_path() . '/upload/images/' . $ali_code_url;


            $wx_code_url = explode('/', $wx_code_url);
            $wx_code_url = end($wx_code_url);
            $wx_code_url = public_path() . '/upload/images/' . $wx_code_url;


            $a = new QrReader($ali_code_url);
            $a = $a->text(); //return decoded text from QR Code

            $b = new QrReader($wx_code_url);
            $b = $b->text(); //return decoded text from QR Code


            if (!$a) {
                return json_encode([
                    'status' => 1,
                    'message' => '支付宝二维码不清楚'
                ]);
            }

            if (!$b) {
                return json_encode([
                    'status' => 1,
                    'message' => '微信二维码不清楚'
                ]);
            }


            $data = [
                'code_name' => $code_name,
                'user_id' => $user_id,
                'ali_code_url' => $ali,
                'wx_code_url' => $wx,
                'ali_url' => $a,
                'wx_url' => $b,
                'hb_url' => url('/qr_code_hb'),
            ];

            QrCodeHb::create($data);

            return json_encode([
                'status' => 1,
                'message' => '二维码添加成功'
            ]);

        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }


    //个人码合并列表
    public function qr_code_hb_del(Request $request)
    {
        try {
            $user = $this->parseToken();
            $id = $request->get('id');

            QrCodeHb::where('id', $id)
                ->where('user_id', $user->user_id)
                ->delete();

            return json_encode([
                'status' => 1,
                'message' => '二维码删除成功'
            ]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }

}