<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function db_connect()
{
    $conn = new mysqli('localhost', 'root', 'Ss13626350673', 'YunTongZhuo');
    if ($conn->connect_error) {
        $return=array(
            'code'=>1001,
            'msg'=>$conn->connect_error,
            'status'=>false
        );
        die(json_encode($return,JSON_UNESCAPED_UNICODE));
    } else {
        return $conn;
    }
}
function put_object($path,$filename){
    require dirname(__FILE__) . '/vendor/autoload.php';
    $secretId = "secretId"; //替换为用户的 secretId，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
    $secretKey = "secretKey"; //替换为用户的 secretKey，请登录访问管理控制台进行查看和管理，https://console.cloud.tencent.com/cam/capi
    $region = "region"; //替换为用户的 region，已创建桶归属的region可以在控制台查看，https://console.cloud.tencent.com/cos5/bucket
    $cosClient = new Qcloud\Cos\Client(
        array(
            'region' => $region,
            'schema' => 'https', //协议头部，默认为http
            'credentials' => array(
                'secretId' => $secretId,
                'secretKey' => $secretKey)));
//    $local_path = "/data/exampleobject";
//添加tagging
    /*$tagSet = http_build_query( array(
        urlencode("key1") => urlencode("value1"),
        urlencode("key2") => urlencode("value2")),
        '',
        '&'
    ); */
    try {
        $result = $cosClient->putObject(array(
            'Bucket' => 'yuntongzhuo-1304998734', //存储桶名称，由BucketName-Appid 组成，可以在COS控制台查看 https://console.cloud.tencent.com/cos5/bucket
            'Key' => $filename,
            'Body' => fopen($path, 'rb'),
            /*
            'CacheControl' => 'string',
            'ContentDisposition' => 'string',
            'ContentEncoding' => 'string',
            'ContentLanguage' => 'string',
            'ContentLength' => integer,
            'ContentType' => 'string',
            'Expires' => 'string',
            'Metadata' => array(
                'string' => 'string',
            ),
            'StorageClass' => 'string',
            'Tagging' => $tagSet //最多10个标签
            */
        ));
        // 请求成功
        return json($result);
    } catch (\Exception $e) {
        // 请求失败
        return json($e);
    }
}
function getnickname_by_openid($openid)
{
    $conn = db_connect();
    $result = $conn->query("select `nickName` from `User` where `openid`='$openid'");
    $row = $result->fetch_row();
    return $row[0];
}

function getavatar_by_openid($openid)
{
    $conn = db_connect();
    $result = $conn->query("select `avatarUrl` from `User` where `openid`='$openid'");
    $row = $result->fetch_row();
    return $row[0];
}

function getthumb_by_topicid($topic_id)
{
    $conn = db_connect();
    $result = $conn->query("select `thumbs_up` from `topic` where `id`=$topic_id ");
    $row = $result->fetch_row();
    return (int)$row[0];
}

function getthumb_by_commentid($comment_id)
{
    $conn = db_connect();
    $result = $conn->query("select `thumbs_up` from `comment` where `id`=$comment_id ");
    $row = $result->fetch_row();
    return (int)$row[0];
}

function getthumbflag_by_openid_topicid($openid, $topic_id)
{
    $conn = db_connect();
    $result = $conn->query("select * from `thumbs` where `thumb_user_id`='$openid' and `thumb_topic_id`=$topic_id");
    if (count($result->fetch_all()) == 0) {
        return false;
    } else {
        return true;
    }
}

function get_one_comment_by_topicid($topic_id)
{
    $conn = db_connect();
    $result = $conn->query("select `content` from `comment` where `comment_topic_id`=$topic_id order by `thumbs_up` desc,`comment_time` desc  limit 1");
    $row = $result->fetch_all();
    if (count($row, 0) == 0) {
        return "null";
    } else {
        return $row[0][0];
    }
}
function get_thumbs($id, $flag)
{
    $conn = db_connect();
    if ($flag) {
        $result = $conn->query("select `thumbs_up` from `topic` where `id`='$id'");
    } else {
        $result = $conn->query("select `thumbs_up` from `comment` where `id`='$id'");
    }
    $row = $result->fetch_row();
    return $row[0];
}
function get_unread_thumbs_by_topicid($topic_id, $openid)
{
    $conn = db_connect();
    $thumbs_result = $conn->query("select * from `thumbs` where `thumb_topic_id`=$topic_id and `isread`='false' ");
    for ($count = 0; $row = $thumbs_result->fetch_row(); $count++) {
        if ($row[1] != $openid) {
            $thumbs_id_array[$count] = array(
                'id' => $row[0],
                'openid' => $row[1],
                'avatarUrl' => getavatar_by_openid($row[1]),
            );
        }
    }
    return $thumbs_id_array;
}

function get_unread_thumbs_count_by_topicid($topic_id, $openid)
{
    $conn = db_connect();
    $thumbs_result = $conn->query("select * from `thumbs` where `thumb_topic_id`=$topic_id and `isread`='false' ");
    for ($count = 0; $row = $thumbs_result->fetch_row();) {
        if ($row[1] != $openid) {
            $count++;
        }
    }
    return $count;
}
function curl_get($url, &$httpCode = 0)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //不做证书校验,部署在linux环境下请改为true
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $file_contents = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $file_contents;
}
function curl_post($remote_server, $post_data)
{
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $remote_server);
    //设置头文件的信息作为数据流输出
