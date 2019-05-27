<?php
/**
 * Created by PhpStorm.
 * User: wangxiaoke
 * Date: 2017/8/30
 * Time: 下午8:49
 */
require_once 'MybankSdk.php';

$xmlTool = new XmlseclibsAdapter();
$xmlTool->setPrivateKey(file_get_contents(__DIR__ . '/keys/privkey.pem'));
$xmlTool->addTransform(XmlseclibsAdapter::ENVELOPED);

$filePath = '/keys/request.xml';
$xmlDocument = new DOMDocument();
$requestXml = @file_get_contents(__DIR__ . $filePath);
$xmlDocument->loadXML($requestXml);
$xmlTool->sign($xmlDocument);

var_dump($xmlDocument->saveXML());
?>