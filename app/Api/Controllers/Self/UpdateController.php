<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/13
 * Time: 下午7:12
 */

namespace App\Api\Controllers\Self;


use App\Models\SelfCategory;
use App\Models\SelfShop;
use App\Models\SelfStore;
use Illuminate\Http\Request;
use Monolog\Handler\IFTTTHandler;

class UpdateController extends BaseController
{


    //手动同步分类-会删除之前的分类
    public function updateCategory(Request $request)
    {

        try {
            $store_id = $request->get('store_id', '');
            $store = SelfStore::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店不存在']);

            }

            $erp_info = $store->erp_info;
            $erp_arr = json_decode($erp_info, true);
            if (!isset($erp_arr['erp_type'])) {
                return json_encode([
                    'status' => 2,
                    'message' => 'erp_type不正确',
                ]);
            }


            //银豹收银系统更新
            if ($erp_arr['erp_type'] == "pospal") {
                $appID = $erp_arr['appID'];
                $appKey = $erp_arr['appKey'];

                $aop = new PospalApiClientController();
                $url = $aop->category_api_url;
                $sendData = [
                    'appId' => $appID,
                ];
                $re = $aop->doApiRequest($url, $sendData, $appKey);

                $re_arr = json_decode($re, true);

                if ($re_arr['status'] == "success") {
                    //先清空旧数据
                    SelfCategory::where('store_id', $store_id)
                        ->where('erp_type', $erp_arr['erp_type'])
                        ->delete();

                    //$re_arr['data']['result'];
                    foreach ($re_arr['data']['result'] as $k => $v) {
                        // $v['uid'] $v['parentUid'] $v['name']
                        $insert_data = [
                            'pid' => $v['parentUid'],
                            'erp_type' => $erp_arr['erp_type'],
                            'store_id' => $store_id,
                            'category_id' => $v['uid'],
                            'category_name' => $v['name'],
                            'ext' => json_encode($re_arr['data']['postBackParameter']),
                        ];
                        SelfCategory::create($insert_data);
                    }


                    return json_encode([
                        'status' => 1,
                        'message' => '更新成功',
                    ]);


                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $re_arr['messages'][0],
                    ]);
                }
            }


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

    //手动同步商品
    public function updateProduct(Request $request)
    {

        try {
            $store_id = $request->get('store_id', '');
            $store = SelfStore::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店不存在']);

            }

            $erp_info = $store->erp_info;
            $erp_arr = json_decode($erp_info, true);
            if (!isset($erp_arr['erp_type'])) {
                return json_encode([
                    'status' => 2,
                    'message' => 'erp_type不正确',
                ]);
            }


            //银豹收银系统更新
            if ($erp_arr['erp_type'] == "pospal") {
                $appID = $erp_arr['appID'];
                $appKey = $erp_arr['appKey'];

                $aop = new PospalApiClientController();
                $url = 'https://area23-win.pospal.cn:443//pospal-api2/openapi/v1/productOpenApi/queryProductPages';
                $sendData = [
                    'appId' => $appID,
                ];
                $re = $aop->doApiRequest($url, $sendData, $appKey);
                $re_arr = json_decode($re, true);
                if ($re_arr['status'] == "success") {
                    foreach ($re_arr['data']['result'] as $k => $v) {
                       $time= $v['uid'].time();
                        $insert_data = [
                            'store_id' => $store_id,
                            'product_id' => $v['uid'],
                            'title' => $v['name'],
                            'spec_id' => $time,
                            'spec_desc' => "",
                            'img_url' => '',
                            'description' => $v['description'],
                            'category_id' => $v['categoryUid'],
                            'product_code' => $v['barcode'],
                            'buy_price' => $v['buyPrice'] * 100,
                            'sell_price' => $v['sellPrice'] * 100,
                            'customer_price' => $v['customerPrice'] * 100,
                            'stock' => $v['stock'],
                            'is_cd' => $v['isCustomerDiscount'],
                            'sku_list' => json_encode([[
                                'store_id' => $store_id,
                                'product_id' => $v['uid'],
                                'title' => $v['name'],
                                'spec_id' => $time,
                                'spec_desc' => "",
                                'img_url' => '',
                                'description' => $v['description'],
                                'category_id' => $v['categoryUid'],
                                'product_code' => $v['barcode'],
                                'buy_price' => $v['buyPrice'] * 100,
                                'sell_price' => $v['sellPrice'] * 100,
                                'customer_price' => $v['customerPrice'] * 100,
                                'stock' => $v['stock'],
                                'is_cd' => $v['isCustomerDiscount'],
                                'supplier' => $v['supplierUid'],
                                'status' => $v['enable'],
                                's_date' => $v['productionDate'],
                                'e_date' => '',
                                'e_day' => '',
                                'weight' => '',
                                'qc' => '',
                                'discount' => json_encode([
                                    [
                                        's' => '2000',
                                        'e' => '10000',
                                        'r' => '500'
                                    ],
                                    [
                                        's' => '10000',
                                        'e' => '2000000000',
                                        'r' => '1000'
                                    ],

                                ])
                            ],]),
                            'supplier' => $v['supplierUid'],
                            'status' => $v['enable'],
                            's_date' => $v['productionDate'],
                            'e_date' => '',
                            'e_day' => '',
                            'weight' => '',
                            'qc' => '',
                            'discount' => json_encode([
                                [
                                    's' => '2000',
                                    'e' => '10000',
                                    'r' => '500'
                                ],
                                [
                                    's' => '10000',
                                    'e' => '2000000000',
                                    'r' => '1000'
                                ],

                            ]),
                        ];
                        SelfShop::create($insert_data);
                    }


                    return json_encode([
                        'status' => 1,
                        'message' => '更新成功',
                    ]);


                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $re_arr['messages'][0],
                    ]);
                }
            }


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

    //手动同步商品图片
    public function updateProductImage(Request $request)
    {

        try {
            $store_id = $request->get('store_id', '');
            $store = SelfStore::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店不存在']);

            }

            $erp_info = $store->erp_info;
            $erp_arr = json_decode($erp_info, true);
            if (!isset($erp_arr['erp_type'])) {
                return json_encode([
                    'status' => 2,
                    'message' => 'erp_type不正确',
                ]);
            }


            //银豹收银系统更新
            if ($erp_arr['erp_type'] == "pospal") {
                $appID = $erp_arr['appID'];
                $appKey = $erp_arr['appKey'];

                $aop = new PospalApiClientController();
                $url = 'https://area23-win.pospal.cn:443//pospal-api2/openapi/v1/productOpenApi/queryProductImagePages';
                $sendData = [
                    'appId' => $appID,
                ];
                $re = $aop->doApiRequest($url, $sendData, $appKey);
                $re_arr = json_decode($re, true);
                if ($re_arr['status'] == "success") {
                    foreach ($re_arr['data']['result'] as $k => $v) {
                        $s = SelfShop::where('store_id', $store_id)
                            ->where('product_id', $v['productUid'])
                            ->first();
                        if (!$s) {
                            continue;
                        }
                        $img_url = $v['imageUrl'];
                        if (!isset($img_url)) {
                            continue;
                        }
                        //上传到阿里云oss
                        $oss = 1;
                        if ($oss) {
                            //阿里云oss
                            $AccessKeyId = "LTAIO0UpPBX6aao4";
                            $AccessKeySecret = "3N9ttMilrbNC9EpuT9MZ8I4meI6bvo";
                            $endpoint = "oss-cn-beijing.aliyuncs.com";
                            $bucket = 'xiangyongimg';
                            $img_type = trim(strrchr($img_url, '.'), '.');
                            $object = $store_id . time() . '.' . $img_type;
                            try {
                                $content = file_get_contents($img_url);
                                $ossClient = new \OSS\OssClient($AccessKeyId, $AccessKeySecret, $endpoint);
                                $data = $ossClient->putObject($bucket, $object, $content);
                                $web_pic_url = $data['oss-request-url'];
                                if (isset($web_pic_url)) {
                                    $img_url = $web_pic_url;
                                }
                                //删除本地图片
                            } catch (\OSS\Core\OssException $e) {
                            }
                        }

                        $s->img_url = $img_url;
                        $s->save();
                    }


                    return json_encode([
                        'status' => 1,
                        'message' => '更新成功',
                    ]);


                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $re_arr['messages'][0],
                    ]);
                }
            }


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

}