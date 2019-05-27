<?php

namespace MyBank;


use Alipayopen\Sdk\LtLogger;
use Illuminate\Support\Facades\Log;

class AopClient
{


    public $Appid;
    public $IsvOrgId;
    public $url;
    public $Version = "1.0.0";
    public $ReqTime;
    public $Function;
    public $ReqTimeZone = 'UTC+8';
    public $ReqMsgId;
    public $InputCharset = 'UTF-8';
    public $partner_private_key;
    public $mybank_public_key;

    //请求
    public function Request($data)
    {
        try {
            $xml = Tools::arrayToXml($data);
            $dataxml = '<?xml version="1.0" encoding="UTF-8"?>
<document>
      <request id="request">
    <head>
      <Version>' . $this->Version . '</Version>
      <Appid>' . $this->Appid . '</Appid>
      <ReqTime>' . date('YmdHis') . '</ReqTime>
      <Function>' . $this->Function . '</Function>
      <ReqTimeZone>' . $this->ReqTimeZone . '</ReqTimeZone>
      <ReqMsgId>' . $this->ReqMsgId . '</ReqMsgId>
      <InputCharset>' . $this->InputCharset . '</InputCharset>
     <Reserve/>
    </head>
    <body>
      <IsvOrgId>' . $this->IsvOrgId . '</IsvOrgId>
      ' . $xml . '
    </body>
  </request>
</document>
';
            $this->writelog(storage_path() . '/logs/网商银行报文.log', '请求报文：'.$dataxml);

            $xmlTool = new Sdk\XmlseclibsAdapter();
            $xmlTool->setPrivateKey($this->p($this->partner_private_key));
            $xmlTool->addTransform(Sdk\XmlseclibsAdapter::ENVELOPED);
            $xmlTool->setKeyAlgorithm(Sdk\XMLSecurityKey::RSA_SHA256);
            //
            $xmlDocument = new \DOMDocument();
            $xmlDocument->loadXML($dataxml);
            $xmlTool->sign($xmlDocument);
            $xmlDocument = $xmlDocument->saveXML();
            $return = Tools::curlXML($xmlDocument, $this->url);
            $this->writelog(storage_path() . '/logs/网商银行报文.log', '返回报文：'.$return);

            $TRUE = substr($return, 0, 3);
            if ($TRUE == 'SGW' || $return == false) {
                return $return = [
                    'status' => 2,
                    'message' => $return  . $this->ReqMsgId,
                    'ReqMsgId' => $this->ReqMsgId
                ];
            }
            //校验
            $xmlTool->setPublicKey($this->mybank_public_key);
            $xmlTool->addTransform(Sdk\XmlseclibsAdapter::ENVELOPED);
            $xmlTool->setKeyAlgorithm(Sdk\XMLSecurityKey::RSA_SHA256);
            $xmlDocument = new \DOMDocument();
            $xmlDocument->loadXML($return);

            if ($xmlTool->verify($xmlDocument)) {

                // $return=Tools::xml_to_array($return);
                $data = Tools::xml_to_array($return);
                $return = [
                    'status' => 1,
                    'data' => $data,
                    'ReqMsgId' => $this->ReqMsgId,
                ];
            } else {
                $return = [
                    'status' => 2,
                    'message' => '验证签名错误',
                    'ReqMsgId' => $this->ReqMsgId,
                ];
            }
        } catch (\Exception $exception) {
            $return = [
                'status' => 2,
                'message' => $exception->getMessage()
            ];
        }
        return $return;

    }

