<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/9/5
 * Time: 下午6:48
 */

namespace App\Api\Rsa;


class RsaD
{
    private static $PRIVATE_KEY = '-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEA6/sCEY8PIhzfPMhsRaBlgZUhq9F2gokObxhPZ1o8n2MJ7Cgr
PcUg6AR1bA9AbnL4QLxvpGqdh6GbeZC0AH6zU9MbzkFuEv1mUuK9w4uae755+t+i
yAw30SJzdWf2DYGHLnVXyT69EFgBvey3SJ9vCJR3TQBFOePnA4DAJeGO3zjhHAkU
+Wel35HWmG3qGbLDQYfbQURyBRKxG0Od7HvizCwaMaIyUJ0x78aU7B0anQrs2kQm
l3HUtILzmHsVQQUTwqohkYfiZJkj7+/hE++kKZtPjC96Yp06AOJJOeHGxDdTecpV
RmOKo1ALKjz/RmxxlGQo14oPxKYXuBmiNjIQPQIDAQABAoIBAGrTgqg4Pv2OXHDD
ul/6sHjs7gU+GYwWR3Z7Zta+vtrYltFVjd20s6TU/+MfNGfLnB6SL2ga651Ox3dM
zm+666ty0g+ZBx+Jnxy+kHFJbXG/VLEBNEujXFFMa0AnA/gxPuUFMexkfmo7rO4x
jvdNVZJow2kUSkJerWGkk1eSuH7L/l0TfYHb4Ir4/TV6mY8NjW7fj1z+siUfu7KS
fQjJM1ypb/Lyo1nunYfJGEkMCejS43P7yiptDt95M2YT0IxTRUcmM+zma5OIImPZ
zNn1Hlk+CXhbldVVcuJCELQzL01IjfoSlXTgXEAxPyH0/ynWVwUMkl11piEkvrzX
6MTMBIECgYEA/MMGfE6v3r/+BVNnXaouE+HfOogd4J3DNaj+RCNVY8QMhxyxUyvD
pcXMe6e6cv+T/al4/GD26GrlEL/kfMkWkjh3lnpcF2Pt8MceCJtQqi06PVVB0EGW
PdJOxw2OUVpDCAJLHyI7xMP5DVPfdiSMnVSAJOFImmCjLCA7BK4sAvUCgYEA7wDy
E2RCnLbGloDXnbapeWNX4JJfSLdEJ3NkG3UWGaTrYdM8Z+N+z7lmX2I9Ae8nydH8
kn+6dFehGxPkcH+hUOMLV4hKv8SvA7N3Tk76gimxx28KiPOuLOK0ELx/VPcoWaoX
bZPUydY/1m2Bu4jjuNCvl39Q2x4grGqht2lm2ykCgYEAyXpr4QqYBebkhTpGWtMc
h5y0Y+O8bR9US6G5jHbdyfisQ8cLUlDAU5Onu6mnZaN0Q+6jEgn4xqrujLtpVk60
PznjmX9PiRWOxS7zMckcM4p5sgoTu/2L1Ruez+xuVUqtw+SQHPEc02ujSoxgw7u+
mmqQ+tTZGrWNW88VPFXZn0ECgYBdWTQziLxPsz+7NtWAPwDQbZG8H89helr/QZ1+
7+tS1swsqDF2ri8weMxYQulrIPqcZAzPN14e5L8C8XEO03qxOgyLAquXXP2yZ10Y
09YaEqjiKSenN+32kBEeXFErYWF8K+f8n3nD34+Nc7XxBlVTMQb0GAD/pDPEw90n
4t4OiQKBgQDjIKllWM7cAxmjQyiCNiurFSYuSzsqm4EMC1Snb278pL/WRgaw3Db7
sGSX6EeaQrRR5hBNwp0DlC9V3wbUBrik3kYpIDZR8RkJlOUdkK3/e1XCGBobVLmL
ke1IBA8vzgfWFc9Hsgeo2tByrT4/U1xGkNxMA+SUlCTSVh1VVNVeug==
-----END RSA PRIVATE KEY-----
';

