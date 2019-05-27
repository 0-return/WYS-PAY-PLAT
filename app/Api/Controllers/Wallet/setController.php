<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/1/25
 * Time: 3:13 PM
 */

namespace App\Api\Controllers\Wallet;


use App\Api\Controllers\BaseController;
use App\Models\SettlementConfig;
use Illuminate\Http\Request;

class setController extends BaseController
{


    //设置提现
    public function settlement_configs(Request $request)
    {

        try {
            $public = $this->parseToken();
            $user_id = $public->user_id;
            $config_id = $public->config_id;
            $data = $request->except(['token']);
            $level = $public->level;
            if ($level > 0) {
                return json_encode([
                    'status' => 2,
                    'message' => '暂无权限'
                ]);
            }

            $dx = $request->get('dx', '');
            $s_amount = $request->get('s_amount', '');
            $e_amount = $request->get('e_amount', '');
            $sxf_amount = $request->get('sxf_amount', '');
            $tx_remark = $request->get('tx_remark', '');
            $out_type = $request->get('out_type', '');

            $SettlementConfig = SettlementConfig::where('config_id', $config_id)
                ->where('dx', $dx)
                ->first();

            if ($dx && $s_amount == "" && $out_type == "" && $sxf_amount == "" && $e_amount == "" && $tx_remark == "") {
                if (!$SettlementConfig) {
                    $SettlementConfig = [];
                } else {

                    if ($SettlementConfig->out_type == "1") {
                        $SettlementConfig->alipay_out_qr_url = url('/merchant/appAlipay?store_id=' . $config_id . '&merchant_id=' . $config_id . "&config_id=" . $config_id);

                    }

                }

                $this->status = 1;
                $this->message = '数据返回成功';
                return $this->format($SettlementConfig);
            }


            //修改
            $check_data = [
                'dx' => '付款金额',
                's_amount' => '最小提现金额',
                'e_amount' => '最大提现金额',
                'sxf_amount' => '提现手续费',
                'tx_remark' => '转出备注',
                'out_type' => '转出账户',

            ];


            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $data['config_id'] = $config_id;

            if ($SettlementConfig) {
                $SettlementConfig->update($data);
                $SettlementConfig->save();
            } else {
                SettlementConfig::create($data);
            }

            $this->status = 1;
            $this->message = '保存成功';
            return $this->format($data);

        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }

    }

}