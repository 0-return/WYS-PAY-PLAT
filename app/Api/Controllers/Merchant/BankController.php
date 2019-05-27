<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/12
 * Time: 下午3:46
 */

namespace App\Api\Controllers\Merchant;


use App\Api\Controllers\BaseController;
use App\Models\ProvinceCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankController extends BaseController


{
    //查询银行信息
    public function sub_bank(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $bank_name = $request->get('bank_name', '');
            $sub_bank_keyword = $request->get('sub_bank_keyword', '');
            $bank_area_name = $request->get('bank_area_name', '');
            $bank_city_name = $request->get('bank_city_name', '');
            $bank_province_name = $request->get('bank_province_name', '');

            $bank_province_name = str_replace('省', '', $bank_province_name);
            $bank_city_name = str_replace('市', '', $bank_city_name);
            $bank_area_name = str_replace('区', '', $bank_area_name);

            $obj = DB::table('bank_info');

            if ($sub_bank_keyword) {
                $obj = $obj
                    ->where('bankName', 'like', '%' . $bank_name . '%' . '%' . $sub_bank_keyword . '%')
                    ->select('instOutCode  as bank_no', 'bankName as sub_bank_name');
            } else {
                $obj = $obj
                    ->orWhere('bankName', 'like', $bank_name . $bank_province_name . '%')
                    ->orWhere('bankName', 'like', $bank_name . $bank_city_name . '%')
                    ->orWhere('bankName', 'like', $bank_name . $bank_area_name . '%')
                    ->orWhere('bankName', 'like', $bank_province_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', $bank_city_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', $bank_area_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . $bank_province_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . $bank_city_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . $bank_area_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_province_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_city_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_area_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . '%' . $bank_province_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . '%' . $bank_city_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . '%' . $bank_area_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_province_name . '%' . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_city_name . '%' . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_area_name . '%' . $bank_name . '%')
                    ->select('instOutCode as bank_no', 'bankName as sub_bank_name');


            }






            $this->t = $obj->count();

            $data = $this->page($obj)->get();


            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    public function searchArray($fields = [])
    {
        $results = [];
        if (is_array($fields)) {
            foreach ($fields as $field => $operator) {
                if (request()->has($field) && $value = $this->checkParam($field, '', false)) {
                    $results[$field] = [$field, $operator, "%{$value}%"];
                }
            }
        }
        return $results;
    }

    public function getCityName($areaCode)
    {
        $areaName = '';
        $ProvinceCity = ProvinceCity::where('areaCode', $areaCode)
            ->select('areaName')
            ->first();
        if ($ProvinceCity) {
            $areaName = $ProvinceCity->areaName;
        }
        return [
            'areaName' => $areaName,
        ];
    }

}