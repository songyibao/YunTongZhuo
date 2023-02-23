<?php

namespace app\index\controller;

use think\Controller;
use think\Db;

class Study extends Controller
{
    public function start($uid,$did){
        Db::table('Study')->insert(['uid'=>$uid,'did'=>$did]);
        $row=Db::table('Study')
            ->where('is_end','=',0)
            ->where('uid','=',$uid)
            ->where('did','=',$did)
            ->order('start_time','desc')
            ->find();
        return json(['code'=>0,'message'=>'创建学习成功','data'=>$row['id']]);
    }
    public function end($id){
        $row=Db::table('Study')
            ->where('is_end','=',0)
            ->where('id','=',$id)
            ->update(['is_end'=>1]);
        if($row==1){
            return json(['code'=>0,'message'=>'结束学习成功','data'=>$row]);
        }else{
            return json(['code'=>1,'message'=>'结束学习失败','data'=>$row]);
        }

    }
    public function get_records($uid){
        $res=Db::table('Study')
            ->where('is_end','=',1)
            ->where('uid','=',$uid)
            ->order('start_time','desc')
            ->limit(5)
            ->select();
        for($count=0;$count<count($res);$count++){
            $res[$count]['length']=strtotime($res[$count]['end_time'])-strtotime($res[$count]['start_time']);
        }
        return json(['code'=>0,'message'=>'查询学习记录成功','data'=>$res]);
    }
}