<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Merchant extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasRoles;
    protected $guard_name = 'merchant.api';

    public $store_id;
    protected $fillable = [
        'config_id',
        'user_id',
        'wx_openid',
        'wxapp_openid',
        'money',
        'settlement_money',
        'unsettlement_money',
        'pid',
        'type',
        'name',
        'phone',
        'jpush_id',
        'logo',
        'device_type',
        'device_info',
        'is_close',
        'password',
        'pay_password',
        'email',
        'remember_token',
        'wx_logo',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pay_password'
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getUserId();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getUserId()
    {
        return [
            'type' => 'merchant',
            'merchant_id' => $this->id,
            'merchant_name' => $this->name,
            'config_id' => $this->config_id,
            'pid' => $this->pid,
            'phone' => $this->phone,
            'imei' => $this->imei,
            'user_id' => $this->user_id,
            'merchant_type' => $this->type,
            'email' => $this->email,
            'created_store_no' => date('Ymdhis', time()) . rand(1000, 9999),//预创建门店专用
            // 'store' => $this->getStore(),//这个人的id;
        ];//返回用户id
    }


    /**查询门店
     * @return string
     */
    public
    function getStore()
    {
        $store = [
            'store_id' => '',
            'id' => '',
            'pid' => '',
        ];
        $data = MerchantStore::where('merchant_id', $this->id)
            ->orderBy('created_at', 'asc')
            ->select('store_id')
            ->first();
        if ($data) {
            $store = Store::where('store_id', $data->store_id)
                ->select('id', 'pid')
                ->first();
            if ($store) {
                $store = [
                    'store_id' => $data->store_id,
                    'id' => $store->id,
                    'pid' => $store->pid,
                ];
            }
        }
        return $store;
    }

    public
    function getMerchantId()
    {
        $MerchantId = '';
        return $MerchantId;
    }


    public
    function childMerchant()
    {
        return $this->hasMany('App\Merchant', 'pid', 'id');
    }

    public
    function allChildrenMerchants()
    {
        return $this->childMerchant()->with('allChildrenMerchants');
    }


}
