<?php

namespace Jdjr\Sdk;
class RSAUtils
{
    public static function encryptByPrivateKey($data, $pi_key)
    {
        $pi_key = openssl_pkey_get_private($pi_key);//这个函数可用来判断私钥是否是可用的，可用返回资源id Resource id
        $encrypted = "";
        openssl_private_encrypt($data, $encrypted, $pi_key, OPENSSL_PKCS1_PADDING);//私钥加密
        $encrypted = base64_encode($encrypted);//加密后的内容通常含有特殊字符，需要编码转换下，在网络间通过url传输时要注意base64编码是否是url安全的
        return $encrypted;
    }

    public static function decryptByPublicKey($data, $pu_key)
    {

        $pu_key = openssl_pkey_get_public($pu_key);//这个函数可用来判断公钥是否是可用的，可用返回资源id Resource id
        $decrypted = "";
        $data = base64_decode($data);
        openssl_public_decrypt($data, $decrypted, $pu_key);//公钥解密
        return $decrypted;
    }
}

?>