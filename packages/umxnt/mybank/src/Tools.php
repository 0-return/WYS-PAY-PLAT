<?php
/**
 */

namespace MyBank;

class Tools
{
    // 表单提交字符集编码
    public $postCharset = "UTF-8";

    /*

        任意数组层数，返回xml格式
    */
    public static function makeXml($data)
    {
        $xml = self::_makeXml($data);
        return '<?xml version="1.0" encoding="UTF-8" ?>' . $xml;
    }

    public static function _makeXml($data)
    {
        $str = '';
        foreach ($data as $k => $v) {
            $str .= "<{$k}>";
            if (is_array($v)) {
                $str .= self::_makeXml($v);
            } else {
                // $str.="<![CDATA[{$v}]]>";
                $str .= "{$v}";
            }
            $str .= "</{$k}>";
        }
        return $str;

    }

    public function xmlToArray($xml)
    {

        libxml_disable_entity_loader(true);

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $val = json_decode(json_encode($xmlstring), true);

        return $val;

    }


    /*
        将键值对数组，生成xml数据
    */
    public static function toXml($array)
    {
        $xml = '<xml>';
        foreach ($array as $k => $v) {
            $xml .= '<' . $k . '><![CDATA[' . $v . ']]></' . $k . '>';
        }
        $xml .= '</xml>';
        return $xml;
    }

    //设置原始内容
    static function setContent($content)
    {
        $xml = simplexml_load_string($content);
        $encode = self::getXmlEncode($content);

        $result = [];
        if ($xml && $xml->children()) {
            foreach ($xml->children() as $node) {
                //有子节点
                if ($node->children()) {
                    $k = $node->getName();
                    $nodeXml = $node->asXML();
                    $v = substr($nodeXml, strlen($k) + 2, strlen($nodeXml) - 2 * strlen($k) - 5);

                } else {
                    $k = $node->getName();
                    $v = (string)$node;
                }

                if ($encode != "" && $encode != "UTF-8") {
                    $k = iconv("UTF-8", $encode, $k);
                    $v = iconv("UTF-8", $encode, $v);
                }
                $result[$k] = $v;
                // $this->setParameter($k, $v);
            }
        }
        return $result;
    }


    /*

        解析xml数据为数组形式
    */
    public static function parseXML($xmlSrc)
    {
        if (empty($xmlSrc)) {
            return false;
        }
        $array = array();
        $xml = simplexml_load_string($xmlSrc);
        $encode = self::getXmlEncode($xmlSrc);

        if ($xml && $xml->children()) {
            foreach ($xml->children() as $node) {
                //有子节点
                if ($node->children()) {
                    $k = $node->getName();
                    $nodeXml = $node->asXML();
                    $v = substr($nodeXml, strlen($k) + 2, strlen($nodeXml) - 2 * strlen($k) - 5);

                } else {
                    $k = $node->getName();
                    $v = (string)$node;
                }

                if ($encode != "" && $encode != "UTF-8") {
                    $k = iconv("UTF-8", $encode, $k);
                    $v = iconv("UTF-8", $encode, $v);
                }
                $array[$k] = $v;
            }
        }
        return $array;
    }

    //获取xml编码
    static function getXmlEncode($xml)
    {
        $ret = preg_match("/<?xml[^>]* encoding=\"(.*)\"[^>]* ?>/i", $xml, $arr);
        if ($ret) {
            return strtoupper($arr[1]);
        } else {
            return "";
        }
    }


    /*
        生成签名
    */

