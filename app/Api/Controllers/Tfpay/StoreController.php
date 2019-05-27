<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/4/27
 * Time: 9:36 PM
 */

namespace App\Api\Controllers\Tfpay;


class StoreController extends BaseController
{
    //进件
    public function open_store($data)
    {
        try {


            $method = '/openapi/picture/upload'; //方法名
            $file = public_path().'/123.png'; //文件本地路径
            $post_data['file'] = curl_file_create($file, 'image/png', 'file');

            $result = $this->api($post_data, $method, true);
            dd($result);


            $method = '/openapi/merchant/register';
            $bankcard = [
                'type' => '2',
                'bank_code' => 'ABC',
                'account_name' => '有梦想科技',
                'account_number' => '有梦想科技',
                'bank_province_code' => '100',
                'bank_city_code' => '100',
                'branch_name' => '中国银行金沙湖支行',
            ];
            $file_pics = [
                'idcard_front_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
                'idcard_back_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
                'license_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
                'bankcard_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
                'door_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            ];
            $post_data = [
                'organization_type' => 3,
                'name' => '传化金服',
                'shortname' => '传化',
                'mcc_code' => '1001',
                'sub_mcc_code' => '4214',
                'contact' => '张三',
                'contact_type' => 'LEGAL_PERSON',
                'contact_business_type' => '02',
                'service_phone' => '13656814630',
                'id_card_number' => '412828199911020116',
                'license_type' => 'NATIONAL_LEGAL',
                'license_number' => 'qa4567896789jkghjkl',
                'province_code' => '100',
                'city_code' => '100',
                'district_code' => '100',
                'address' => '这是我的地址',
                'bankcard' => json_encode($bankcard),
                'file_pics' => json_encode($file_pics),
                'notify_url' => 'www.baidu.com'
            ];

            $re = $this->api($post_data, $method, true);
            dd($re);

            //成功
            if ($re['ret_code'] == "0000") {
                return [
                    'status' => 1,
                    'message' => '进件成功',
                    'data' => $re,
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => $re['ret_msg'],
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }


    }


}