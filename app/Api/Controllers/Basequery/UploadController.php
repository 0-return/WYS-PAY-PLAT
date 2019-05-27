<?php
/**
 * Created by PhpStorm.
 * User: cc
 * Date: 2017/3/21
 * Time: 15:48
 */

namespace App\Api\Controllers\Basequery;

use App\Api\Controllers\BaseController;
use App\Models\MerchantStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Image;
use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;

class UploadController extends BaseController
{


    public function upload(Request $request, $file_key = 'attach_name', $type = 'img')
    {
        try {
            $token = $request->get('token');
            $merchant = $this->parseToken($token);
            $img_type = $request->get('img_type', '');
            $file_key = trim($request->get('attach_name')) ? trim($request->get('attach_name')) : $file_key;
            $type = $request->get('type', 'img');

            $up = Input::hasFile($file_key);//检测是否有上传文件  返回布尔值
            if (!$up) {
                return response()->json([
                    'status' => 2,
                    'message' => '未识别到文件！'
                ]);
            }


            $public_dir = public_path();

            $disk_dir = $public_dir;
            $web_dir = 'upload';
            if (!is_dir($public_dir . '/' . $web_dir)) {
                return response()->json([
                    'status' => 2,
                    'message' => '服务器初始目录未设置！'
                ]);
            }

            // 上传图片
            if ($type == 'img') {
                $web_dir .= '/images';
                $disk_dir = $public_dir . '/' . $web_dir;
            }

            // 上传商品图片
            if ($type == 'shop') {
                $web_dir .= '/images/shop/' . $merchant->id;
                $disk_dir = $public_dir . '/' . $web_dir;
            }

            // 上传附件
            if ($type == 'file') {
                $web_dir .= '/attach/' . date('Y/m/d');
                $disk_dir = $public_dir . '/' . $web_dir;
            }

            $ok = true;
            !is_dir($disk_dir) && $ok = mkdir($disk_dir, 0777, true);
            if ($ok === false) {
                return response()->json([
                    'status' => 2,
                    'message' => '服务器权限不够'
                ]);

            }

            $file = Input::file($file_key);
            $extension = $file->getClientOriginalExtension(); //上传文件的后缀   png

            $new_name = date('His') . '_' . mt_rand(100, 999) . '.' . $extension;

            $web_pic_url = url($web_dir . '/' . $new_name);
            $return = $file->move($disk_dir, $new_name);

            if (file_exists($disk_dir . '/' . $new_name)) {
                //上传到阿里云oss
                if (env('ALIOSS_AccessKeyId')) {
                    //阿里云oss
                    $AccessKeyId = env('ALIOSS_AccessKeyId');
                    $AccessKeySecret = env('ALIOSS_AccessKeySecret');
                    $endpoint = env('ALIOSS_endpoint');
                    $bucket = env('ALIOSS_bucket');
                    $object = $new_name;
                    try {
                        $content = file_get_contents($disk_dir . '/' . $new_name);
                        $ossClient = new \OSS\OssClient($AccessKeyId, $AccessKeySecret, $endpoint);
                        $data = $ossClient->putObject($bucket, $object, $content);
                        $web_pic_url = $data['oss-request-url'];
                        //删除本地图片
                    } catch (\OSS\Core\OssException $e) {

                    }
                }


                //得到返回图片
                $data_return = [
                    'img_url' => $web_pic_url,
                ];
                //识别营业执照
                $is_yyzz = 1;
                if ($is_yyzz && $img_type == '1') {
                    $data_return['store_license_no'] = '';
                    $data_return['store_license_stime'] = '';
                    $data_return['store_license_time'] = '';
                    $data_return['is_long_time'] = '0';

                    try {
                        // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                        $appid = env('YOUTU_appid');
                        $secretId = env('YOUTU_secretId');
                        $secretKey = env('YOUTU_secretKey');
                        $userid = env('YOUTU_userid');
                        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                        $uploadRet = YouTu::bizlicenseocrurl($web_pic_url);
                        Log::info($uploadRet);

                        if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {

                            foreach ($uploadRet['items'] as $k => $v) {
                                if (isset($v['item'])) {
                                    if ($v['item'] == '注册号' || $v['item'] == "营业期限") {
                                        if ($v['item'] == '注册号') {
                                            //营业执照编号
                                            $data_return['store_license_no'] = $v['itemstring'];
                                        }
                                        if ($v['item'] == '营业期限') {
                                            $ex = explode('至', $v['itemstring']);
                                            if (isset($ex[1]) && strlen($ex[1]) > 5) {


                                                $store_license_stime = $ex[0];
                                                $store_license_stime = str_replace("年", "-", $store_license_stime);
                                                $store_license_stime = str_replace("月", "-", $store_license_stime);
                                                $store_license_stime = str_replace("日", "", $store_license_stime);


                                                $data_return['store_license_stime'] = $store_license_stime;


                                                $store_license_time = $ex[1];
                                                $store_license_time = str_replace("年", "-", $store_license_time);
                                                $store_license_time = str_replace("月", "-", $store_license_time);
                                                $store_license_time = str_replace("日", "", $store_license_time);

                                                $data_return['store_license_time'] = $store_license_time;


                                                if ($store_license_time == "长期") {
                                                    $data_return['is_long_time'] = '1';
                                                }
                                            }
                                        }

                                    } else {
                                        continue;
                                    }
                                } else {
                                    break;
                                }
                            }

                        }
                    } catch
                    (\Exception $exception) {

                    }
                }


                //识别身份证正面
                $is_card_a = 1;
                if ($is_card_a && $img_type == '2') {
                    $data_return['sfz_name'] = '';
                    $data_return['sfz_no'] = '';
                    try {
                        // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                        $appid = env('YOUTU_appid');
                        $secretId = env('YOUTU_secretId');
                        $secretKey = env('YOUTU_secretKey');
                        $userid = env('YOUTU_userid');
                        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                        $uploadRet = YouTu::idcardocrurl($web_pic_url, 0);

                        if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                            if (isset($uploadRet['name'])) {
                                //身份证名字
                                $data_return['sfz_name'] = $uploadRet['name'];
                            }
                            if (isset($uploadRet['id'])) {
                                //身份证号码
                                $data_return['sfz_no'] = $uploadRet['id'];
                            }

                        }
                    } catch (\Exception $exception) {

                    }
                }

                //识别身份证反面
                $is_card_b = 1;
                if ($is_card_b && $img_type == '3') {
                    $data_return['sfz_stime'] = '';
                    $data_return['sfz_time'] = '';
                    try {
                        // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                        $appid = env('YOUTU_appid');
                        $secretId = env('YOUTU_secretId');
                        $secretKey = env('YOUTU_secretKey');
                        $userid = env('YOUTU_userid');
                        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                        $uploadRet = YouTu::idcardocrurl($web_pic_url, 1);


                        if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                            if (isset($uploadRet['valid_date'])) {
                                $valid_date = explode("-", $uploadRet['valid_date']);
                                if (isset($valid_date[0])) {
                                    $data_return['sfz_stime'] = $valid_date[0];
                                    $data_return['sfz_stime'] = str_replace(".", "-", $data_return['sfz_stime']);

                                }

                                if (isset($valid_date[1])) {
                                    $data_return['sfz_time'] = $valid_date[1];
                                    $data_return['sfz_time'] = str_replace(".", "-", $data_return['sfz_time']);

                                }
                            }
                        }

                    } catch (\Exception $exception) {

                    }
                }


                //识别银行卡正面
                $is_bank_a = 1;
                if ($is_bank_a && $img_type == '4') {
                    $data_return['store_bank_no'] = '';
                    $data_return['bank_name'] = '';

                    try {
                        // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                        $appid = env('YOUTU_appid');
                        $secretId = env('YOUTU_secretId');
                        $secretKey = env('YOUTU_secretKey');
                        $userid = env('YOUTU_userid');
                        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                        $uploadRet = YouTu::creditcardocrurl($url, 1);


                        if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                            if (isset($uploadRet['items'])) {
                                foreach ($uploadRet['items'] as $k => $v) {
                                    if (isset($v['item'])) {
                                        if ($v['item'] == '卡号') {
                                            //卡号
                                            $data_return['store_bank_no'] = $v['itemstring'];
                                        }

                                        if ($v['item'] == '卡类型') {
                                            //卡类型
                                            if ($v['itemstring'] == "贷记卡") {
                                                return json_encode([
                                                    'status' => 2,
                                                    'message' => '暂时不支持信用卡绑卡',
                                                ]);
                                            }
                                        }

                                        if ($v['item'] == '银行信息') {
                                            //卡号
                                            $data = explode('(', $v['itemstring']);
                                            $data_return['bank_name'] = isset($data[0]) ? $data[0] : "";
                                        }


                                    } else {
                                        break;
                                    }
                                }
                            }
                        }
                    } catch (\Exception $exception) {

                    }
                }

