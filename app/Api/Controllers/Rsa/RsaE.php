<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/9/5
 * Time: 下午6:48
 */

namespace App\Api\Rsa;


class RsaE
{
    private static $PRIVATE_KEY = '-----BEGIN RSA PRIVATE KEY-----
MIIEowIBAAKCAQEA4COVutRbOUfQNjvVOzwK49NzHIPRwwksnJ6QtdHwGmdUZiT2
HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS0lfeBMqUcm+bY7aZ54wClm75RI90
uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Ycr8S8O004ExtvQqu2QCl7Aai/WC4U
RIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx37Y9AMR0V94R13v4BFlMQDG+2REV
Jsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzLPYV0jsiit2AUkyURRLxEaZm2C0YY
hfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DXkQIDAQABAoIBAFP5wR1wdoUtfLr1
C9zXkBTvog8q7Cowg8BiQEF+2IBr+lkSvAs4CYFVpDTLX4orDcNoQ1lifSIAjAAr
snw0t/kK5qnQ5HFDk2p2fY8dC9Mk3MttHASJcMEmAD4Uu5mJqLZx1n0QmYoypGGC
69iGtLS99vAY7EtsWfEWuH+tiuKBjOXZWfkV7yTuum9mMjzgseGNzlo5vPsVLmwj
QvwuEwqg10EhUoqqm2n4oYyspu0uKnLYo8LGIhkodJwGyitX6Rt/qIChgGY8tDer
ZD16NqLcPg3C8aG2LVFmSCO/mkF4MAWUAZHdhpN51suKpSRSrte3qqt12wzVBvsT
vzjVdmkCgYEA/xD9aQa10U0lhnp5kDIJpCG6rzaTs/a1PILVlG2immTWv732xAy0
Omgph3aeMG29T1nnQVek3S5uayyt+qrNC/oKS11qsVXSdoVWtNHeDQetNi0B6RGq
Wy9EAnTiYJ53+z88S6LS5fXPDyN8tcj3t7PtkWqF9T4QjuzKvwzq7CcCgYEA4PWd
T0eT7XB3TsNGClbgol+22qDRSpvjp2KE1XAgJ1tN5uBq798YrfRGHwI6mhqCCO+g
ssPMtpQp8Tsx8GaPkexstsPdq0qUM/2EsC/2dlfYW/iL8yEihOj7S2L8S6q+dWdt
Vfg/H2mfcCUNuYiKSv9WuG31V/FTsBLm+VC9mYcCgYBNn/UPRoyE4y6da56dZK0M
d3tiIYD4Dwf/H24ymt8Wj8PPXNfBuIANGnAxGsdvw6YOhTTc7Phum9fc5B8an2qB
z5ncb9StnYnMqi3GH+ytGH39c9sV/FtVHuBawwm2D+RB4W/PMQFwHMvkNo+Yn03M
aYTOcZXNGhNd+/CEDkFclwKBgQCugRkUZLv1liaWrJfqcVYz3vejRNjVfXPtZlkQ
kLgAj60wiamqhW9JkZHLgBkhbaqtb+VChuyIPQsEHB0zFPwOAE6cv/d2ZpXsdp61
ZZ9UUfR986Hsaimy3GADLLf1om+39xEzfSzKG08Y2UV6RNayMrx8uJ93Jrb6gM83
W1CYhQKBgDcEyWUVpDla5aLqZXT0mQC+mr3mKzyDx7lJD08QKgPoby3Yh5fKbSer
6exujhkS5FWC0JhquJ11lbp+xLqCpHmCMt5hbIv5My7W9K844+NnCzfE3HEUbnoV
9s17qSkDVdYgcL1zmEh1Hvn5+gGfkX/CERl1Jw91NgXleiHWXAsO
-----END RSA PRIVATE KEY-----
';

    private static $PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4COVutRbOUfQNjvVOzwK
49NzHIPRwwksnJ6QtdHwGmdUZiT2HZxVwfotcOjA5aY16D/2Ahq3gLH4yu2y42dS
0lfeBMqUcm+bY7aZ54wClm75RI90uc54F8IgMkNz8J/VS9LYI/B4uHVsc+4KK4Yc
r8S8O004ExtvQqu2QCl7Aai/WC4URIdCyNm8La2axoA1jjj3SzpytLvP6Z/iHSlx
37Y9AMR0V94R13v4BFlMQDG+2REVJsk6LCyzHQfUvJlnsyKey0n/v8DLC070lQzL
PYV0jsiit2AUkyURRLxEaZm2C0YYhfrGjl+x8n/kDteZbDVcyn7UsEdSicijv9DX
kQIDAQAB
-----END PUBLIC KEY-----
';
    public $clientPublicKey = '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA6/sCEY8PIhzfPMhsRaBl
gZUhq9F2gokObxhPZ1o8n2MJ7CgrPcUg6AR1bA9AbnL4QLxvpGqdh6GbeZC0AH6z
U9MbzkFuEv1mUuK9w4uae755+t+iyAw30SJzdWf2DYGHLnVXyT69EFgBvey3SJ9v
CJR3TQBFOePnA4DAJeGO3zjhHAkU+Wel35HWmG3qGbLDQYfbQURyBRKxG0Od7Hvi
zCwaMaIyUJ0x78aU7B0anQrs2kQml3HUtILzmHsVQQUTwqohkYfiZJkj7+/hE++k
KZtPjC96Yp06AOJJOeHGxDdTecpVRmOKo1ALKjz/RmxxlGQo14oPxKYXuBmiNjIQ
PQIDAQAB
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