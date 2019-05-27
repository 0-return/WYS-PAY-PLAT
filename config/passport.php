<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/5/25
 * Time: 下午2:18
 */

return [
    'grant_type' => env('OAUTH_GRANT_TYPE'),
    'client_id' => env('OAUTH_CLIENT_ID'),
    'client_secret' => env('OAUTH_CLIENT_SECRET'),
    'scope' => env('OAUTH_SCOPE', '*'),
];
