<?php

namespace MyBank\Sdk;


class ParamUtil
{


    /**
     * Created by PhpStorm.
     * User: wangxiaoke
     * Date: 2017/8/30
     * Time: 下午6:38
     */

    function getOrderSignContent($params, $postCharset)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        $CharUtil=new CharUtil();
        foreach ($params as $k => $v) {
            if (false ===$CharUtil->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $CharUtil->characet($v, $postCharset);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }

//此方法对value做urlencode
    function getOrderSignContentUrlencode($params, $postCharset)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        $CharUtil=new CharUtil();
        foreach ($params as $k => $v) {
            if (false === $CharUtil->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $CharUtil->characet($v, $postCharset);
                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . urlencode($v);
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . urlencode($v);
                }
                $i++;
            }
        }
        unset ($k, $v);
        return $stringToBeSigned;
    }


    /**
     * @param $request
     * @param $responseContent
     * @return null
     */
    function parserXMLSignSource($request, $responseContent)
    {
        $apiName = $request->getApiMethodName();
        $rootNodeName = str_replace(".", "_", $apiName) . Constant::$RESPONSE_SUFFIX;
        $rootIndex = strpos($responseContent, $rootNodeName);
        $errorIndex = strpos($responseContent, Constant::$ERROR_RESPONSE);

        if ($rootIndex > 0) {
            return $this->parserXMLSource($responseContent, $rootNodeName, $rootIndex);
        } else if ($errorIndex > 0) {
            return $this->parserXMLSource($responseContent, Constant::$ERROR_RESPONSE, $errorIndex);
        } else {
            return null;
        }
    }


    /**
     * @param $responseContent
     * @param $nodeName
     * @param $nodeIndex
     * @return bool|null|string
     */
    function parserXMLSource($responseContent, $nodeName, $nodeIndex)
    {
        $signDataStartIndex = $nodeIndex + strlen($nodeName) + 1;
        $signIndex = strpos($responseContent, "<" . Constant::$SIGN_NODE_NAME . ">");
        // 签名前-逗号
        $signDataEndIndex = $signIndex - 1;
        $indexLen = $signDataEndIndex - $signDataStartIndex + 1;

        if ($indexLen < 0) {
            return null;
        }
        return substr($responseContent, $signDataStartIndex, $indexLen);
    }

    /**
     * @param $responseContent
     * @return bool|null|string
     */
    function parserXMLSign($responseContent)
    {
        $signNodeName = "<" . Constant::$SIGN_NODE_NAME . ">";
        $signEndNodeName = "</" . Constant::$SIGN_NODE_NAME . ">";
        $indexOfSignNode = strpos($responseContent, $signNodeName);
        $indexOfSignEndNode = strpos($responseContent, $signEndNodeName);
        if ($indexOfSignNode < 0 || $indexOfSignEndNode < 0) {
            return null;
        }
        $nodeIndex = ($indexOfSignNode + strlen($signNodeName));
        $indexLen = $indexOfSignEndNode - $nodeIndex;
        if ($indexLen < 0) {
            return null;
        }
        // 签名
        return substr($responseContent, $nodeIndex, $indexLen);
    }

}