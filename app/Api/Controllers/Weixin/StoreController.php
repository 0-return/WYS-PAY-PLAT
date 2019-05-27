<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/2/21
 * Time: 3:47 PM
 */

namespace App\Api\Controllers\Weixin;


class StoreController extends BaseController
{

    //获取平台证书
    public function getcertficates($request_url, $mch_id, $key)
    {
        try {
            //获取平台证书
            //公共配置
            $data = [
                "mch_id" => $mch_id,
                "sign_type" => 'HMAC-SHA256',
                "nonce_str" => '' . time() . '',
            ];

            $data['sign'] = $this->MakeSign($data, $key, 'HMAC-SHA256');
            $xml = $this->ToXml($data);
            $re_data = $this->postXmlCurl($data, $xml, $request_url, $useCert = false, $second = 30);
            $re_data = $this->xml_to_array($re_data);
            if ($re_data['return_code'] == "SUCCESS" && $re_data['result_code'] === "SUCCESS") {
                return [
                    'status' => 1,
                    'serial_no' => json_decode($re_data['certificates'], true)['data'][0]['serial_no'],
                    'data' => $re_data
                ];
            } else {
                return [
                    'status' => 2,
                    'message' => "获取平台证书" . $re_data['return_msg']
                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 2,
                'message' => "获取平台证书" . $exception->getMessage()
            ];
        }
    }


    //上传图片
    public function upload_img($request_url, $mch_id, $img, $key, $sslCertPath, $sslKeyPath)
    {
        try {

            //压缩图片
            $img_url = explode('/', $img);
            $img_url = end($img_url);
            $img = public_path() . '/upload/images/' . $img_url;
            if ($img) {
                try {
                    //压缩图片
                    $img_obj = \Intervention\Image\Facades\Image::make($img);
                    $img_obj->resize(500, 500);
                    $img = public_path() . '/upload/s_images/' . $img_url;
                    $img_obj->save($img);

                } catch (\Exception $exception) {
                    return [
                        'status' => 2,
                        'message' => "上传图片" . $exception->getMessage()
                    ];

                }
            }


            //上传到微信
            $data = [
                "media_hash" => md5_file($img),
                "mch_id" => $mch_id,
                "sign_type" => 'HMAC-SHA256',
            ];
            $data['sign'] = $this->MakeSign($data, $key, 'HMAC-SHA256');
            //不参与签名
            $data['sslCertPath'] = $sslCertPath;
            $data['sslKeyPath'] = $sslKeyPath;
            $data["media"] = new \CURLFile($img);
            $header = [
                "content-type:multipart/form-data",
            ];
            $re_data = $this->httpsRequest($request_url, $data, $header, true);

            $re_data = $this->xml_to_array($re_data);

            if ($re_data['return_code'] == "SUCCESS" && $re_data['result_code'] === "SUCCESS") {
                return [
                    'status' => 1,
                    'media_id' => $re_data['media_id'],
                    'data' => $re_data
                ];
            } else {
                return [
                    'status' => 2,
                    'message' => "上传图片" . $re_data['return_msg']
                ];
            }
        } catch (\Exception $exception) {
            return [
                'status' => 2,
                'message' => "上传图片" . $exception->getMessage()
            ];
        }
    }

    /**
     * setHashSign SHA256 with RSA 签名
     * @param $signContent
     * @return string
     */
    public function encryptSign($signContent, $privateKey)
    {
        // 解析 key 供其他函数使用。
        $privateKey = openssl_get_privatekey($privateKey);
        // 调用openssl内置签名方法，生成签名$sign
        openssl_sign($signContent, $sign, $privateKey, "SHA256");
        // 释放内存中私钥资源
        openssl_free_key($privateKey);
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * getCertificates  下载平台证书
     * @return mixed
     */
    public function downloadCertificates($mch_id, $serial_no, $privateKey)
    {
        try {
            $url = 'https://api.mch.weixin.qq.com/v3/certificates';
            // 请求随机串
            $nonce_str = time();
            // 当前时间戳
            $timestamp = time();
            // 签名串
            $signContent = "GET\n/v3/certificates\n" . $timestamp . "\n" . $nonce_str . "\n\n";
            // 签名值
            $signature = $this->encryptSign($signContent, $privateKey);
            // 含有服务器用于验证商户身份的凭证
            $authorization = 'WECHATPAY2-SHA256-RSA2048 mchid="' . $mch_id . '",nonce_str="' . $nonce_str . '",signature="' . $signature . '",timestamp="' . $timestamp . '",serial_no="' . $serial_no . '"';
            $curl_v = curl_version();
            $header = [
                'Accept:application/json',
                // 'Accept-Language:zh-CN',    // 默认 zh-CN 可以不填
                'Authorization:' . $authorization,
                'Content-Type:application/json',
                'User-Agent:curl/' . $curl_v['version'],
            ];
            $result = $this->httpsRequest($url, NULL, $header);
            dd($result);
            $responseHeader = $result[2];
            $http_code = $result[1];
            $responseBody = json_decode($result[0], true);
            if ($http_code == 200 && !isset($responseBody['code'])) {
                return $this->verifySign($responseHeader, $result[0]);
            } else {
                throw new \Exception($responseBody['code'] . '----' . $responseBody['message']);
            }
        } catch (\Exception $e) {
            dd($e);
        }
    }


    //小微进件
    public function xw_store($data, $img_data)
    {
        try {

            $mch_id = $data['mch_id'];
            $key = $data['key'];
            $getcertficates_request_url = $data['getcertficates_request_url'];
            $upload_img_request_url = $data['upload_img_request_url'];
            $submit_request_url = $data['submit_request_url'];
            $sslCertPath = $data['sslCertPath'];
            $sslKeyPath = $data['sslKeyPath'];
            $public_key_path = $data['public_key_path'];

            //获取证书
            $getcertficates = $this->getcertficates($getcertficates_request_url, $mch_id, $key);
            if ($getcertficates['status'] == 2) {
                return $getcertficates;
            }

            $encryptCertificate = json_decode($getcertficates['data']['certificates'], true)['data'][0]['encrypt_certificate'];
            $ciphertext = base64_decode($encryptCertificate['ciphertext']);
            $associated_data = $encryptCertificate['associated_data'];
            $nonce = $encryptCertificate['nonce'];

            $check_sodium_mod = extension_loaded('sodium');
            if ($check_sodium_mod === false) {
                return [
                    'status' => 2,
                    'message' => "没有安装sodium模块",
                ];
            }

            // sodium_crypto_aead_aes256gcm_decrypt >=7.2版本，去php.ini里面开启下libsodium扩展就可以，之前版本需要安装libsodium扩展，具体查看php.net（ps.使用这个函数对扩展的版本也有要求哦，扩展版本 >=1.08）
            $plaintext = sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce, $key);

            $public_key_path = $plaintext;


            //上传图片
            //1.身份证正面

            $img = $img_data['head_sfz_img_a'];

            $head_sfz_img_a = $this->upload_img($upload_img_request_url, $mch_id, $img, $key, $sslCertPath, $sslKeyPath);
            if ($head_sfz_img_a['status'] == 2) {
                return [
                    'status' => 2,
                    'message' => '身份证正面' . $head_sfz_img_a['message']
                ];
            }

            //2.身份证反面
            $img = $img_data['head_sfz_img_b'];
            $head_sfz_img_b = $this->upload_img($upload_img_request_url, $mch_id, $img, $key, $sslCertPath, $sslKeyPath);
            if ($head_sfz_img_b['status'] == 2) {
                return [
                    'status' => 2,
                    'message' => '身份证反面' . $head_sfz_img_b['message']
                ];
            }

            //3.门头
            $img = $img_data['store_logo_img'];
            $store_logo_img = $this->upload_img($upload_img_request_url, $mch_id, $img, $key, $sslCertPath, $sslKeyPath);
            if ($store_logo_img['status'] == 2) {
                return [
                    'status' => 2,
                    'message' => '门头照片' . $store_logo_img['message']
                ];
            }

            //4.店内场景
            $img = $img_data['store_img_b'];
            $store_img_b = $this->upload_img($upload_img_request_url, $mch_id, $img, $key, $sslCertPath, $sslKeyPath);
            if ($store_img_b['status'] == 2) {
                return [
                    'status' => 2,
                    'message' => '店内场景' . $store_img_b['message']
                ];
            }

            $data = [
                "version" => "3.0",
                "cert_sn" => $getcertficates['serial_no'],
                "mch_id" => $mch_id,
                "nonce_str" => '' . time() . '',
                "sign_type" => 'HMAC-SHA256',
                "business_code" => time() . rand(10000, 999999),//业务编号
                "id_card_name" => $this->getEncrypt($data['head_name'], $public_key_path),//身份证姓名
                "id_card_number" => $this->getEncrypt($data['head_sfz_no'], $public_key_path),//身份证号码
                "id_card_valid_time" => '["2012-02-07","2022-02-07"]',//身份证有效期
                "account_name" => $this->getEncrypt($data['store_bank_name'], $public_key_path),//银行卡户名
                "account_bank" => $data['bank_name'],//开户银行
                "bank_address_code" => $data['bank_city_code'],//开户银行省市编码
                "bank_name" => $data['sub_bank_name'],//开户银行全称（含支行）
                "account_number" => $this->getEncrypt($data['store_bank_no'], $public_key_path),//银行账号
                "store_name" => $data['store_name'],//门店名称
                "store_address_code" => $data['city_code'],//门店省市编码
                "store_street" => $data['store_address'],//门店街道名称
                "merchant_shortname" => $data['store_short_name'],//商户简称
                "service_phone" => $data['people_phone'],//客服电话
                "product_desc" => $data['product_desc'],//售卖商品/提供服务描述
                "rate" => '0.6%',//费率
                "contact" => $this->getEncrypt($data['people'], $public_key_path),//联系人姓名
                "contact_phone" => $this->getEncrypt($data['people_phone'], $public_key_path),//电话
                "id_card_copy" => $head_sfz_img_a['media_id'],//身份证人像面照片
                "id_card_national" => $head_sfz_img_b['media_id'],//身份证国徽面照片
                "store_entrance_pic" => $store_logo_img['media_id'],//门店门口照片
                "indoor_pic" => $store_img_b['media_id'],//店内环境照片
            ];

            $data['sign'] = $this->MakeSign($data, $key, 'HMAC-SHA256');
            $xml = $this->ToXml($data);
            $data['sslCertPath'] = $sslCertPath;
            $data['sslKeyPath'] = $sslKeyPath;

            $re_data = $this->postXmlCurl($data, $xml, $submit_request_url, $useCert = true, $second = 30);
            $return = $this->xml_to_array($re_data);

            //返回状态码
            if ($return['return_code'] == "FAIL") {
                return [
                    'status' => 2,
                    'message' => $return['return_msg'],
                ];
            }

            if ($return['result_code'] == "FAIL") {
                return [
                    'status' => 2,
                    'message' => $return['err_code_des'],
                ];
            }

            return [
                'status' => 1,
                'data' => $return,
                'message' => '成功',
            ];

        } catch (\Exception $exception) {
            return [
                'status' => 2,
                'message' => $exception->getMessage()
            ];
        }

    }

    //升级进件
    public function qy_store($data, $img_data)
    {
        try {

            $mch_id = $data['mch_id'];
            $key = $data['key'];
            $getcertficates_request_url = $data['getcertficates_request_url'];
            $upload_img_request_url = $data['upload_img_request_url'];
            $submit_request_url = $data['submit_request_url'];
            $sslCertPath = $data['sslCertPath'];
            $sslKeyPath = $data['sslKeyPath'];
            $sub_mch_id = $data['sub_mch_id'];
            //获取证书
            $getcertficates = $this->getcertficates($getcertficates_request_url, $mch_id, $key);
            if ($getcertficates['status'] == 2) {
                return $getcertficates;
            }

            $encryptCertificate = json_decode($getcertficates['data']['certificates'], true)['data'][0]['encrypt_certificate'];
            $ciphertext = base64_decode($encryptCertificate['ciphertext']);
            $associated_data = $encryptCertificate['associated_data'];
            $nonce = $encryptCertificate['nonce'];

            $check_sodium_mod = extension_loaded('sodium');
            if ($check_sodium_mod === false) {
                return [
                    'status' => 2,
                    'message' => "没有安装sodium模块",
                ];
            }

            // sodium_crypto_aead_aes256gcm_decrypt >=7.2版本，去php.ini里面开启下libsodium扩展就可以，之前版本需要安装libsodium扩展，具体查看php.net（ps.使用这个函数对扩展的版本也有要求哦，扩展版本 >=1.08）
            $plaintext = sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce, $key);

            $public_key_path = $plaintext;


            //上传图片
            //1.营业执照

            $img = $img_data['store_license_img'];

            $store_license_img = $this->upload_img($upload_img_request_url, $mch_id, $img, $key, $sslCertPath, $sslKeyPath);
            if ($store_license_img['status'] == 2) {
                return [
                    'status' => 2,
                    'message' => '营业执照' . $store_license_img['message']
                ];
            }


            $data = [
                "version" => "1.0",
                "cert_sn" => $getcertficates['serial_no'],
                "mch_id" => $mch_id,
                "sub_mch_id" => $sub_mch_id,
                "nonce_str" => '' . time() . '',
                "sign_type" => 'HMAC-SHA256',
                "organization_type" => $data['store_type'],//2-企业   4-个体工商户   3-政府及事业单位  1708-其他组织
                "business_license_copy" => $store_license_img['media_id'],//营业执照
                "business_license_number" => $data['store_license_no'],
                "merchant_name" => $data['store_name'],
                "company_address" => $data['store_address'],
                "legal_person" => $this->getEncrypt($data['head_name'], $public_key_path),//身份证姓名
                "business_time" => '[""' . $data['store_license_stime'] . '"","' . $data['store_license_time'] ? $data['store_license_time'] : "长期" . '"]',
                "business_licence_type" => "1762",
                "merchant_shortname" => $data['store_short_name'],
                "business" => $data['business'],
                "business_scene" => "1721",
            ];

            $data['sign'] = $this->MakeSign($data, $key, 'HMAC-SHA256');
            $xml = $this->ToXml($data);
            $data['sslCertPath'] = $sslCertPath;
            $data['sslKeyPath'] = $sslKeyPath;

            $re_data = $this->postXmlCurl($data, $xml, $submit_request_url, $useCert = true, $second = 30);
            $return = $this->xml_to_array($re_data);

            //返回状态码
            if ($return['return_code'] == "FAIL") {
                return [
                    'status' => 2,
                    'message' => $return['return_msg'],
                ];
            }

            if ($return['result_code'] == "FAIL") {
                return [
                    'status' => 2,
                    'message' => $return['err_code_des'],
                ];
            }

            return [
                'status' => 1,
                'data' => $return,
                'message' => "成功"
            ];

        } catch (\Exception $exception) {
            return [
                'status' => 2,
                'message' => $exception->getMessage()
            ];
        }

    }

}