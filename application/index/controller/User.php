<?php

namespace app\index\controller;

use think\Controller;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\Request;
use think\Db;

class User extends Controller
{
    public function login()
    {
        $openid = Request::param('openid');
        $nickName = Request::param('nickName');
        $avatarUrl = Request::param('avatarUrl');
        $user =[];
        try {
            $user=Db::table('User')->where('openid', $openid)->selectOrFail();
        } catch (ModelNotFoundException $e) {

        } catch (DataNotFoundException $e) {
            $data = ['nickName' => $nickName, 'avatarUrl' => $avatarUrl, 'openid' => $openid];
            Db::table('User')->insert($data);
            $user=Db::table('User')->where('openid', $openid)->find();
            return json(['code' => 0, 'message' => '用户创建成功', 'data' => $user]);
        }
        put_object($avatarUrl,'avatar/'.$user[0]['id'].'_avatar.jpg');
        $data = ['nickName' => $nickName, 'avatarUrl' => $avatarUrl, 'openid' => $openid];
        Db::table('User')->where('id',$user[0]['id'])->update($data);
        $user=Db::table('User')->where('openid', $openid)->find();
        return json(['code' => 0, 'message' => '用户更新成功', 'data' => $user]);
    }

    public function change_info(){
        $id=Request::param('uid');
//        $tel=Request::param('tel');
//        $intro=Request::param('intro');
        $signature = Request::param('signature');
//        $data = ['tel' => $tel, 'signature' => $signature,'intro'=>$intro];
        $data = ['signature' => $signature];
        $count=Db::table('User')->where('id',$id)->update($data);
        $user=Db::table('User')->where('id', $id)->select();
        return json(['code' => 0, 'message' => $count.'个用户信息更新成功', 'data' => $user[0]]);
    }
}