    /**
     *创建md5摘要,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
     */
    static function createSign($data, $key = '')
    {
        $signPars = "";
        ksort($data);
        foreach ($data as $k => $v) {
            if ("" != $v && "sign" != $k) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $key;

        $sign = strtoupper(md5($signPars));
        $data['sign'] = $sign;
        return $data;
    }


    /*
        生成进件签名
    */

    /**
     *创建md5摘要,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
     */
    static function createjjSign($data, $key = '')
    {
        $signPars = "";
        ksort($data);
        foreach ($data as $k => $v) {
            if ("" != $v && "dataSign" != $k) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars = substr($signPars, 0, -1);
        $signPars .= $key;
        $sign = md5($signPars);
        $data['dataSign'] = $sign;
        return $data;
    }

    /*
        验证浦发进件资料签名

    */
    static function isjjSign($data, $key = '')
    {
        $signPars = "";
        ksort($data);
        foreach ($data as $k => $v) {
            if ("" != $v && "dataSign" != $k) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars = substr($signPars, 0, -1);
        $signPars .= $key;
        $sign = md5($signPars);
        return $sign == $data['dataSign'];
    }




    /*
        验证签名

    */


    /**
     *是否威富通签名,规则是:按参数名称a-z排序,遇到空值的参数不参加签名。
     *true:是
     *false:否
     */
    static function isTenpaySign($data, $key = '')
    {
        $signPars = "";
        ksort($data);
        foreach ($data as $k => $v) {
            if ("sign" != $k && "" != $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $key;
        $sign = strtolower(md5($signPars));
        $tenpaySign = strtolower($data["sign"]);
        return $sign == $tenpaySign;
    }


    /**
     * 是否财付通签名
     * @param signParameterArray 签名的参数数组
     * @return boolean
     */
    private function _isTenpaySign($signParameterArray)
    {

        $signPars = "";
        foreach ($signParameterArray as $k) {
            $v = $this->getParameter($k);
            if ("sign" != $k && "" != $v) {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $this->getKey();

        $sign = strtolower(md5($signPars));

        $tenpaySign = strtolower($this->getParameter("sign"));

        //debug信息
        $this->_setDebugInfo($signPars . " => sign:" . $sign .
            " tenpaySign:" . $this->getParameter("sign"));
        return $sign == $tenpaySign;
    }


    /*
        curl发送数据
    */
    static function curl($data, $url)
    {
        //启动一个CURL会话
        $ch = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //要传送的所有数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // 执行操作
        $res = curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($res == NULL) {
            curl_close($ch);
            return false;
        } else if ($response != "200") {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $res;
    }

    static function curlXML($data, $url)
    {
        //启动一个CURL会话
        $ch = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:text/xml; charset=utf-8"));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //要传送的所有数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // 执行操作
        $res = curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($res == NULL) {
            curl_close($ch);
            return false;
        } else if ($response != "200") {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $res;
    }















/////////////////////////////////////////////进件资料的工具start/////////////////////////////

    /*
		将键值对数组，生成xml数据
	*/
    public static function tojjXml($array)
    {
        $xml = '<xml version="1.0" encoding="UTF-8"?>';
        foreach ($array as $k => $v) {
            $xml .= '<' . $k . '>' . $v . '</' . $k . '>';
            // $xml.='<'.$k.'><![CDATA['.$v.']]></'.$k.'>';
        }
        $xml .= '</xml>';
        return $xml;
    }

    /*
        将键值对数组，生成xml数据
    */
    public static function toXmlTwo($array)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';

        foreach ($array as $k => $v) {
            $cc = '<' . $k . '>';
            $bb = '';
            if (is_array($v)) {
                $dd = '';
                foreach ($v as $kk => $vv) {
                    $dd .= '<' . $kk . '>';
                    $ee = '';

                    if (is_array($vv)) {
                        foreach ($vv as $kkk => $vvv) {
                            $ee .= '<' . $kkk . '>' . $vvv . '</' . $kkk . '>';
                        }
                    } else {
                        $ee .= $vv;
                    }
                    $dd .= $ee;
                    $dd .= '</' . $kk . '>';

                }

                $bb .= $dd;

            } else {
                $bb .= $v;
            }

            $cc .= $bb;
            $cc .= '</' . $k . '>';
        }

        $xml .= $cc;

        return $xml;
    }


    /*
        curl  post方式  发送数据  包括字段和文件流
    */
    static function curl2($url, $post_data = array(), $file_fields = array(), $timeout = 600)
    {
        /*
                $url='http://www.design.me/curl_2/getcurl.php';
                $post_data=array('nnnnnnn');
                $file_fields=[realpath('./test.php')];
        */

        // function curl_upload($url, $post_data=array(), $file_fields=array(), $timeout=600) {
        // $result = array('errno' => 0, 'errmsg' => '', 'result' => '');

        $ch = curl_init();
        //set various curl options first

        // set url to post to
        curl_setopt($ch, CURLOPT_URL, $url);

        // return into a variable rather than displaying it
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        //set curl function timeout to $timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //curl_setopt($ch, CURLOPT_VERBOSE, true);

        //set method to post
        curl_setopt($ch, CURLOPT_POST, true);

        // disable Expect header
        // hack to make it working
        $headers = array("Expect: ");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        //generate post data
        $post_array = array();
        if (!is_array($post_data)) {
            return false;
            // $result['errno'] = 5;
            // $result['errmsg'] = 'Params error.';
            return $result;
        }

        foreach ($post_data as $key => $value) {
            $post_array[$key] = $value;
        }

        // set multipart form data - file array field-value pairs
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            if (!empty($file_fields)) {
                foreach ($file_fields as $key => $value) {
                    if (strpos(PHP_OS, "WIN") !== false) {
                        $value = str_replace("/", "\\", $value); // win hack
                    }
                    $file_fields[$key] = new \CURLFile($value);
                }
            }
        } else {
            if (!empty($file_fields)) {
                foreach ($file_fields as $key => $value) {
                    if (strpos(PHP_OS, "WIN") !== false) {
                        $value = str_replace("/", "\\", $value); // win hack
                    }
                    $file_fields[$key] = "@" . $value;
                }
            }
        }

        // set post data
        $result_post = array_merge($post_array, $file_fields);

        // var_dump($result_post);die;
        curl_setopt($ch, CURLOPT_POSTFIELDS, $result_post);
        // print_r($result_post);

        //and finally send curl request
        $output = curl_exec($ch);


        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($output == NULL) {
            curl_close($ch);
            return false;
        } else if ($response != "200") {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $output;

    }

    static function curl3($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $postBodyString = "";
        $encodeArray = Array();
        $postMultipart = false;


        if (is_array($postFields) && 0 < count($postFields)) {

            foreach ($postFields as $k => $v) {
                if ("@" != substr($v, 0, 1)) //判断是不是文件上传
                {

                    $postBodyString .= "$k=" . urlencode(self::characet($v, 'UTF-8')) . "&";
                    $encodeArray[$k] = self::characet($v, 'UTF-8');
                } else //文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                    $encodeArray[$k] = new \CURLFile(substr($v, 1));
                }

            }
            unset ($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeArray);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            }
        }

        if ($postMultipart) {

            $headers = array('content-type: multipart/form-data;charset=' . 'UTF-8' . ';boundary=' . self::getMillisecond());
        } else {

            $headers = array('content-type: application/x-www-form-urlencoded;charset=' . 'UTF-8');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {

            throw new \Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new \Exception($reponse, $httpStatusCode);
            }
        }

        curl_close($ch);
        return $reponse;
    }

    static function characet($data, $targetCharset)
    {


        if (!empty($data)) {
            $fileType = "UTF-8";
            if (strcasecmp($fileType, $targetCharset) != 0) {

                $data = mb_convert_encoding($data, $targetCharset);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }

    static function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /*
            xml转数组


    */
    static function xmltojjarr($xml)
    {

        //禁止引用外部xml实体

        libxml_disable_entity_loader(true);

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $val = json_decode(json_encode($xmlstring), true);

        return $val;

    }


    static function simplest_xml_to_array($xmlstring)
    {
        return json_decode(json_encode((array)simplexml_load_string($xmlstring)), true);
    }

    //数组转XML
    static public function arrayToXml($arr)
    {
        $xml = "";
        foreach ($arr as $key => $val) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            /* if (is_numeric($val)){
                 $xml.="<".$key.">".$val."</".$key.">";
             }else{
                 $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
             }*/
        }
        return $xml;
    }

    static function xml_to_array($xml)
    {
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            $arr = array();
            for ($i = 0; $i < $count; $i++) {
                $key = $matches[1][$i];
                $val = self::xml_to_array($matches[2][$i]);  // 递归
                if (array_key_exists($key, $arr)) {
                    if (is_array($arr[$key])) {
                        if (!array_key_exists(0, $arr[$key])) {
                            $arr[$key] = array($arr[$key]);
                        }
                    } else {
                        $arr[$key] = array($arr[$key]);
                    }
                    $arr[$key][] = $val;
                } else {
                    $arr[$key] = $val;
                }
            }
            return $arr;
        } else {
            return $xml;
        }
    }

    public static function xml_to_array_weixin($xml)
    {
        if (!$xml) {
            return 0;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    public static function curlT($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $postBodyString = "";
        $encodeArray = Array();
        $postMultipart = false;


        if (is_array($postFields) && 0 < count($postFields)) {

            foreach ($postFields as $k => $v) {
                if ("@" != substr($v, 0, 1)) //判断是不是文件上传
                {

                    $postBodyString .= "$k=" . urlencode(self::characet($v, "UTF-8")) . "&";
                    $encodeArray[$k] = self::characet($v, "UTF-8");
                } else //文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                    $encodeArray[$k] = new \CURLFile(substr($v, 1));
                }

            }
            unset ($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeArray);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            }
        }

        if ($postMultipart) {

            $headers = array('content-type: multipart/form-data;charset=' . "UTF-8" . ';boundary=' . self::getMillisecond());
        } else {

            $headers = array('content-type: application/x-www-form-urlencoded;charset=' . "UTF-8");
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {

            throw new \Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new \Exception($reponse, $httpStatusCode);
            }
        }

        curl_close($ch);
        return $reponse;
    }

    public static function curl_get($url)
    {

        $ch = curl_init();

        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);

        //释放curl句柄
        curl_close($ch);
        return $output;
    }


    public static function curl_post_form($post = '',$url, $cookie = '', $returnCookie = 0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if ($post != '' && !empty($post)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('application/x-www-form-urlencoded', 'Content-Length: ' . strlen($post)));
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


}