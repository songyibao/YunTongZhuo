<?php

require dirname(__FILE__) . '/../vendor/autoload.php';

$secretId = "SECRETID"; //替换为用户的 secretId，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
$secretKey = "SECRETKEY"; //替换为用户的 secretKey，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
$region = "ap-beijing"; //替换为用户的 region，已创建桶归属的region可以在控制台查看，https://console.cloud.tencent.com/cos5/bucket
$cosClient = new Qcloud\Cos\Client(
    array(
        'region' => $region,
        'schema' => 'https', //协议头部，默认为http
        'credentials'=> array(
            'secretId'  => $secretId ,
            'secretKey' => $secretKey)));
$local_path = "/data/exampleobject";
try {
    $imageStyleTemplate = new Qcloud\Cos\ImageParamTemplate\ImageStyleTemplate();
    $imageStyleTemplate->setStyle("stylename");
    $picOperationsTemplate = new \Qcloud\Cos\ImageParamTemplate\PicOperationsTransformation();
    $picOperationsTemplate->setIsPicInfo(1);
    $picOperationsTemplate->addRule($imageStyleTemplate, "resultobject");
    $result = $cosClient->putObject(array(
        'Bucket' => 'examplebucket-125000000', //格式：BucketName-APPID
        'Key' => 'exampleobject',
        'Body' => fopen($local_path, 'rb'),
        'PicOperations' => $picOperationsTemplate->queryString(),
    ));
    // 请求成功
    print_r($result);
} catch (\Exception $e) {
    // 请求失败
    echo($e);
}

