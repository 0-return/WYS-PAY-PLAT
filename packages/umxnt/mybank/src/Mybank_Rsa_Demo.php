<?php
/**
 * Created by PhpStorm.
 * User: wangxiaoke
 * Date: 2017/8/30
 * Time: 下午8:31
 */
require_once 'MybankSdk.php';

$partner_public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDMJK912mUFpQIeoIImrk7jWqWU+WXHcYiGNLRxmSyw8VYoJ1rq1nwq8T7G16xQ5a7Z80BUw4O8MPIN/Yu29tsjWwT4rB6iiy4GWVu05CoWRVPLBJU+ePqhpRlHRnGCUK/gNckPhpl+PC7bJvVscxINGk1QOdRJEU5HxjGPiOheGQIDAQAB';
$partner_private_key = 'MIICXQIBAAKBgQDMJK912mUFpQIeoIImrk7jWqWU+WXHcYiGNLRxmSyw8VYoJ1rq1nwq8T7G16xQ5a7Z80BUw4O8MPIN/Yu29tsjWwT4rB6iiy4GWVu05CoWRVPLBJU+ePqhpRlHRnGCUK/gNckPhpl+PC7bJvVscxINGk1QOdRJEU5HxjGPiOheGQIDAQABAoGAd7HC1x0YQnj/hymhZkCprJCHqZOZY5lj7RyV+E1RcOXwGFcK7cqYvyz4G+p09HHXVZ9Uxt5kLUGdiypwcvTGgyZLoG3VHK2AqQxQ09W+vhyWh+oNbbsVYv+i2IIZv/k5xSPljDQYONsruA413OtAnJ5O9Gyv515oHn+or+NnECkCQQDoXvwOEYRd9+Yee2BJTYYUYvwSxwmKIMb1kHregRApTmg8xyRjMyAQqHu1tcmUesKp4weY9LV43FuhYrgVi3hTAkEA4ObieouFsGgx6w0ZY0ub6F9G4ti/bCighKvM61ru7fRktTxHQGraEEJv86Ckm4hoi8JutjCnlHN9GbbY+BESYwJBAMSrf9G4abvOkOnDql7gSlb+4DJUW3KZA0vbmOKxgag0QG0Qb2+2WbE/qFTHI3GT5SY8jLWch+tfNf6FuyAhBxkCQAqlIwcl33gQFnzHq/H1PDXtfI90LutRqPdeol5peXGt5a5mYgD8jcHDQ0VGz0PFWx1yYIcjGmt+Y+d5rh9fv30CQQCBcOKhDIEy9aqxvuaEuNUvcduK9ENqATRDx5et0sev8WoN364e/TX8qktH5SDvTqqaDU6JgQVXTjfT5fNiy4+/';

$a = array("Bnimal" => "horse", "Animal" => "是嘛", "Type" => "mammal");
$unsign = getOrderSignContent($a, "UTF-8");
$sign = sign($unsign, null, $partner_private_key, "RSA");
$isVerify = verify($unsign, $sign, null, null, $partner_public_key, "RSA");
print_r($isVerify);
?>