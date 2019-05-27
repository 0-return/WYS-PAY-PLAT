<?php

namespace MyBank\Sdk;
class RSACert
{


    /**
     * Created by PhpStorm.
     * User: wangxiaoke
     * Date: 2017/8/30
     * Time: 下午7:48
     */
    function sign($data, $rsaPrivateKeyFilePath, $rsaPrivateKey, $signType = "RSA")
    {
        $CharUtil=new CharUtil();
        if ($CharUtil->checkEmpty($rsaPrivateKeyFilePath)) {
            $priKey = $rsaPrivateKey;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        } else {
            $priKey = file_get_contents($rsaPrivateKeyFilePath);
            $res = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        if (!$CharUtil->checkEmpty($rsaPrivateKeyFilePath)) {
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * RSA单独签名方法，未做字符串处理,字符串处理见getSignContent()
     * @param $data 待签名字符串
     * @param $privatekey 商户私钥，根据keyfromfile来判断是读取字符串还是读取文件，false:填写私钥字符串去回车和空格 true:填写私钥文件路径
     * @param $signType 签名方式，RSA:SHA1     RSA2:SHA256
     * @param $keyfromfile 私钥获取方式，读取字符串还是读文件
     * @return string
     * @author mengyu.wh
     */
    function alonersaSign($data, $privatekey, $signType = "RSA", $keyfromfile = false)
    {
        if (!$keyfromfile) {
            $priKey = $privatekey;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        } else {
            $priKey = file_get_contents($privatekey);
            $res = openssl_get_privatekey($priKey);
        }
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        if ($keyfromfile) {
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    /**
     * 验签
     *
     * @param $data
     * @param $sign
     * @param $rsaPublicKeyFilePath
     * @param $mayibankPublicKey
     * @param $mayibankrsaPublicKey
     * @param string $signType
     * @return bool
     */
    function verify($data, $sign, $rsaPublicKeyFilePath, $mayibankPublicKey, $mayibankrsaPublicKey, $signType = 'RSA')
    {
        $CharUtil=new CharUtil();

        if ($CharUtil->checkEmpty($mayibankPublicKey)) {
            $pubKey = $mayibankrsaPublicKey;
            $res = "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($pubKey, 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";
        } else {
            //读取公钥文件
            $pubKey = file_get_contents($rsaPublicKeyFilePath);
            //转换为openssl格式密钥
            $res = openssl_get_publickey($pubKey);
        }

        ($res) or die('网商银行RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }

        if (!$CharUtil->checkEmpty($mayibankPublicKey)) {
            //释放资源
            openssl_free_key($res);
        }
        return $result;
    }

    function verifynotify($data, $sign, $rsaPublicKeyFilePath, $mayibankPublicKey, $mayibankrsaPublicKey, $signType = 'RSA')
    {
        $CharUtil=new CharUtil();

        if ($CharUtil->checkEmpty($mayibankPublicKey)) {
            $res=  $pubKey = $mayibankrsaPublicKey;
        } else {
            //读取公钥文件
            $pubKey = file_get_contents($rsaPublicKeyFilePath);
            //转换为openssl格式密钥
            $res = openssl_get_publickey($pubKey);
        }

        ($res) or die('网商银行RSA公钥错误。请检查公钥文件格式是否正确');

        //调用openssl内置方法验签，返回bool值
        if ("RSA2" == $signType) {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        } else {
            $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        }

        if (!$CharUtil->checkEmpty($mayibankPublicKey)) {
            //释放资源
            openssl_free_key($res);
        }
        return $result;
    }

    /**
     *  在使用本方法前，必须初始化AopClient且传入公私钥参数。
     *  公钥是否是读取字符串还是读取文件，是根据初始化传入的值判断的。
     **/
    function rsaEncrypt($data, $rsaPublicKeyFilePath, $mayibankPublicKey, $mayibankrsaPublicKey, $rsaPublicKeyPem, $charset)
    {
        $CharUtil=new CharUtil();

        if ($CharUtil->checkEmpty($mayibankPublicKey)) {
            //读取字符串
            $pubKey = $mayibankrsaPublicKey;
            $res = "-----BEGIN PUBLIC KEY-----\n" .
                wordwrap($pubKey, 64, "\n", true) .
                "\n-----END PUBLIC KEY-----";
        } else {
            //读取公钥文件
            $pubKey = file_get_contents($rsaPublicKeyFilePath);
            //转换为openssl格式密钥
            $res = openssl_get_publickey($pubKey);
        }

        ($res) or die('网商银行RSA公钥错误。请检查公钥文件格式是否正确');
        $blocks = $CharUtil->splitCN($data, 0, 30, $charset);
        $chrtext  = null;
        $encodes  = array();
        foreach ($blocks as $n => $block) {
            if (!openssl_public_encrypt($block, $chrtext , $res)) {
                echo "<br/>" . openssl_error_string() . "<br/>";
            }
            $encodes[] = $chrtext ;
        }
        $chrtext = implode(",", $encodes);
        return base64_encode($chrtext);
    }

    /**
     *  在使用本方法前，必须初始化AopClient且传入公私钥参数。
     *  公钥是否是读取字符串还是读取文件，是根据初始化传入的值判断的。
     **/
    function rsaDecrypt($data, $rsaPrivateKeyFilePath, $rsaPrivateKey, $rsaPrivateKeyPem, $charset)
    {
        $CharUtil=new CharUtil();

        if ($CharUtil->checkEmpty($rsaPrivateKeyFilePath)) {
            //读字符串
            $priKey = $rsaPrivateKey;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        } else {
            $priKey = file_get_contents($rsaPrivateKeyFilePath);
            $res = openssl_get_privatekey($priKey);
        }
        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');
        //转换为openssl格式密钥
        $decodes = explode(',', $data);
        $strnull = "";
        $dcyCont = "";
        foreach ($decodes as $n => $decode) {
            if (!openssl_private_decrypt($decode, $dcyCont, $res)) {
                echo "<br/>" . openssl_error_string() . "<br/>";
            }
            $strnull .= $dcyCont;
        }
        return $strnull;
    }

}