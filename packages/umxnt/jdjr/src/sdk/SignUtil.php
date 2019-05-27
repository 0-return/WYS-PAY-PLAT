<?php

namespace Jdjr\Sdk;
use Jdjr\Sdk\RSAUtils;

/**
 * 签名
 *
 *
 */
class SignUtil
{

// 	public static $unSignKeyList = array (
// 			"merchantSign",
// 			"version", 
// 			"successCallbackUrl",
// 			"forPayLayerUrl"
// 	);
    public static function signWithoutToHex($params, $unSignKeyList,$pi_key)
    {
        ksort($params);
        $sourceSignString = SignUtil::signString($params, $unSignKeyList);
        //echo  "sourceSignString=".htmlspecialchars($sourceSignString)."<br/>";
        //error_log("=========>sourceSignString:".$sourceSignString, 0);
        $sha256SourceSignString = hash("sha256", $sourceSignString);
        //error_log($sha256SourceSignString, 0);
        //echo "sha256SourceSignString=".htmlspecialchars($sha256SourceSignString)."<br/>";
        return RSAUtils::encryptByPrivateKey($sha256SourceSignString,$pi_key);
    }

    public static function sign($params, $unSignKeyList,$pi_key)
    {
        ksort($params);
        $sourceSignString = SignUtil::signString($params, $unSignKeyList);
        error_log($sourceSignString, 0);
        $sha256SourceSignString = hash("sha256", $sourceSignString);
        error_log($sha256SourceSignString, 0);
        return RSAUtils::encryptByPrivateKey($sha256SourceSignString,$pi_key);
    }

    public static function signString($data, $unSignKeyList)
    {
        $linkStr = "";
        $isFirst = true;
        ksort($data);
        foreach ($data as $key => $value) {
            if ($value == null || $value == "") {
                continue;
            }
            $bool = false;
            foreach ($unSignKeyList as $str) {
                if ($key . "" == $str . "") {
                    $bool = true;
                    break;
                }
            }
            if ($bool) {
                continue;
            }
            if (!$isFirst) {
                $linkStr .= "&";
            }
            $linkStr .= $key . "=" . $value;
            if ($isFirst) {
                $isFirst = false;
            }
        }
        return $linkStr;
    }
}

// $params['currency']="CNY";
// $params['version']="";


// $unSignKeyList = array ("sign");
// echo SignUtil::signWithoutToHex ( $params ,$unSignKeyList);

?>