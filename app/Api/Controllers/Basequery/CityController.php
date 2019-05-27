<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/9/1
 * Time: 下午5:20
 */

namespace App\Api\Controllers\Basequery;


use App\Api\Controllers\BaseController;
use App\Models\ProvinceCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CityController extends BaseController
{

    public function city(Request $request)
    {
        $areaCode = $request->get('area_code', 1);//编号
        $city = Cache::get('city_' . $areaCode);
        if (!$city) {
            $city = ProvinceCity::where('area_parent_id', $areaCode)->select('area_code', 'area_name', 'area_parent_id', 'area_type')->get();
            Cache::put('city_' . $areaCode, $city, 1000000);
        }

        $this->status = 1;
        $this->meassage = '数据请求成功';
        return $this->format($city);
    }

}