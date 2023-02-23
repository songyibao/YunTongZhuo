<?php

namespace app\index\controller;

use think\Controller;
use think\facade\App;

class Download extends Controller
{
    public function font($name){
        if($name=='zhehei'){
            return download(App::getRootPath().'res/zh.ttF','zh.ttf');
        }else if($name=='siyuanhei'){
            return download(App::getRootPath().'res/sy.OTF','sy.OTF');
        }else if($name=='yuanti'){
            return download(App::getRootPath().'res/yt.TTF','yt.TTF');
        }

//        return App::getRootPath();
    }
    public function font_base64($name){
        if($name=='zhehei'){
            return json(['code'=>0,'message'=>'获取到字体css','data'=>'']);
        }else if($name=='siyuanhei'){
            return download(App::getRootPath().'res/sy.OTF','sy.OTF');
        }else if($name=='yuanti'){
            return download(App::getRootPath().'res/yt.TTF','yt.TTF');
        }
    }
}