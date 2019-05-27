<?php
namespace MyBank\Sdk;
/**
 * Created by PhpStorm.
 * User: wangxiaoke
 * Date: 2017/8/30
 * Time: 下午6:45
 */

/**
 * 校验$value是否非空
 *  if not set ,return true;
 *    if is null , return true;
 **/
class CharUtil
{
    public  function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
   public function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = Constant::$fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }

  public  function splitCN($cont, $n = 0, $subnum, $charset)
    {
        //$len = strlen($cont) / 3;
        $arrr = array();
        for ($i = $n; $i < strlen($cont); $i += $subnum) {
            $res = subCNchar($cont, $i, $subnum, $charset);
            if (!empty ($res)) {
                $arrr[] = $res;
            }
        }
        return $arrr;
    }

  public  function subCNchar($str, $start = 0, $length, $charset = "gbk")
    {
        if (strlen($str) <= $length) {
            return $str;
        }
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
        return $slice;
    }

}