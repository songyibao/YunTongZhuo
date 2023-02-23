<?php

namespace app\index\controller;

use CURLFile;
use think\facade\Env;
use think\facade\Request;
use think\Controller;

class WechatApi extends Controller
{
    public function hello($code){
//        return 'hello';
        return $code;
    }
    public function onLogin($code,$appid)
    {
//        $code = Request::param('code');
//        $appid = Request::param('appid');
        $appsecret = '5e308ac99a2c23882ef933483db0202c';
        $wxUrl = 'https://api.weixin.qq.com/sns/jscode2session?appid=%s&secret=%s&js_code=%s&grant_type=authorization_code';
        $getUrl = sprintf($wxUrl, $appid, $appsecret, $code);//把appid，appsecret，code拼接到url里
        $result = curl_get($getUrl);//请求拼接好的url
        $wxResult = json_decode($result, true);
        if (empty($wxResult)) {
            return json(['code'=>1,'message'=>'微信内部错误','data'=>'']);
        } else {
            $loginFail = array_key_exists('errcode', $wxResult);
            if ($loginFail) {//请求失败
                var_dump($wxResult);
            } else {//请求成功
                $openid = $wxResult['openid'];
                return json(['code'=>0,'message'=>'获取openid成功','data'=>$openid]);
            }
        }
    }



}