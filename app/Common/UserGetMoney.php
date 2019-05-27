<?php

namespace App\Common;


use App\Models\User;
use App\Models\UserWalletDetail;

class UserGetMoney
{

    public $store_id;//门店
    public $out_trade_no;//订单号
    public $order_total_amount;//订单金额
    public $user_id;//直属推广员ID
    public $ways_type;//通道类型
    public $store_ways_type_rate;//门店通道类型费率
    public $source_type;//返佣类型
    public $source_desc;//返佣类型说明
    public $config_id;//返佣类型说明


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {


        $this->user_id = $data['user_id'];
        $this->store_id = $data['store_id'];
        $this->out_trade_no = $data['out_trade_no'];
        $this->ways_type = $data['ways_type'];
        $this->order_total_amount = $data['order_total_amount'];
        $this->store_ways_type_rate = $data['store_ways_type_rate'];
        $this->source_type = $data['source_type'];
        $this->source_desc = $data['source_desc'];
        $this->config_id = $data['config_id'];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function insert()
    {
        try {

            $now_user_id = $this->user_id;//商户直接归属的代理商id
            $user = User::join('user_rates', 'users.id', 'user_rates.user_id')
                ->where('users.id', $now_user_id)
                ->where('user_rates.ways_type', $this->ways_type)
                ->select('user_rates.rate', 'user_rates.rate_a', 'user_rates.rate_e', 'users.pid', 'users.level', 'users.name', 'users.phone')
                ->first();

            if ($user) {
                $pid_user_id = $user->pid;
                $level = $user->level;//商户直接归属的代理商等级

                //支付宝微信扫码费率
                $user_rate = $user->rate;

                //银联扫码
                if (in_array($this->ways_type, [6004, 8004])) {
                    $user_rate = $user->rate_a;//小于1000
                    //大于1000
                    if ($this->order_total_amount > 1000) {
                        $user_rate = $user->rate_c;
                    }
                }


                //银联刷卡
                if (in_array($this->ways_type, [6005, 8005])) {
                    $user_rate = $user->rate_e;
                }

                $name = $user->name;
                $phone = $user->phone;

                $money = $this->order_total_amount * ($this->store_ways_type_rate - $user_rate);//直属代理商
                $money = $money / 100;
                $money = number_format($money, 4, '.', '');
                $store_id = $this->store_id;
                $config_id = $this->config_id;
                $out_trade_no = $this->out_trade_no;
                $source_type = $this->source_type;
                $source_desc = $this->source_desc;

                if ($money > 0) {

                }
                $user_detail = [
                    'store_id' => $store_id,
                    'config_id' => $config_id,
                    'user_id' => $now_user_id,
                    'user_name' => $name,
                    'phone' => $phone,
                    'money' => $money,
                    'out_trade_no' => $out_trade_no,
                    'trade_no' => $out_trade_no,
                    'source_type' => $source_type,
                    'source_desc' => $source_desc,
                ];

                //直属入库

                if ($money > 0) {
                    UserWalletDetail::create($user_detail);
                }
                $sub_user_rate = $user_rate;

                for ($x = $level; $x >= 0; $x--) {

                    try {
                        $user = User::join('user_rates', 'users.id', 'user_rates.user_id')
                            ->where('users.id', $pid_user_id)
                            ->where('user_rates.ways_type', $this->ways_type)
                            ->select('user_rates.rate', 'user_rates.rate_a', 'user_rates.rate_e', 'users.id', 'users.pid', 'users.level', 'users.name', 'users.phone')
                            ->first();
                        if (!$user) {
                            continue;
                        }
                        //直到循环到顶级
                        if ($user->pid == 0) {
                            break;
                        }


                        $pid_user_id = $user->pid;//上级ID

                        //支付宝微信扫码费率
                        $user_rate = $user->rate;

                        //银联扫码
                        if (in_array($this->ways_type, [6004, 8004])) {
                            $user_rate = $user->rate_a;//小于1000
                            //大于1000
                            if ($this->order_total_amount > 1000) {
                                $user_rate = $user->rate_c;
                            }
                        }


                        //银联刷卡
                        if (in_array($this->ways_type, [6005, 8005])) {
                            $user_rate = $user->rate_e;
                        }


                        $name = $user->name;
                        $phone = $user->phone;
                        $now_user_id = $user->id;
                        $money = $this->order_total_amount * ($sub_user_rate - $user_rate);//直属代理商
                        $money = $money / 100;
                        $money = number_format($money, 4, '.', '');

                        $user_detail = [
                            'store_id' => $store_id,
                            'config_id' => $config_id,
                            'user_id' => $now_user_id,
                            'user_name' => $name,
                            'phone' => $phone,
                            'money' => $money,
                            'out_trade_no' => $out_trade_no,
                            'trade_no' => $out_trade_no,
                            'source_type' => $source_type,
                            'source_desc' => $source_desc,
                        ];

                        if ($money > 0) {
                            UserWalletDetail::create($user_detail);
                        }
                        $sub_user_rate = $user_rate;
                    } catch (\Exception $exception) {
                        \Illuminate\Support\Facades\Log::info($exception);
                        continue;
                    }

                }

            }


        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);
        }
    }


}
