<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Zizaco\Entrust\Traits\EntrustUserTrait;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    // use EntrustUserTrait;
    use HasRoles;
    protected $guard_name = 'user.api';

    // use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'config_id',
        's_code',
        's_code_url',
        'sub_code_url',
        'money',
        'settlement_money',
        'unsettlement_money',
        'pid',
        'pid_name',
        'level',
        'level_name',
        'name',
        'logo',
        'phone',
        'email',
        'password',
        'pay_password',
        'remember_token',
        'is_delete',
        'is_admin',
        'wx_openid',
        'province_name',
        'city_name',
        'area_name',
        'address',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pay_password'
    ];

    public function owns($related)
    {
        return $this->id == $related->user_id;
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
            'type' => 'user',
            'user_id' => $this->id,
            'config_id' => $this->config_id,
            's_code' => $this->s_code,
            'pid' => $this->pid,
            'level' => $this->level,
            'name' => $this->name,
            'logo' => $this->logo,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_delete' => $this->is_delete,
            'is_admin' => $this->is_admin,


        ];//返回用户id
    }

    public function childUser()
    {
        return $this->hasMany('App\Models\User', 'pid', 'id');
    }

    public function children()
    {
        return $this->childUser()->with('children');
    }
}
