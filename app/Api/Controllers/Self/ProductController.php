<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/3
 * Time: 下午4:01
 */

namespace App\Api\Controllers\Self;


use App\Api\Controllers\BaseController;
use App\Models\SelfCategory;
use App\Models\SelfShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ProductController extends BaseController
{


    //根据条码获得商品ID
    public function get_product(Request $request)
    {

        try {
            $product_code = $request->get('product_code', '');
            $device_id = $request->get('device_id', '');
            $device_type = $request->get('device_type', '');

            $data = [
                'product_id' => '20189765148',
                'title' => '雀巢（Nestle）咖啡 ',
                'img_url' => url('/upload/qc.jpg'),
                'description' => '雀巢（Nestle）咖啡 速溶 1+2 原味 微研磨 冲调饮品',
                'category_id' => '1001',
                'product_code' => '22459536245',
                'buy_price' => '1',
                'sell_price' => '1',
                'customer_price' => '1',
                'stock' => '102',
                'is_cd' => '1',
                'supplier' => '国产',
                'status' => '1',
                's_date' => '2018-08-01 00:00:00',
                'e_date' => '2019-07-29 59:59:59',
                'e_day' => '360',//保质期
                'weight' => '10.00',//g
                'qc' => '1',//客户端需要
                'discount' => [
                    [
                        's' => '20',
                        'e' => '50',
                        'r' => '1'
                    ]

                ]
            ];


            //月饼
            if ($product_code == "234567890") {
                $data = [
                    'product_id' => '234567890',
                    'title' => '月饼:花生、葵花籽仁、杏仁',
                    'img_url' => 'http://img.mp.itc.cn/upload/20160912/c153a80dffa14599b783cf011ff3ff5c.jpg',
                    'description' => '中秋月饼',
                    'category_id' => '1001',
                    'product_code' => '234567890',
                    'buy_price' => '1',
                    'sell_price' => '1',
                    'customer_price' => '1',
                    'stock' => '102',
                    'is_cd' => '2',
                    'supplier' => '国产',
                    'status' => '1',
                    's_date' => '2018-08-01 00:00:00',
                    'e_date' => '2019-07-29 59:59:59',
                    'e_day' => '365',//保质期
                    'weight' => '10.00',//g
                    'qc' => '1',//客户端需要
                    'discount' => [
                        [
                            's' => '20',
                            'e' => '50',
                            'r' => '1'
                        ]
                    ]
                ];
            }


            //饮用水、桃浓缩汁、果葡桃浆
            if ($product_code == "6923555239845") {
                $data = [
                    'product_id' => '20189765149',
                    'title' => '汇源桃汁鲜果饮品',
                    'img_url' => 'https://cbu01.alicdn.com/img/ibank/2016/231/917/3223719132_915921230.jpg',
                    'description' => '饮用水、桃浓缩汁、果葡桃浆	',
                    'category_id' => '1001',
                    'product_code' => '6923555239845',
                    'buy_price' => '1',
                    'sell_price' => '2',
                    'customer_price' => '1',
                    'stock' => '102',
                    'is_cd' => '2',
                    'supplier' => '国产',
                    'status' => '1',
                    's_date' => '2018-08-01 00:00:00',
                    'e_date' => '2019-07-29 59:59:59',
                    'e_day' => '365',//保质期
                    'weight' => '10.00',//g
                    'qc' => '1',//客户端需要
                    'discount' => [
                        [
                            's' => '20',
                            'e' => '50',
                            'r' => '1'
                        ]
                    ]
                ];
            }

            //黄山(新制皖烟)
            if ($product_code == "6901028225168") {
                $data = [
                    'product_id' => '2018976510',
                    'title' => '黄山(新制皖烟)',
                    'img_url' => 'https://www.cnxiangyan.com/pictures/2016/10/S1aKo1.jpg',
                    'description' => '普皖金(黄)',
                    'category_id' => '1001',
                    'product_code' => '6901028225168',
                    'buy_price' => '1',
                    'sell_price' => '2',
                    'customer_price' => '1',
                    'stock' => '102',
                    'is_cd' => '2',
                    'supplier' => '国产',
                    'status' => '1',
                    's_date' => '2018-08-01 00:00:00',
                    'e_date' => '2019-07-29 59:59:59',
                    'e_day' => '365',//保质期
                    'weight' => '10.00',//g
                    'qc' => '1',//客户端需要
                    'discount' => [
                        [
                            's' => '20',
                            'e' => '50',
                            'r' => '1'
                        ]
                    ]
                ];
            }


            //益达含木糖醇
            if ($product_code == "6923450656181") {
                $data = [
                    'product_id' => '2018976514',
                    'title' => '益达含木糖醇',
                    'img_url' => 'https://img.yzcdn.cn/upload_files/2017/05/27/Fn7vBglY-1-kbtVEpwbvJdFkTwnG.png?imageView2/2/w/580/h/580/q/75/format/png',
                    'description' => '薄荷、草莓',
                    'category_id' => '1001',
                    'product_code' => '6901028225168',
                    'buy_price' => '1',
                    'sell_price' => '2',
                    'customer_price' => '1',
                    'stock' => '102',
                    'is_cd' => '1',
                    'supplier' => '国产',
                    'status' => '1',
                    's_date' => '2018-08-01 00:00:00',
                    'e_date' => '2019-07-29 59:59:59',
                    'e_day' => '365',//保质期
                    'weight' => '10.00',//g
                    'qc' => '1',//客户端需要
                    'discount' => [
                        [
                            's' => '20',
                            'e' => '50',
                            'r' => '1'
                        ]

                    ]
                ];
            }


            //护手霜：6927006113289

            if ($product_code == "6927006113289") {
                $data = [
                    'product_id' => '2018976510',
                    'title' => '护手霜',
                    'img_url' => 'https://img.yzcdn.cn/upload_files/2014/11/27/1417059724453620.jpg?imageView2/2/w/580/h/580/q/75/format/jpg',
                    'description' => '薄荷、草莓',
                    'category_id' => '1001',
                    'product_code' => '6901028225168',
                    'buy_price' => '1',
                    'sell_price' => '2',
                    'customer_price' => '1',
                    'stock' => '102',
                    'is_cd' => '1',
                    'supplier' => '国产',
                    'status' => '1',
                    's_date' => '2018-08-01 00:00:00',
                    'e_date' => '2019-07-29 59:59:59',
                    'e_day' => '365',//保质期
                    'weight' => '10.00',//g
                    'qc' => '1',//客户端需要
                    'discount' => [
                        [
                            's' => '20',
                            'e' => '50',
                            'r' => '1'
                        ]

                    ]
                ];
            }


            return json_encode([
                'status' => 1,
                'data' => $data,
            ]);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    //获得商品
    public function get_product_list(Request $request)
    {

        try {
            $category_id = $request->get('category_id', '');
            $device_id = $request->get('device_id', '');
            $device_type = $request->get('device_type', '');
            $store_id = $request->get('store_id', '');
            $obj = DB::table('self_shops');
            $where = [];

            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
            }

            if ($category_id) {
                $where[] = ['category_id', '=', $category_id];
            }
            $data = [
                [
                    'store_id'=>'2018061205492993161',
                    'product_id' => '201832976501',
                    'title' => '购物袋中号',
                    'img_url' => '',
                    'description' => '购物袋中号',
                    'category_id' => '201801',
                    'product_code' => '69010282251681',
                    'buy_price' => '1',
                    'sell_price' => '2',
                    'customer_price' => '1',
                    'stock' => '102',
                    'is_cd' => '1',
                    'supplier' => '国产',
                    'status' => '1',
                    's_date' => '2018-08-01 00:00:00',
                    'e_date' => '2019-07-29 59:59:59',
                    'e_day' => '365',//保质期
                    'weight' => '10.00',//g
                    'qc' => '1',//客户端需要
                    'discount' =>[
                        [
                            's' => '20',
                            'e' => '50',
                            'r' => '1'
                        ]

                    ]
                ], [
                    'store_id'=>'2018061205492993161',
                    'product_id' => '20183297650',
                    'title' => '购物袋小号',
                    'img_url' => '',
                    'description' => '购物袋小号',
                    'category_id' => '201801',
                    'product_code' => '6901028225168',
                    'buy_price' => '1',
                    'sell_price' => '2',
                    'customer_price' => '1',
                    'stock' => '102',
                    'is_cd' => '1',
                    'supplier' => '国产',
                    'status' => '1',
                    's_date' => '2018-08-01 00:00:00',
                    'e_date' => '2019-07-29 59:59:59',
                    'e_day' => '365',//保质期
                    'weight' => '10.00',//g
                    'qc' => '1',//客户端需要
                    'discount' =>[
                        [
                            's' => '20',
                            'e' => '50',
                            'r' => '1'
                        ]

                    ]
                ],

            ];

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    //获取分类
    public function get_category(Request $request)
    {

        try {
            $device_id = $request->get('device_id', '');
            $device_type = $request->get('device_type', '');
            $store_id = $request->get('store_id', '');
            $erp_info = $request->get('erp_info', '');

            $arr_erp_info = json_decode($erp_info, true);

            //判断是否设置erp
            if (!isset($arr_erp_info['erp_type'])) {
                return json_encode([
                    'status' => 2,
                    'message' => 'erp_type-必须传',
                ]);
            }

            //判断是否设置erp
            if ($store_id == "") {
                return json_encode([
                    'status' => 2,
                    'message' => 'store_id-必须传',
                ]);
            }

            $Key = 'get_category-' . $store_id;
            $SelfCategory = Cache::get($Key);
            if (!$SelfCategory) {
                $SelfCategory = SelfCategory::where('store_id', $store_id)
                    ->where('erp_type', $arr_erp_info['erp_type'])
                    ->select('pid', 'category_id', 'category_name')
                    ->get();

                Cache::put($Key, $SelfCategory, 3600);
            }

            return json_encode([
                'status' => 1,
                'data' => $SelfCategory,
            ]);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    //获得所有分类和商品
    public function get_products(Request $request)
    {

        $data = '{
    "status": 1,
    "data": [
        {
            "id": 27,
            "pid": 0,
            "store_id": "2018061205492993161",
            "erp_type": "pospal",
            "category_id": "1536662747953565377",
            "category_name": "蔬菜",
            "category_image": "",
            "ext": "{\"parameterType\":\"LAST_RESULT_MAX_ID\",\"parameterValue\":\"391800\"}",
            "created_at": "2018-10-18 10:59:44",
            "updated_at": "2018-10-18 10:59:44",
            "shop_lists": [
                {
                    "sku_list": [
                        {
                            "id": 529,
                            "store_id": "2018061205492993161",
                            "product_id": "209966812316607072",
                            "title": "西红柿",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/5084984c-f504-4a82-bb77-1f7bd9a0519a.jpg",
                            "description": "低温保存",
                            "spec_id": "2099668123166070721539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171119598",
                            "buy_price": "100",
                            "sell_price": "200",
                            "customer_price": "100",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 530,
                            "store_id": "2018061205492993161",
                            "product_id": "1124981688998725539",
                            "title": "黄瓜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/150a8242-db2c-4c44-8dcd-a1eb203476ed.jpg",
                            "description": "不能冻",
                            "spec_id": "11249816889987255391539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171122376",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 589,
                            "store_id": "2018061205492993161",
                            "product_id": "628256737463430489",
                            "title": "测试-099",
                            "img_url": "",
                            "description": "",
                            "spec_id": "6282567374634304891539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810191340033",
                            "buy_price": "1000",
                            "sell_price": "1000",
                            "customer_price": "1000",
                            "stock": "10",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 590,
                            "store_id": "2018061205492993161",
                            "product_id": "299910783204676899",
                            "title": "测试-222",
                            "img_url": "",
                            "description": "",
                            "spec_id": "2999107832046768991539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "123123",
                            "buy_price": "9800",
                            "sell_price": "10000",
                            "customer_price": "10000",
                            "stock": "900",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 591,
                            "store_id": "2018061205492993161",
                            "product_id": "944638295784152122",
                            "title": "测试",
                            "img_url": "",
                            "description": "",
                            "spec_id": "9446382957841521221539935810",
                            "spec_desc": "中杯",
                            "category_id": "1536662747953565377",
                            "product_code": "213123",
                            "buy_price": "2900",
                            "sell_price": "1800",
                            "customer_price": "1800",
                            "stock": "992",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        },{
                            "id": 591,
                            "store_id": "2018061205492993161",
                            "product_id": "944638295784152123",
                            "title": "测试",
                            "img_url": "",
                            "description": "",
                            "spec_id": "9446382957841521209991539935810",
                            "spec_desc": "小杯",
                            "category_id": "1536662747953565377",
                            "product_code": "213123",
                            "buy_price": "900",
                            "sell_price": "800",
                            "customer_price": "800",
                            "stock": "992",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                }
            ]
        },
        {
            "id": 28,
            "pid": 0,
            "store_id": "2018061205492993161",
            "erp_type": "pospal",
            "category_id": "1536662759094630756",
            "category_name": "水果",
            "category_image": "",
            "ext": "{\"parameterType\":\"LAST_RESULT_MAX_ID\",\"parameterValue\":\"391800\"}",
            "created_at": "2018-10-18 10:59:44",
            "updated_at": "2018-10-18 10:59:44",
            "shop_lists": [
                {
                    "sku_list": [
                        {
                            "id": 529,
                            "store_id": "2018061205492993161",
                            "product_id": "209966812316607072",
                            "title": "西红柿",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/5084984c-f504-4a82-bb77-1f7bd9a0519a.jpg",
                            "description": "低温保存",
                            "spec_id": "2099668123166070721539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171119598",
                            "buy_price": "10",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 530,
                            "store_id": "2018061205492993161",
                            "product_id": "1124981688998725539",
                            "title": "黄瓜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/150a8242-db2c-4c44-8dcd-a1eb203476ed.jpg",
                            "description": "不能冻",
                            "spec_id": "11249816889987255391539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171122376",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 589,
                            "store_id": "2018061205492993161",
                            "product_id": "628256737463430489",
                            "title": "测试-099",
                            "img_url": "",
                            "description": "",
                            "spec_id": "6282567374634304891539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810191340033",
                            "buy_price": "1000",
                            "sell_price": "1000",
                            "customer_price": "1000",
                            "stock": "10",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 590,
                            "store_id": "2018061205492993161",
                            "product_id": "299910783204676899",
                            "title": "测试-222",
                            "img_url": "",
                            "description": "",
                            "spec_id": "2999107832046768991539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "123123",
                            "buy_price": "9800",
                            "sell_price": "10000",
                            "customer_price": "10000",
                            "stock": "900",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 591,
                            "store_id": "2018061205492993161",
                            "product_id": "944638295784152122",
                            "title": "测试",
                            "img_url": "",
                            "description": "",
                            "spec_id": "9446382957841521221539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "213123",
                            "buy_price": "2900",
                            "sell_price": "1800",
                            "customer_price": "1800",
                            "stock": "992",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 528,
                            "store_id": "2018061205492993161",
                            "product_id": "343389447295593113",
                            "title": "测试商品3",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png",
                            "description": "111",
                            "spec_id": "3433894472955931131539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1809111844263",
                            "buy_price": "800",
                            "sell_price": "1",
                            "customer_price": "900",
                            "stock": "99989",
                            "is_cd": "0",
                            "supplier": "0",
                            "status": "1",
                            "s_date": "2018-09-11",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 531,
                            "store_id": "2018061205492993161",
                            "product_id": "549403991975548426",
                            "title": "红心柚子",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/46e1f013-1d79-4444-9d98-be5afe6f30b8.jpg",
                            "description": "不能压",
                            "spec_id": "5494039919755484261539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171123007",
                            "buy_price": "20",
                            "sell_price": "30",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 532,
                            "store_id": "2018061205492993161",
                            "product_id": "1076396052083467956",
                            "title": "菠萝",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/9bf5e8c0-4944-41f6-b37e-53a316725654.jpg",
                            "description": "常温保存，易腐烂！",
                            "spec_id": "10763960520834679561539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171126336",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                }
            ]
        },
        {
            "id": 29,
            "pid": 0,
            "store_id": "2018061205492993161",
            "erp_type": "pospal",
            "category_id": "1539746300305143767",
            "category_name": "办公用品",
            "category_image": "",
            "ext": "{\"parameterType\":\"LAST_RESULT_MAX_ID\",\"parameterValue\":\"391800\"}",
            "created_at": "2018-10-18 10:59:44",
            "updated_at": "2018-10-18 10:59:44",
            "shop_lists": [
                {
                    "sku_list": [
                        {
                            "id": 529,
                            "store_id": "2018061205492993161",
                            "product_id": "209966812316607072",
                            "title": "西红柿",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/5084984c-f504-4a82-bb77-1f7bd9a0519a.jpg",
                            "description": "低温保存",
                            "spec_id": "2099668123166070721539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171119598",
                            "buy_price": "10",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 530,
                            "store_id": "2018061205492993161",
                            "product_id": "1124981688998725539",
                            "title": "黄瓜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/150a8242-db2c-4c44-8dcd-a1eb203476ed.jpg",
                            "description": "不能冻",
                            "spec_id": "11249816889987255391539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171122376",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 589,
                            "store_id": "2018061205492993161",
                            "product_id": "628256737463430489",
                            "title": "测试-099",
                            "img_url": "",
                            "description": "",
                            "spec_id": "6282567374634304891539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810191340033",
                            "buy_price": "1000",
                            "sell_price": "1000",
                            "customer_price": "1000",
                            "stock": "10",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 590,
                            "store_id": "2018061205492993161",
                            "product_id": "299910783204676899",
                            "title": "测试-222",
                            "img_url": "",
                            "description": "",
                            "spec_id": "2999107832046768991539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "123123",
                            "buy_price": "9800",
                            "sell_price": "10000",
                            "customer_price": "10000",
                            "stock": "900",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 591,
                            "store_id": "2018061205492993161",
                            "product_id": "944638295784152122",
                            "title": "测试",
                            "img_url": "",
                            "description": "",
                            "spec_id": "9446382957841521221539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "213123",
                            "buy_price": "2900",
                            "sell_price": "1800",
                            "customer_price": "1800",
                            "stock": "992",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 528,
                            "store_id": "2018061205492993161",
                            "product_id": "343389447295593113",
                            "title": "测试商品3",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png",
                            "description": "111",
                            "spec_id": "3433894472955931131539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1809111844263",
                            "buy_price": "800",
                            "sell_price": "1",
                            "customer_price": "900",
                            "stock": "99989",
                            "is_cd": "0",
                            "supplier": "0",
                            "status": "1",
                            "s_date": "2018-09-11",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 531,
                            "store_id": "2018061205492993161",
                            "product_id": "549403991975548426",
                            "title": "红心柚子",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/46e1f013-1d79-4444-9d98-be5afe6f30b8.jpg",
                            "description": "不能压",
                            "spec_id": "5494039919755484261539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171123007",
                            "buy_price": "20",
                            "sell_price": "30",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 532,
                            "store_id": "2018061205492993161",
                            "product_id": "1076396052083467956",
                            "title": "菠萝",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/9bf5e8c0-4944-41f6-b37e-53a316725654.jpg",
                            "description": "常温保存，易腐烂！",
                            "spec_id": "10763960520834679561539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171126336",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 533,
                            "store_id": "2018061205492993161",
                            "product_id": "1115178051374965484",
                            "title": "打印纸",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f069b7f8-e9cb-4c2b-a590-e26163a73680.jpg",
                            "description": "易受潮，干燥保存",
                            "spec_id": "11151780513749654841539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171212442",
                            "buy_price": "200",
                            "sell_price": "1000",
                            "customer_price": "950",
                            "stock": "100",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 534,
                            "store_id": "2018061205492993161",
                            "product_id": "972649552002842447",
                            "title": "中性水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/657dfae1-1207-4faf-bffa-7bdd30b37b64.jpg",
                            "description": "",
                            "spec_id": "9726495520028424471539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "200",
                            "stock": "150",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:06"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 535,
                            "store_id": "2018061205492993161",
                            "product_id": "882513527165244816",
                            "title": "黑水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/b01d6df6-3c03-4553-b30d-8399578e2a31.jpg",
                            "description": "",
                            "spec_id": "8825135271652448161539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-1",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 536,
                            "store_id": "2018061205492993161",
                            "product_id": "1049581760999832702",
                            "title": "红水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/2fc64cdd-99fb-4a91-84e6-2b817fd5a630.jpg",
                            "description": "",
                            "spec_id": "10495817609998327021539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-2",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 537,
                            "store_id": "2018061205492993161",
                            "product_id": "367915709964723822",
                            "title": "蓝水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/41fc22a5-310c-45d3-8dd5-45a3b5a4c44f.jpg",
                            "description": "",
                            "spec_id": "3679157099647238221539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-3",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                }
            ]
        },
        {
            "id": 30,
            "pid": 0,
            "store_id": "2018061205492993161",
            "erp_type": "pospal",
            "category_id": "1539746310352691489",
            "category_name": "零食",
            "category_image": "",
            "ext": "{\"parameterType\":\"LAST_RESULT_MAX_ID\",\"parameterValue\":\"391800\"}",
            "created_at": "2018-10-18 10:59:44",
            "updated_at": "2018-10-18 10:59:44",
            "shop_lists": [
                {
                    "sku_list": [
                        {
                            "id": 529,
                            "store_id": "2018061205492993161",
                            "product_id": "209966812316607072",
                            "title": "西红柿",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/5084984c-f504-4a82-bb77-1f7bd9a0519a.jpg",
                            "description": "低温保存",
                            "spec_id": "2099668123166070721539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171119598",
                            "buy_price": "10",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 530,
                            "store_id": "2018061205492993161",
                            "product_id": "1124981688998725539",
                            "title": "黄瓜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/150a8242-db2c-4c44-8dcd-a1eb203476ed.jpg",
                            "description": "不能冻",
                            "spec_id": "11249816889987255391539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171122376",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 589,
                            "store_id": "2018061205492993161",
                            "product_id": "628256737463430489",
                            "title": "测试-099",
                            "img_url": "",
                            "description": "",
                            "spec_id": "6282567374634304891539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810191340033",
                            "buy_price": "1000",
                            "sell_price": "1000",
                            "customer_price": "1000",
                            "stock": "10",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 590,
                            "store_id": "2018061205492993161",
                            "product_id": "299910783204676899",
                            "title": "测试-222",
                            "img_url": "",
                            "description": "",
                            "spec_id": "2999107832046768991539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "123123",
                            "buy_price": "9800",
                            "sell_price": "10000",
                            "customer_price": "10000",
                            "stock": "900",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 591,
                            "store_id": "2018061205492993161",
                            "product_id": "944638295784152122",
                            "title": "测试",
                            "img_url": "",
                            "description": "",
                            "spec_id": "9446382957841521221539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "213123",
                            "buy_price": "2900",
                            "sell_price": "1800",
                            "customer_price": "1800",
                            "stock": "992",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 528,
                            "store_id": "2018061205492993161",
                            "product_id": "343389447295593113",
                            "title": "测试商品3",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png",
                            "description": "111",
                            "spec_id": "3433894472955931131539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1809111844263",
                            "buy_price": "800",
                            "sell_price": "1",
                            "customer_price": "900",
                            "stock": "99989",
                            "is_cd": "0",
                            "supplier": "0",
                            "status": "1",
                            "s_date": "2018-09-11",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 531,
                            "store_id": "2018061205492993161",
                            "product_id": "549403991975548426",
                            "title": "红心柚子",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/46e1f013-1d79-4444-9d98-be5afe6f30b8.jpg",
                            "description": "不能压",
                            "spec_id": "5494039919755484261539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171123007",
                            "buy_price": "20",
                            "sell_price": "30",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 532,
                            "store_id": "2018061205492993161",
                            "product_id": "1076396052083467956",
                            "title": "菠萝",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/9bf5e8c0-4944-41f6-b37e-53a316725654.jpg",
                            "description": "常温保存，易腐烂！",
                            "spec_id": "10763960520834679561539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171126336",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 533,
                            "store_id": "2018061205492993161",
                            "product_id": "1115178051374965484",
                            "title": "打印纸",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f069b7f8-e9cb-4c2b-a590-e26163a73680.jpg",
                            "description": "易受潮，干燥保存",
                            "spec_id": "11151780513749654841539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171212442",
                            "buy_price": "200",
                            "sell_price": "1000",
                            "customer_price": "950",
                            "stock": "100",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 534,
                            "store_id": "2018061205492993161",
                            "product_id": "972649552002842447",
                            "title": "中性水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/657dfae1-1207-4faf-bffa-7bdd30b37b64.jpg",
                            "description": "",
                            "spec_id": "9726495520028424471539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "200",
                            "stock": "150",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:06"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 535,
                            "store_id": "2018061205492993161",
                            "product_id": "882513527165244816",
                            "title": "黑水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/b01d6df6-3c03-4553-b30d-8399578e2a31.jpg",
                            "description": "",
                            "spec_id": "8825135271652448161539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-1",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 536,
                            "store_id": "2018061205492993161",
                            "product_id": "1049581760999832702",
                            "title": "红水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/2fc64cdd-99fb-4a91-84e6-2b817fd5a630.jpg",
                            "description": "",
                            "spec_id": "10495817609998327021539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-2",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 537,
                            "store_id": "2018061205492993161",
                            "product_id": "367915709964723822",
                            "title": "蓝水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/41fc22a5-310c-45d3-8dd5-45a3b5a4c44f.jpg",
                            "description": "",
                            "spec_id": "3679157099647238221539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-3",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 546,
                            "store_id": "2018061205492993161",
                            "product_id": "1106402921774817872",
                            "title": "卫龙辣条",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/3649e71f-07be-4a3d-a602-65b62d1f3cf3.jpg",
                            "description": "常温保存",
                            "spec_id": "11064029217748178721539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171441248",
                            "buy_price": "500",
                            "sell_price": "990",
                            "customer_price": "990",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 547,
                            "store_id": "2018061205492993161",
                            "product_id": "145317714345745177",
                            "title": "酸奶",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/1b55335f-a35e-496d-b5f0-2061a99c18d0.jpg",
                            "description": "常温保存",
                            "spec_id": "1453177143457451771539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171448254",
                            "buy_price": "4000",
                            "sell_price": "5600",
                            "customer_price": "5600",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 548,
                            "store_id": "2018061205492993161",
                            "product_id": "1053322639928345624",
                            "title": "矿泉水",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/ab7d46cc-ba7e-4c3a-a791-1b6f315d35b7.jpg",
                            "description": "常温保存",
                            "spec_id": "10533226399283456241539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171456143",
                            "buy_price": "2000",
                            "sell_price": "2960",
                            "customer_price": "2960",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                }
            ]
        },
        {
            "id": 31,
            "pid": 0,
            "store_id": "2018061205492993161",
            "erp_type": "pospal",
            "category_id": "1539746331852441898",
            "category_name": "护肤品",
            "category_image": "",
            "ext": "{\"parameterType\":\"LAST_RESULT_MAX_ID\",\"parameterValue\":\"391800\"}",
            "created_at": "2018-10-18 10:59:44",
            "updated_at": "2018-10-18 10:59:44",
            "shop_lists": [
                {
                    "sku_list": [
                        {
                            "id": 529,
                            "store_id": "2018061205492993161",
                            "product_id": "209966812316607072",
                            "title": "西红柿",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/5084984c-f504-4a82-bb77-1f7bd9a0519a.jpg",
                            "description": "低温保存",
                            "spec_id": "2099668123166070721539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171119598",
                            "buy_price": "10",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 530,
                            "store_id": "2018061205492993161",
                            "product_id": "1124981688998725539",
                            "title": "黄瓜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/150a8242-db2c-4c44-8dcd-a1eb203476ed.jpg",
                            "description": "不能冻",
                            "spec_id": "11249816889987255391539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171122376",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 589,
                            "store_id": "2018061205492993161",
                            "product_id": "628256737463430489",
                            "title": "测试-099",
                            "img_url": "",
                            "description": "",
                            "spec_id": "6282567374634304891539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810191340033",
                            "buy_price": "1000",
                            "sell_price": "1000",
                            "customer_price": "1000",
                            "stock": "10",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 590,
                            "store_id": "2018061205492993161",
                            "product_id": "299910783204676899",
                            "title": "测试-222",
                            "img_url": "",
                            "description": "",
                            "spec_id": "2999107832046768991539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "123123",
                            "buy_price": "9800",
                            "sell_price": "10000",
                            "customer_price": "10000",
                            "stock": "900",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 591,
                            "store_id": "2018061205492993161",
                            "product_id": "944638295784152122",
                            "title": "测试",
                            "img_url": "",
                            "description": "",
                            "spec_id": "9446382957841521221539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "213123",
                            "buy_price": "2900",
                            "sell_price": "1800",
                            "customer_price": "1800",
                            "stock": "992",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 528,
                            "store_id": "2018061205492993161",
                            "product_id": "343389447295593113",
                            "title": "测试商品3",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png",
                            "description": "111",
                            "spec_id": "3433894472955931131539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1809111844263",
                            "buy_price": "800",
                            "sell_price": "1",
                            "customer_price": "900",
                            "stock": "99989",
                            "is_cd": "0",
                            "supplier": "0",
                            "status": "1",
                            "s_date": "2018-09-11",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 531,
                            "store_id": "2018061205492993161",
                            "product_id": "549403991975548426",
                            "title": "红心柚子",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/46e1f013-1d79-4444-9d98-be5afe6f30b8.jpg",
                            "description": "不能压",
                            "spec_id": "5494039919755484261539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171123007",
                            "buy_price": "20",
                            "sell_price": "30",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 532,
                            "store_id": "2018061205492993161",
                            "product_id": "1076396052083467956",
                            "title": "菠萝",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/9bf5e8c0-4944-41f6-b37e-53a316725654.jpg",
                            "description": "常温保存，易腐烂！",
                            "spec_id": "10763960520834679561539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171126336",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 533,
                            "store_id": "2018061205492993161",
                            "product_id": "1115178051374965484",
                            "title": "打印纸",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f069b7f8-e9cb-4c2b-a590-e26163a73680.jpg",
                            "description": "易受潮，干燥保存",
                            "spec_id": "11151780513749654841539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171212442",
                            "buy_price": "200",
                            "sell_price": "1000",
                            "customer_price": "950",
                            "stock": "100",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 534,
                            "store_id": "2018061205492993161",
                            "product_id": "972649552002842447",
                            "title": "中性水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/657dfae1-1207-4faf-bffa-7bdd30b37b64.jpg",
                            "description": "",
                            "spec_id": "9726495520028424471539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "200",
                            "stock": "150",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:06"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 535,
                            "store_id": "2018061205492993161",
                            "product_id": "882513527165244816",
                            "title": "黑水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/b01d6df6-3c03-4553-b30d-8399578e2a31.jpg",
                            "description": "",
                            "spec_id": "8825135271652448161539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-1",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 536,
                            "store_id": "2018061205492993161",
                            "product_id": "1049581760999832702",
                            "title": "红水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/2fc64cdd-99fb-4a91-84e6-2b817fd5a630.jpg",
                            "description": "",
                            "spec_id": "10495817609998327021539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-2",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 537,
                            "store_id": "2018061205492993161",
                            "product_id": "367915709964723822",
                            "title": "蓝水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/41fc22a5-310c-45d3-8dd5-45a3b5a4c44f.jpg",
                            "description": "",
                            "spec_id": "3679157099647238221539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-3",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 546,
                            "store_id": "2018061205492993161",
                            "product_id": "1106402921774817872",
                            "title": "卫龙辣条",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/3649e71f-07be-4a3d-a602-65b62d1f3cf3.jpg",
                            "description": "常温保存",
                            "spec_id": "11064029217748178721539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171441248",
                            "buy_price": "500",
                            "sell_price": "990",
                            "customer_price": "990",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 547,
                            "store_id": "2018061205492993161",
                            "product_id": "145317714345745177",
                            "title": "酸奶",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/1b55335f-a35e-496d-b5f0-2061a99c18d0.jpg",
                            "description": "常温保存",
                            "spec_id": "1453177143457451771539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171448254",
                            "buy_price": "4000",
                            "sell_price": "5600",
                            "customer_price": "5600",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 548,
                            "store_id": "2018061205492993161",
                            "product_id": "1053322639928345624",
                            "title": "矿泉水",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/ab7d46cc-ba7e-4c3a-a791-1b6f315d35b7.jpg",
                            "description": "常温保存",
                            "spec_id": "10533226399283456241539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171456143",
                            "buy_price": "2000",
                            "sell_price": "2960",
                            "customer_price": "2960",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 538,
                            "store_id": "2018061205492993161",
                            "product_id": "933266610658381269",
                            "title": "面膜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/b6acb5af-7772-42c5-9e47-9a72ef0519ef.png",
                            "description": "常温保存",
                            "spec_id": "9332666106583812691539935809",
                            "spec_desc": "",
                            "category_id": "1539746331852441898",
                            "product_code": "1810171328006",
                            "buy_price": "1500",
                            "sell_price": "4600",
                            "customer_price": "4000",
                            "stock": "100",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:06"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 539,
                            "store_id": "2018061205492993161",
                            "product_id": "109849062817763285",
                            "title": "洗面奶",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f6a4db26-66db-4fc1-8f63-436cbe442f4f.png",
                            "description": "此商品买一送一",
                            "spec_id": "1098490628177632851539935809",
                            "spec_desc": "",
                            "category_id": "1539746331852441898",
                            "product_code": "1810171336216",
                            "buy_price": "1200",
                            "sell_price": "3390",
                            "customer_price": "3300",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:07"
                        }
                    ]
                }
            ]
        },
        {
            "id": 32,
            "pid": 0,
            "store_id": "2018061205492993161",
            "erp_type": "pospal",
            "category_id": "1539754816649944026-1",
            "category_name": "电子产品",
            "category_image": "",
            "ext": "{\"parameterType\":\"LAST_RESULT_MAX_ID\",\"parameterValue\":\"391800\"}",
            "created_at": "2018-10-18 10:59:44",
            "updated_at": "2018-10-18 10:59:44",
            "shop_lists": [
                {
                    "sku_list": [
                        {
                            "id": 529,
                            "store_id": "2018061205492993161",
                            "product_id": "209966812316607072",
                            "title": "西红柿",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/5084984c-f504-4a82-bb77-1f7bd9a0519a.jpg",
                            "description": "低温保存",
                            "spec_id": "2099668123166070721539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171119598",
                            "buy_price": "10",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 530,
                            "store_id": "2018061205492993161",
                            "product_id": "1124981688998725539",
                            "title": "黄瓜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/150a8242-db2c-4c44-8dcd-a1eb203476ed.jpg",
                            "description": "不能冻",
                            "spec_id": "11249816889987255391539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171122376",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 589,
                            "store_id": "2018061205492993161",
                            "product_id": "628256737463430489",
                            "title": "测试-099",
                            "img_url": "",
                            "description": "",
                            "spec_id": "6282567374634304891539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810191340033",
                            "buy_price": "1000",
                            "sell_price": "1000",
                            "customer_price": "1000",
                            "stock": "10",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 590,
                            "store_id": "2018061205492993161",
                            "product_id": "299910783204676899",
                            "title": "测试-222",
                            "img_url": "",
                            "description": "",
                            "spec_id": "2999107832046768991539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "123123",
                            "buy_price": "9800",
                            "sell_price": "10000",
                            "customer_price": "10000",
                            "stock": "900",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 591,
                            "store_id": "2018061205492993161",
                            "product_id": "944638295784152122",
                            "title": "测试",
                            "img_url": "",
                            "description": "",
                            "spec_id": "9446382957841521221539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "213123",
                            "buy_price": "2900",
                            "sell_price": "1800",
                            "customer_price": "1800",
                            "stock": "992",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 528,
                            "store_id": "2018061205492993161",
                            "product_id": "343389447295593113",
                            "title": "测试商品3",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png",
                            "description": "111",
                            "spec_id": "3433894472955931131539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1809111844263",
                            "buy_price": "800",
                            "sell_price": "1",
                            "customer_price": "900",
                            "stock": "99989",
                            "is_cd": "0",
                            "supplier": "0",
                            "status": "1",
                            "s_date": "2018-09-11",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 531,
                            "store_id": "2018061205492993161",
                            "product_id": "549403991975548426",
                            "title": "红心柚子",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/46e1f013-1d79-4444-9d98-be5afe6f30b8.jpg",
                            "description": "不能压",
                            "spec_id": "5494039919755484261539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171123007",
                            "buy_price": "20",
                            "sell_price": "30",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 532,
                            "store_id": "2018061205492993161",
                            "product_id": "1076396052083467956",
                            "title": "菠萝",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/9bf5e8c0-4944-41f6-b37e-53a316725654.jpg",
                            "description": "常温保存，易腐烂！",
                            "spec_id": "10763960520834679561539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171126336",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 533,
                            "store_id": "2018061205492993161",
                            "product_id": "1115178051374965484",
                            "title": "打印纸",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f069b7f8-e9cb-4c2b-a590-e26163a73680.jpg",
                            "description": "易受潮，干燥保存",
                            "spec_id": "11151780513749654841539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171212442",
                            "buy_price": "200",
                            "sell_price": "1000",
                            "customer_price": "950",
                            "stock": "100",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 534,
                            "store_id": "2018061205492993161",
                            "product_id": "972649552002842447",
                            "title": "中性水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/657dfae1-1207-4faf-bffa-7bdd30b37b64.jpg",
                            "description": "",
                            "spec_id": "9726495520028424471539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "200",
                            "stock": "150",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:06"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 535,
                            "store_id": "2018061205492993161",
                            "product_id": "882513527165244816",
                            "title": "黑水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/b01d6df6-3c03-4553-b30d-8399578e2a31.jpg",
                            "description": "",
                            "spec_id": "8825135271652448161539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-1",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 536,
                            "store_id": "2018061205492993161",
                            "product_id": "1049581760999832702",
                            "title": "红水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/2fc64cdd-99fb-4a91-84e6-2b817fd5a630.jpg",
                            "description": "",
                            "spec_id": "10495817609998327021539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-2",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 537,
                            "store_id": "2018061205492993161",
                            "product_id": "367915709964723822",
                            "title": "蓝水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/41fc22a5-310c-45d3-8dd5-45a3b5a4c44f.jpg",
                            "description": "",
                            "spec_id": "3679157099647238221539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-3",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 546,
                            "store_id": "2018061205492993161",
                            "product_id": "1106402921774817872",
                            "title": "卫龙辣条",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/3649e71f-07be-4a3d-a602-65b62d1f3cf3.jpg",
                            "description": "常温保存",
                            "spec_id": "11064029217748178721539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171441248",
                            "buy_price": "500",
                            "sell_price": "990",
                            "customer_price": "990",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 547,
                            "store_id": "2018061205492993161",
                            "product_id": "145317714345745177",
                            "title": "酸奶",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/1b55335f-a35e-496d-b5f0-2061a99c18d0.jpg",
                            "description": "常温保存",
                            "spec_id": "1453177143457451771539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171448254",
                            "buy_price": "4000",
                            "sell_price": "5600",
                            "customer_price": "5600",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 548,
                            "store_id": "2018061205492993161",
                            "product_id": "1053322639928345624",
                            "title": "矿泉水",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/ab7d46cc-ba7e-4c3a-a791-1b6f315d35b7.jpg",
                            "description": "常温保存",
                            "spec_id": "10533226399283456241539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171456143",
                            "buy_price": "2000",
                            "sell_price": "2960",
                            "customer_price": "2960",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 538,
                            "store_id": "2018061205492993161",
                            "product_id": "933266610658381269",
                            "title": "面膜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/b6acb5af-7772-42c5-9e47-9a72ef0519ef.png",
                            "description": "常温保存",
                            "spec_id": "9332666106583812691539935809",
                            "spec_desc": "",
                            "category_id": "1539746331852441898",
                            "product_code": "1810171328006",
                            "buy_price": "1500",
                            "sell_price": "4600",
                            "customer_price": "4000",
                            "stock": "100",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:06"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 539,
                            "store_id": "2018061205492993161",
                            "product_id": "109849062817763285",
                            "title": "洗面奶",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f6a4db26-66db-4fc1-8f63-436cbe442f4f.png",
                            "description": "此商品买一送一",
                            "spec_id": "1098490628177632851539935809",
                            "spec_desc": "",
                            "category_id": "1539746331852441898",
                            "product_code": "1810171336216",
                            "buy_price": "1200",
                            "sell_price": "3390",
                            "customer_price": "3300",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:07"
                        }
                    ]
                }
            ]
        },
        {
            "id": 35,
            "pid": 0,
            "store_id": "2018061205492993161",
            "erp_type": "pospal",
            "category_id": "153975481664994402600000",
            "category_name": "数码产品",
            "category_image": "",
            "ext": "{\"parameterType\":\"LAST_RESULT_MAX_ID\",\"parameterValue\":\"391800\"}",
            "created_at": "2018-10-18 10:59:44",
            "updated_at": "2018-10-18 10:59:44",
            "shop_lists": [
                {
                    "sku_list": [
                        {
                            "id": 529,
                            "store_id": "2018061205492993161",
                            "product_id": "209966812316607072",
                            "title": "西红柿",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/5084984c-f504-4a82-bb77-1f7bd9a0519a.jpg",
                            "description": "低温保存",
                            "spec_id": "2099668123166070721539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171119598",
                            "buy_price": "10",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 530,
                            "store_id": "2018061205492993161",
                            "product_id": "1124981688998725539",
                            "title": "黄瓜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/150a8242-db2c-4c44-8dcd-a1eb203476ed.jpg",
                            "description": "不能冻",
                            "spec_id": "11249816889987255391539935809",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810171122376",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 589,
                            "store_id": "2018061205492993161",
                            "product_id": "628256737463430489",
                            "title": "测试-099",
                            "img_url": "",
                            "description": "",
                            "spec_id": "6282567374634304891539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "1810191340033",
                            "buy_price": "1000",
                            "sell_price": "1000",
                            "customer_price": "1000",
                            "stock": "10",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 590,
                            "store_id": "2018061205492993161",
                            "product_id": "299910783204676899",
                            "title": "测试-222",
                            "img_url": "",
                            "description": "",
                            "spec_id": "2999107832046768991539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "123123",
                            "buy_price": "9800",
                            "sell_price": "10000",
                            "customer_price": "10000",
                            "stock": "900",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 591,
                            "store_id": "2018061205492993161",
                            "product_id": "944638295784152122",
                            "title": "测试",
                            "img_url": "",
                            "description": "",
                            "spec_id": "9446382957841521221539935810",
                            "spec_desc": "",
                            "category_id": "1536662747953565377",
                            "product_code": "213123",
                            "buy_price": "2900",
                            "sell_price": "1800",
                            "customer_price": "1800",
                            "stock": "992",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:50",
                            "updated_at": "2018-10-19 15:56:50"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 528,
                            "store_id": "2018061205492993161",
                            "product_id": "343389447295593113",
                            "title": "测试商品3",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png",
                            "description": "111",
                            "spec_id": "3433894472955931131539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1809111844263",
                            "buy_price": "800",
                            "sell_price": "1",
                            "customer_price": "900",
                            "stock": "99989",
                            "is_cd": "0",
                            "supplier": "0",
                            "status": "1",
                            "s_date": "2018-09-11",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:04"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 531,
                            "store_id": "2018061205492993161",
                            "product_id": "549403991975548426",
                            "title": "红心柚子",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/46e1f013-1d79-4444-9d98-be5afe6f30b8.jpg",
                            "description": "不能压",
                            "spec_id": "5494039919755484261539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171123007",
                            "buy_price": "20",
                            "sell_price": "30",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 532,
                            "store_id": "2018061205492993161",
                            "product_id": "1076396052083467956",
                            "title": "菠萝",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/9bf5e8c0-4944-41f6-b37e-53a316725654.jpg",
                            "description": "常温保存，易腐烂！",
                            "spec_id": "10763960520834679561539935809",
                            "spec_desc": "",
                            "category_id": "1536662759094630756",
                            "product_code": "1810171126336",
                            "buy_price": "15",
                            "sell_price": "20",
                            "customer_price": "20",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 533,
                            "store_id": "2018061205492993161",
                            "product_id": "1115178051374965484",
                            "title": "打印纸",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f069b7f8-e9cb-4c2b-a590-e26163a73680.jpg",
                            "description": "易受潮，干燥保存",
                            "spec_id": "11151780513749654841539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171212442",
                            "buy_price": "200",
                            "sell_price": "1000",
                            "customer_price": "950",
                            "stock": "100",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 534,
                            "store_id": "2018061205492993161",
                            "product_id": "972649552002842447",
                            "title": "中性水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/657dfae1-1207-4faf-bffa-7bdd30b37b64.jpg",
                            "description": "",
                            "spec_id": "9726495520028424471539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "200",
                            "stock": "150",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:06"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 535,
                            "store_id": "2018061205492993161",
                            "product_id": "882513527165244816",
                            "title": "黑水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/b01d6df6-3c03-4553-b30d-8399578e2a31.jpg",
                            "description": "",
                            "spec_id": "8825135271652448161539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-1",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 536,
                            "store_id": "2018061205492993161",
                            "product_id": "1049581760999832702",
                            "title": "红水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/2fc64cdd-99fb-4a91-84e6-2b817fd5a630.jpg",
                            "description": "",
                            "spec_id": "10495817609998327021539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-2",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 537,
                            "store_id": "2018061205492993161",
                            "product_id": "367915709964723822",
                            "title": "蓝水笔",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/41fc22a5-310c-45d3-8dd5-45a3b5a4c44f.jpg",
                            "description": "",
                            "spec_id": "3679157099647238221539935809",
                            "spec_desc": "",
                            "category_id": "1539746300305143767",
                            "product_code": "1810171310100-3",
                            "buy_price": "100",
                            "sell_price": "250",
                            "customer_price": "250",
                            "stock": "50",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:05"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 546,
                            "store_id": "2018061205492993161",
                            "product_id": "1106402921774817872",
                            "title": "卫龙辣条",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/3649e71f-07be-4a3d-a602-65b62d1f3cf3.jpg",
                            "description": "常温保存",
                            "spec_id": "11064029217748178721539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171441248",
                            "buy_price": "500",
                            "sell_price": "990",
                            "customer_price": "990",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 547,
                            "store_id": "2018061205492993161",
                            "product_id": "145317714345745177",
                            "title": "酸奶",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/1b55335f-a35e-496d-b5f0-2061a99c18d0.jpg",
                            "description": "常温保存",
                            "spec_id": "1453177143457451771539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171448254",
                            "buy_price": "4000",
                            "sell_price": "5600",
                            "customer_price": "5600",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 548,
                            "store_id": "2018061205492993161",
                            "product_id": "1053322639928345624",
                            "title": "矿泉水",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/ab7d46cc-ba7e-4c3a-a791-1b6f315d35b7.jpg",
                            "description": "常温保存",
                            "spec_id": "10533226399283456241539935809",
                            "spec_desc": "",
                            "category_id": "1539746310352691489",
                            "product_code": "1810171456143",
                            "buy_price": "2000",
                            "sell_price": "2960",
                            "customer_price": "2960",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:09"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 538,
                            "store_id": "2018061205492993161",
                            "product_id": "933266610658381269",
                            "title": "面膜",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/b6acb5af-7772-42c5-9e47-9a72ef0519ef.png",
                            "description": "常温保存",
                            "spec_id": "9332666106583812691539935809",
                            "spec_desc": "",
                            "category_id": "1539746331852441898",
                            "product_code": "1810171328006",
                            "buy_price": "1500",
                            "sell_price": "4600",
                            "customer_price": "4000",
                            "stock": "100",
                            "is_cd": "0",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:06"
                        }
                    ]
                },
                {
                    "sku_list": [
                        {
                            "id": 539,
                            "store_id": "2018061205492993161",
                            "product_id": "109849062817763285",
                            "title": "洗面奶",
                            "img_url": "http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f6a4db26-66db-4fc1-8f63-436cbe442f4f.png",
                            "description": "此商品买一送一",
                            "spec_id": "1098490628177632851539935809",
                            "spec_desc": "",
                            "category_id": "1539746331852441898",
                            "product_code": "1810171336216",
                            "buy_price": "1200",
                            "sell_price": "3390",
                            "customer_price": "3300",
                            "stock": "100",
                            "is_cd": "1",
                            "supplier": "160691713154919686",
                            "status": "1",
                            "s_date": "2018-10-17",
                            "e_date": "",
                            "e_day": "",
                            "weight": "",
                            "qc": "0",
                            "discount": "[{\"s\":\"2000\",\"e\":\"10000\",\"r\":\"500\"},{\"s\":\"10000\",\"e\":\"2000000000\",\"r\":\"1000\"}]",
                            "created_at": "2018-10-19 15:56:49",
                            "updated_at": "2018-10-19 15:57:07"
                        }
                    ]
                }
            ]
        }
    ]
}';


        return $data;

        try {
            $category_id = $request->get('category_id', '');
            $device_id = $request->get('device_id', '');
            $device_type = $request->get('device_type', '');
            $store_id = $request->get('store_id', '');


            $where = [];

            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
            }

            if ($category_id) {
                $where[] = ['category_id', '=', $category_id];
            }

            $SelfCategory = SelfCategory::where($where)->get();
            $data = [];
            foreach ($SelfCategory as $k => $v) {
                $shops = SelfShop::where('category_id', $v['category_id'])->get();
                $data1 = $shops->toArray();
                foreach ($data1 as $k1 => $v1) {
                    $data2[]['sku_list'][] = $v1;

                }

                $v['shop_lists'] = $data2;
                $data[] = $v;
            }


            return json_encode([
                'status' => 1,
                'data' => $data,
            ]);
        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

}