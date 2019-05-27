<?php
/**
 * Created by PhpStorm.
 * User: wangxiaoke
 * Date: 2017/8/30
 * Time: 下午8:49
 */
require_once 'MybankSdk.php';

$xmlTool = new XmlseclibsAdapter();
$xmlTool->setPublicKey(file_get_contents(__DIR__ . '/keys/ALIBABA_BOGDA_PUBLIC_PRIVATE_CERT-pub-cer_2733.pem'));
$xmlTool->addTransform(XmlseclibsAdapter::ENVELOPED);
$xmlTool->setKeyAlgorithm(XMLSecurityKey::RSA_SHA256);
$filePath = '/keys/message.txt';
$xmlDocument = new DOMDocument();
$requestXml = @file_get_contents(__DIR__ . $filePath);
$xmlDocument->loadXML(base64_decode($requestXml));

print_r($xmlTool->verify($xmlDocument));
?>