//    curl_setopt ( $curl , CURLOPT_HEADER ,  1 ) ;
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, 1);
    //设置post数据
//    $post_data  =  array (
//        "xh"  =>  "coder" ,
//        "mm"  =>  "12345"
//    ) ;
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    //执行命令
    $data = curl_exec($curl);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
//    print_r ( $data ) ;
    return $data;
}
function get_access_token()
{
//    echo 'start';
    $appid = "wx061dd30bd114eb49";
    //$appsecret = '9b89506cef25d6810aac8170aa5d4788';
    $appsecret = 'f90c985c0f77062c32c96dbdd1e2b0a0';

    $wxUrl = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';
    $getUrl = sprintf($wxUrl, $appid, $appsecret);//把appid，appsecret，code拼接到url里
    $result = curl_get($getUrl);//请求拼接好的url
    $wxResult = json_decode($result, true);
    if (empty($wxResult)) {
        throw new Exception("请求异常，微信内部错误");
    } else {
        $loginFail = array_key_exists('errcode', $wxResult);
        if ($loginFail) {//请求失败
            throw new Exception("请求失败");
        } else {//请求成功
            $token = $wxResult['access_token'];
            $conn = db_connect();
//        $result1 = $conn->query("insert ignore into `user` (`isregister`,`openid`,`create_time`,`topic_count`,`comment_count`) values ('no','$openid',now(),0,0)");
            return $token;
        }
    }
}
function msgCheck($content)
{
    $access_token = get_access_token();
    $post_data = array(
        'content' => $content
    );
    $post_data = json_encode($post_data, JSON_UNESCAPED_UNICODE);
    $we_url = "https://api.weixin.qq.com/wxa/msg_sec_check?access_token=" . $access_token;
    $data = curl_post($we_url, $post_data);
    $data = json_decode($data, true);
//    return $data;
    return $data['errcode'];
}

function imgSecCheck($img)
{
    $name = substr($img, strripos($img, '.'));
//    echo $name;
    $img = file_get_contents($img);
//    $filePath = '/dev/shm/tmp1'.$name;
    $filePath = Env::get('app_path').'cache/tmp1' . $name;
//    $filePath=dirname(__FILE__).'/tmp1'.$name;
    file_put_contents($filePath, $img);
    $obj = new CURLFile(realpath($filePath));
    $obj->setMimeType("image/jpeg");
    $file['media'] = $obj;
    $token = get_access_token();
    $url = "https://api.weixin.qq.com/wxa/img_sec_check?access_token=" . $token;
    $info = http_request($url, $file);
    $info = json_decode($info, true);
    return $info['errcode'];
}
function http_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    $output = curl_exec($curl);
    curl_close($curl);
    file_put_contents('/tmp/heka_weixin.' . date("Ymd") . '.log', date('Y-m-d H:i:s') . "\t" . $output . "\n", FILE_APPEND);
    return $output;
}
function get_se_comment_count_by_commentid($comment_id)
{
    $conn = db_connect();
    $result = $conn->query("select * from `se_comment` where `comment_comment_id`=$comment_id ");
    $res = count($result->fetch_all(), 0);
    $conn->close();
    return $res;
}

function get_se_comment_by_commentid($comment_id)
{
    $conn = db_connect();
    $result = $conn->query("select * from `se_comment` where `comment_comment_id`=$comment_id order by `comment_time` desc");
    $res = array();
    for ($count = 0; $row = $result->fetch_row(); $count++) {
        $res[$count] = array(
            'id' => $row[0],
            'content' => $row[1],
            'nickname' => getnickname_by_openid($row[2]),
            'avatarUrl' => getavatar_by_openid($row[2]),
            'time' => $row[4]
        );
    }
    $conn->close();
    return $res;
}

function get_unread_comments_by_topicid($topic_id, $openid)
{
    $conn = db_connect();
    $comments_result = $conn->query("select * from `comment` where `comment_topic_id`=$topic_id and `isread`='false' order by `comment_time`");
    $flag = 0;
    $comments_id_array = array();
    for ($count = 0; $row = $comments_result->fetch_row(); $count++) {
        if ($row[2] != $openid) {
            $comments_id_array[$flag] = array(
                'id' => $row[0],
                'content' => $row[1],
                'nickname' => getnickname_by_openid($row[2]),
                'avatarUrl' => getavatar_by_openid($row[2]),
                'time' => $row[4],
            );
            $flag++;
        }
    }
    return $comments_id_array;
}
function getthumbflag_by_openid_commentid($openid, $comment_id)
{
    $conn = db_connect();
    $result = $conn->query("select * from `thumbs` where `thumb_user_id`='$openid' and `thumb_comment_id`=$comment_id");
    if (count($result->fetch_all()) == 0) {
        $conn->close();
        return false;
    } else {
        $conn->close();
        return true;
    }
}
function getcount_by_topicid($topic_id)
{
    $conn = db_connect();
    $result = $conn->query("select `comment_count` from `topic` where `id`='$topic_id'");
    $row = $result->fetch_row();
    $conn->close();
    return $row[0];
}