    private static $PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA6/sCEY8PIhzfPMhsRaBl
gZUhq9F2gokObxhPZ1o8n2MJ7CgrPcUg6AR1bA9AbnL4QLxvpGqdh6GbeZC0AH6z
U9MbzkFuEv1mUuK9w4uae755+t+iyAw30SJzdWf2DYGHLnVXyT69EFgBvey3SJ9v
CJR3TQBFOePnA4DAJeGO3zjhHAkU+Wel35HWmG3qGbLDQYfbQURyBRKxG0Od7Hvi
zCwaMaIyUJ0x78aU7B0anQrs2kQml3HUtILzmHsVQQUTwqohkYfiZJkj7+/hE++k
KZtPjC96Yp06AOJJOeHGxDdTecpVRmOKo1ALKjz/RmxxlGQo14oPxKYXuBmiNjIQ
PQIDAQAB
-----END PUBLIC KEY-----
';
    public $clientPublicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4COVutRbOUfQNjvVOzwK
49NzHIPRwwksnJ6QtdHwGmdUZiT2HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS
0lfeBMqUcm+bY7aZ54wClm75RI90uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Yc
r8S8O004ExtvQqu2QCl7Aai/WC4URIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx
37Y9AMR0V94R13v4BFlMQDG+2REVJsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzL
PYV0jsiit2AUkyURRLxEaZm2C0YYhfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DX
kQIDAQAB
-----END PUBLIC KEY-----';

    /**
     * 设置客户端的公钥
     */
    public function setClientPublicKey($cPubKey)
    {
        $this->clientPublicKey = $cPubKey;
    }

    /**
     *获取服务端私钥
     */
    private static function getPrivateKey()
    {
        $privKey = self::$PRIVATE_KEY;
        $passphrase = '';
        return openssl_pkey_get_private($privKey, $passphrase);
    }

    /**
     * 服务端公钥加密数据
     * @param unknown $data
     */
    public static function publEncrypt($data)
    {
        $publKey = self::$PUBLIC_KEY;
        $publickey = openssl_pkey_get_public($publKey);
        //使用公钥进行加密
        $encryptData = '';
        openssl_public_encrypt($data, $encryptData, $publickey);
        return base64_encode($encryptData);
    }

    /**
     * 服务端私钥加密
     */
    public static function privEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data, $encrypted, self::getPrivateKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 服务端私钥解密
     */
    public static function privDecrypt($encrypted)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $privatekey = self::getPrivateKey();
        $sensitivData = '';
        //return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey()))? $decrypted : null;
        openssl_private_decrypt(base64_decode($encrypted), $sensitivData, $privatekey);
        //var_dump($sensitivData);
        return $sensitivData;
    }

    /**
     * 客户端的公钥解密数据
     * @param unknown $publicKey
     * @param unknown $encryptString
     */
    public function clientPublicDecrypt($encryptString)
    {
        if (!is_string($encryptString)) return null;
        $encodeKey = $this->clientPublicKey;
        $publicKey = openssl_pkey_get_public($encodeKey);
        if (!$publicKey) {
            exit("\nClient Publickey Can not used");
        }
        $sensitivData = '';
        openssl_public_decrypt(base64_decode($encryptString), $sensitivData, $publicKey);
        return $sensitivData;
    }

    /**
     * 客户端公钥加密数据
     * @param string $string 需要加密的字符串
     * @return string Base64编码的密文
     */
    public function clientPublicEncrypt($string)
    {
        $publKey = $this->clientPublicKey;
        $publicKey = openssl_pkey_get_public($publKey);
        if (!$publicKey) {
            exit("\nClient Publickey Can not used");
        }
        //使用公钥进行加密
        $encryptData = '';
        openssl_public_encrypt($string, $encryptData, $publicKey);
        return base64_encode($encryptData);
    }

    public function formatKey($key, $type = 'public')
    {
        if ($type == 'public') {
            $begin = "-----BEGIN PUBLIC KEY-----\n";
            $end = "-----END PUBLIC KEY-----";
        } else {
            $begin = "-----BEGIN PRIVATE KEY-----\n";
            $end = "-----END PRIVATE KEY-----";
        }
        //$key = ereg_replace("\s", "", $key);
        $key = preg_replace('/\s/', '', $key);
        $str = $begin;
        $str .= substr($key, 0, 64);
        $str .= "\n" . substr($key, 64, 64);
        $str .= "\n" . substr($key, 128, 64);
        $str .= "\n" . substr($key, 192, 24);
        $str .= "\n" . $end;
        return $str;
    }

}