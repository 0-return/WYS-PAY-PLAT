<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/9/6
 * Time: 下午12:13
 */

namespace App\Api\Rsa;


class demo
{
    public function demo(){
        /*  //RSA验证

         $data='11111';

         //加密
         $rsaE = new RsaE();
         $p_k=$rsaE->privEncrypt($data);//服务端私钥加密
       var_dump($p_k);
         //解密
         $rsaD = new RsaD();//解密
         $client_public_key = $rsaD->clientPublicKey;//外部客户端公钥
         $rsaD->setClientPublicKey($client_public_key);

         $D=$rsaD->clientPublicDecrypt($p_k);

  dd($D);


         //客户端用的我的公钥加密 我用私钥解密
            $rsa = new RsaE();
            $data=$rsa->privDecrypt($request->get('sign'));//
            parse_str($data, $output);
            $pay_password = $output['pay_password'];
            $pay_password_confirmed =$output['pay_password_confirmed'];


        */
    }

}