                return response()->json([
                    'status' => 1,
                    'message' => '上传成功！',
                    'data' => $data_return,
                ]);
            }


            return response()->json([
                'status' => 2,
                'message' => '上传失败！'
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => -1,
                'message' => $e->getLine()]);

        }

    }

//服务端上传图片
    public
    function webupload(Request $request)
    {

        try {
            $action = $request->get('act'); // 获取GET参数
            $type = $request->get('type', 'store');
            $img_type = $request->get('img_type', '');
            //上传图片具体操作
            $file_name = $_FILES['img_upload']['name'];
            $file_type = $_FILES["img_upload"]["type"];
            $file_tmp = $_FILES["img_upload"]["tmp_name"];
            $file_error = $_FILES["img_upload"]["error"];
            $file_size = $_FILES["img_upload"]["size"];
            if ($file_error > 0) { // 出错
                return json_encode([
                    'status' => 2,
                    'message' => $file_error,
                ]);
            }
            if ($file_size > 50485769999) { // 文件太大了
                return json_encode([
                    'status' => 2,
                    'message' => '上传文件过大',
                ]);
            }


            $file_name_arr = explode('.', $file_name);
            //域名授权目录提交
            $file_first = time() . rand(1000, 9999);
            if ($type == 'wxauth') {
                $file_first = $file_name_arr[0];
            }
            $new_file_name = $file_first . '.' . $file_name_arr[1];
            $file_path = "/upload/images/" . $new_file_name;
            if ($type == 'wxfile') {
                $file_path = "/upload/images/" . $new_file_name;
            }
            //域名授权目录提交
            if ($type == 'wxauth') {
                $file_path = "/" . $new_file_name;
            }

            if ($type == "store") {
                $file_path = "/upload/images/" . $new_file_name;
            }

            if (file_exists($file_path)) {
                return json_encode([
                    'status' => 2,
                    'message' => '文件已存在',
                ]);
            } else {
                $upload_result = move_uploaded_file($file_tmp, public_path() . $file_path); // 此函数只支持 HTTP POST 上传的文件
                if ($upload_result) {
                    $url = url($file_path);
                    if ($type == 'wxfile') {
                        $url = $file_path;
                    }
                    //上传到阿里云oss
                    $oss = 0;
                    if ($oss&&env('ALIOSS_AccessKeyId') && $type == "store") {
                        //阿里云oss
                        //阿里云oss
                        $AccessKeyId = env('ALIOSS_AccessKeyId');
                        $AccessKeySecret = env('ALIOSS_AccessKeySecret');
                        $endpoint = env('ALIOSS_endpoint');
                        $bucket = env('ALIOSS_bucket');

                        $object = $new_file_name;
                        try {
                            $content = file_get_contents(public_path() . $file_path);
                            $ossClient = new \OSS\OssClient($AccessKeyId, $AccessKeySecret, $endpoint);
                            $data = $ossClient->putObject($bucket, $object, $content);

                            $url = $data['oss-request-url'];
                            //删除本地图片


                        } catch (\OSS\Core\OssException $e) {

                        }
                    }


                    //识别营业执照
                    $is_yyzz = 1;
                    if ($is_yyzz && $img_type == '1') {
                        $data_return['store_license_no'] = '';
                        $data_return['store_license_time'] = '';
                        $data_return['store_license_stime'] = '';
                        $data_return['is_long_time'] = '0';

                        try {
                            // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                            $appid = env('YOUTU_appid');
                            $secretId = env('YOUTU_secretId');
                            $secretKey = env('YOUTU_secretKey');
                            $userid = env('YOUTU_userid');
                            Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                            $uploadRet = YouTu::bizlicenseocrurl($url);

                            if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {

                                foreach ($uploadRet['items'] as $k => $v) {
                                    if (isset($v['item'])) {
                                        if ($v['item'] == '注册号' || $v['item'] == "营业期限") {
                                            if ($v['item'] == '注册号') {
                                                //营业执照编号
                                                $data_return['store_license_no'] = $v['itemstring'];
                                            }
                                            if ($v['item'] == '营业期限') {
                                                $ex = explode('至', $v['itemstring']);
                                                if (isset($ex[1]) && strlen($ex[1]) > 5) {

                                                    $store_license_stime = $ex[0];
                                                    $store_license_stime = str_replace("年", "-", $store_license_stime);
                                                    $store_license_stime = str_replace("月", "-", $store_license_stime);
                                                    $store_license_stime = str_replace("日", "", $store_license_stime);


                                                    $data_return['store_license_stime'] = $store_license_stime;


                                                    $store_license_time = $ex[1];
                                                    $store_license_time = str_replace("年", "-", $store_license_time);
                                                    $store_license_time = str_replace("月", "-", $store_license_time);
                                                    $store_license_time = str_replace("日", "", $store_license_time);

                                                    $data_return['store_license_time'] = $store_license_time;


                                                    if ($store_license_time == "长期") {
                                                        $data_return['is_long_time'] = '1';
                                                    }
                                                }
                                            }

                                        } else {
                                            continue;
                                        }
                                    } else {
                                        break;
                                    }
                                }

                            }
                        } catch
                        (\Exception $exception) {

                        }
                    }


                    //识别身份证正面
                    $is_card_a = 1;
                    if ($is_card_a && $img_type == '2') {
                        $data_return['sfz_name'] = '';
                        $data_return['sfz_no'] = '';
                        try {
                            // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                            $appid = env('YOUTU_appid');
                            $secretId = env('YOUTU_secretId');
                            $secretKey = env('YOUTU_secretKey');
                            $userid = env('YOUTU_userid');
                            Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                            $uploadRet = YouTu::idcardocrurl($url, 0);
                            if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                                if (isset($uploadRet['name'])) {
                                    //身份证名字
                                    $data_return['sfz_name'] = $uploadRet['name'];
                                }
                                if (isset($uploadRet['id'])) {
                                    //身份证号码
                                    $data_return['sfz_no'] = $uploadRet['id'];
                                }

                            }
                        } catch (\Exception $exception) {

                        }
                    }

                    //识别身份证反面
                    $is_card_b = 1;
                    if ($is_card_b && $img_type == '3') {
                        $data_return['sfz_time'] = '';
                        $data_return['sfz_stime'] = '';

                        try {
                            // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                            $appid = env('YOUTU_appid');
                            $secretId = env('YOUTU_secretId');
                            $secretKey = env('YOUTU_secretKey');
                            $userid = env('YOUTU_userid');
                            Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                            $uploadRet = YouTu::idcardocrurl($url, 1);


                            if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                                if (isset($uploadRet['valid_date'])) {
                                    $valid_date = explode("-", $uploadRet['valid_date']);

                                    if (isset($valid_date[0])) {
                                        $data_return['sfz_stime'] = $valid_date[0];
                                        $data_return['sfz_stime'] = str_replace(".", "-", $data_return['sfz_stime']);

                                    }

                                    if (isset($valid_date[1])) {
                                        $data_return['sfz_time'] = $valid_date[1];
                                        $data_return['sfz_time'] = str_replace(".", "-", $data_return['sfz_time']);

                                    }

                                }
                            }

                        } catch (\Exception $exception) {

                        }
                    }


                    //识别银行卡正面
                    $is_bank_a = 1;
                    if ($is_bank_a && $img_type == '4') {
                        $data_return['store_bank_no'] = '';
                        $data_return['bank_name'] = '';

                        try {
                            // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                            $appid = env('YOUTU_appid');
                            $secretId = env('YOUTU_secretId');
                            $secretKey = env('YOUTU_secretKey');
                            $userid = env('YOUTU_userid');
                            Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                            $uploadRet = YouTu::creditcardocrurl($url, 1);


                            if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                                if (isset($uploadRet['items'])) {
                                    foreach ($uploadRet['items'] as $k => $v) {
                                        if (isset($v['item'])) {
                                            if ($v['item'] == '卡号') {
                                                //卡号
                                                $data_return['store_bank_no'] = $v['itemstring'];
                                            }

                                            if ($v['item'] == '卡类型') {
                                                //卡类型
                                                if ($v['itemstring'] == "贷记卡") {
                                                    return json_encode([
                                                        'status' => 2,
                                                        'message' => '暂时不支持信用卡绑卡',
                                                    ]);
                                                }
                                            }

                                            if ($v['item'] == '银行信息') {
                                                //卡号
                                                $data = explode('(', $v['itemstring']);
                                                $data_return['bank_name'] = isset($data[0]) ? $data[0] : "";
                                            }


                                        } else {
                                            break;
                                        }
                                    }
                                }
                            }
                        } catch (\Exception $exception) {

                        }
                    }


                    $data_return['img_url'] = $url;

                    return json_encode([
                        'status' => 1,
                        'data' => $data_return,
                    ]);
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '系统错误重新上传',
                    ]);
                }
            }

        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getLine(),
            ]);
        }

    }

}