    //返回
    public function response($data)
    {
        try {
            $xml = Tools::arrayToXml($data);
            $dataxml = '<?xml version="1.0" encoding="UTF-8"?>
<document>
      <response id="response">
    <head>
      <Version>' . $this->Version . '</Version>
      <Appid>' . $this->Appid . '</Appid>
      <ReqTime>' . date('YmdHis') . '</ReqTime>
      <Function>' . $this->Function . '</Function>
      <ReqTimeZone>' . $this->ReqTimeZone . '</ReqTimeZone>
      <ReqMsgId>' . $this->ReqMsgId . '</ReqMsgId>
      <InputCharset>' . $this->InputCharset . '</InputCharset>
     <Reserve/>
    </head>
    <body>
      <RespInfo> ' . $xml . '</RespInfo>
    </body>
  </response>
</document>
';
            $xmlTool = new Sdk\XmlseclibsAdapter();
            $xmlTool->setPrivateKey($this->p($this->partner_private_key));
            $xmlTool->addTransform(Sdk\XmlseclibsAdapter::ENVELOPED);
            $xmlTool->setKeyAlgorithm(Sdk\XMLSecurityKey::RSA_SHA256);
            $xmlDocument = new \DOMDocument();
            $xmlDocument->loadXML($dataxml);
            $xmlTool->sign($xmlDocument);
            $xmlDocument = $xmlDocument->saveXML();

            return $xmlDocument;
        } catch (\Exception $exception) {
            $return = [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }

    //返回
    public function response_a($data)
    {
        try {
            $xml = Tools::arrayToXml($data);
            $dataxml = '<?xml version="1.0" encoding="UTF-8"?>
<document>
      <response id="response">
    <head>
      <Version>' . $this->Version . '</Version>
      <Appid>' . $this->Appid . '</Appid>
      <RespTime>' . date('YmdHis') . '</RespTime>
      <Function>' . $this->Function . '</Function>
      <RespTimeZone>' . $this->ReqTimeZone . '</RespTimeZone>
      <ReqMsgId>' . $this->ReqMsgId . '</ReqMsgId>
      <InputCharset>' . $this->InputCharset . '</InputCharset>
    </head>
    <body>
      ' . $xml . '
    </body>
  </response>
</document>
';
            $xmlTool = new Sdk\XmlseclibsAdapter();
            $xmlTool->setPrivateKey($this->p($this->partner_private_key));
            $xmlTool->addTransform(Sdk\XmlseclibsAdapter::ENVELOPED);
            $xmlTool->setKeyAlgorithm(Sdk\XMLSecurityKey::RSA_SHA256);
            $xmlDocument = new \DOMDocument();
            $xmlDocument->loadXML($dataxml);
            $xmlTool->sign($xmlDocument);
            $xmlDocument = $xmlDocument->saveXML();

            return $xmlDocument;
        } catch (\Exception $exception) {
            $return = [
                'status' => 0,
                'msg' => $exception->getMessage()
            ];
        }

    }

    //返回
    public function response_no_RespInfo($data)
    {
        try {
            $xml = Tools::arrayToXml($data);
            $dataxml = '<?xml version="1.0" encoding="UTF-8"?>
<document>
      <response id="response">
    <head>
      <Version>' . $this->Version . '</Version>
      <Appid>' . $this->Appid . '</Appid>
      <ReqTime>' . date('YmdHis') . '</ReqTime>
      <Function>' . $this->Function . '</Function>
      <ReqTimeZone>' . $this->ReqTimeZone . '</ReqTimeZone>
      <ReqMsgId>' . $this->ReqMsgId . '</ReqMsgId>
      <InputCharset>' . $this->InputCharset . '</InputCharset>
     <Reserve/>
    </head>
    <body>
     ' . $xml . '
    </body>
  </response>
</document>
';
            $xmlTool = new Sdk\XmlseclibsAdapter();
            $xmlTool->setPrivateKey($this->p($this->partner_private_key));
            $xmlTool->addTransform(Sdk\XmlseclibsAdapter::ENVELOPED);
            $xmlTool->setKeyAlgorithm(Sdk\XMLSecurityKey::RSA_SHA256);
            $xmlDocument = new \DOMDocument();
            $xmlDocument->loadXML($dataxml);
            $xmlTool->sign($xmlDocument);
            $xmlDocument = $xmlDocument->saveXML();

            return $xmlDocument;
        } catch (\Exception $exception) {
            $return = [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }

    public function p($partner_private_key)
    {
        $partner_private_key1 = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($partner_private_key, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";

        return $partner_private_key1;
    }

    public function check($return)
    {
        $xmlTool = new Sdk\XmlseclibsAdapter();
        $xmlTool->setPublicKey($this->mybank_public_key);
        $xmlTool->addTransform(Sdk\XmlseclibsAdapter::ENVELOPED);
        $xmlTool->setKeyAlgorithm(Sdk\XMLSecurityKey::RSA_SHA256);
        $xmlDocument = new \DOMDocument();
        $xmlDocument->loadXML($return);

        if ($xmlTool->verify($xmlDocument)) {

            // $return=Tools::xml_to_array($return);
            $data = Tools::xml_to_array($return);
            $return = [
                'status' => 1,
                'data' => $data
            ];
        } else {
            $return = [
                'status' => 0,
                'message' => '验证签名错误'
            ];
        }
        return $return;
    }


    public function writelog($log_file, $exception)
    {
        try {
            $logger = new LtLogger();
            $logger->conf["log_file"] = $log_file;
            $logger->conf["separator"] = "---------------";
            $logData = array(
                date("Y-m-d H:i:s"),
                str_replace("\n", "", $exception),
            );
            $logger->log($logData);
        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }
}