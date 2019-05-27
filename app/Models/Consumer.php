<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Consumer extends Authenticatable implements JWTSubject
{
    use Notifiable;
    public $store_id;
    protected $fillable = [
        'type',
        'name',
        'phone',
        'logo',
        'device_type',
        'device_info',
        'password',
        'pay_password',
        'email',
        'remember_token',
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
            'type' => 'consumer',
            'name' => $this->name,
            'phone' => $this->phone,
        ];//返回用户id
    }

}
