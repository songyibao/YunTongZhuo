<?php

namespace app\index\controller;

use think\Controller;
use think\Exception;
use think\facade\Request;
use think\Db;

class Deskmate extends Controller
{
    public function get_users($uid)
    {
        $users=Db::table('User')
            ->where([['id','<>',$uid],['have_d','=',0]])
            ->order('update_time','desc')
            ->limit(10)
            ->select();
        return json(['code'=>0,'message'=>'success','data'=>$users]);
    }
    public function get_apls($uid){
        $apl_ids=Db::table('D_apply')->where('id','=',$uid)->column('from');
//        return json(Db::table('User')->column('id'));
        $coms=Db::table('D_apply')->where('id',$uid)->column('comment');
        $users=[];
        for($count=0;$count<count($apl_ids);$count++){
            $user=Db::table('User')->where('id',$apl_ids[$count])->select()[0];
            $user['comment']=$coms[$count];
            array_push($users,$user);
        }
        return json(['code'=>0,'message'=>'查询申请记录成功','data'=>$users]);
    }

    public function apply()
    {
        $id=Request::param('id');
        $from=Request::param('from');
        $comment=Request::param('comment');
//        return json(['id'=>$id,'from'=>$from,'comment'=>$comment]);
        try{
            $res=Db::table('D_apply')->insert(['id'=>$id,'from'=>$from,'comment'=>$comment]);
            return json(['code'=>0,'message'=>'添加申请记录成功','data'=>$res]);
        }catch(Exception $e){
            return json(['code'=>1,'message'=>'已有记录','data'=>'']);
        }


    }
    public function accept($id,$from){
        try{
            $res=Db::table('Deskmate')->insert(['id'=>$id,'from'=>$from]);
            Db::table('D_apply')
                ->where('from','=',$from)
                ->delete();
            Db::table('D_apply')
                ->where('from','=',$id)
                ->delete();
            Db::table('User')->where('id','in',[$id,$from])->update(['have_d'=>1]);
            $user=Db::table('User')->where('id',$id)->select()[0];
            return json(['code'=>0,'message'=>'确认关系成功','data'=>$user]);
        }catch (\Exception $ex){
            return json(['code'=>2,'message'=>'确认同桌关系失败','data'=>'']);
        }
    }
    public function reject($id,$from){
        Db::table('D_apply')
            ->where('from','=',$from)
            ->where('id','=',$id)
            ->delete();
        return json(['code'=>0,'message'=>'拒绝关系成功','data'=>'']);
    }
    public function cancel($id,$from){
        try{
            $res1=Db::table('Deskmate')->where('id','in',[$id,$from])->delete();
            $res2=Db::table('User')->where('id','in',[$id,$from])->update(['have_d'=>0]);
            return json(['code'=>0,'message'=>'解除同桌关系成功','data'=>['delete'=>$res1,'update'=>$res2]]);
        }catch (\Exception $ex){
            return json(['code'=>1,'message'=>'解除同桌关系失败','data'=>$ex]);
        }
    }
    public function get_dinfo($uid){
        $deskmate=Db::table('Deskmate')->whereOr(['id'=>$uid,'from'=>$uid])->find();
        $from=$deskmate['from'];
        $id=$deskmate['id'];
        if($id==$uid){
            $dinfo=Db::table('User')->where('id',$from)->find();
        }else {
            $dinfo = Db::table('User')->where('id', $id)->find();
        }
        if(empty($dinfo)){
            return json(['code'=>0,'message'=>'获取同桌信息成功','data'=>$dinfo]);
        }else{
            $dinfo['plan']=$this->get_plan($uid);
            $dinfo['status']=$this->get_tell_status($uid);
            return json(['code'=>0,'message'=>'获取同桌信息成功','data'=>$dinfo]);
        }


    }
    public function get_plan($id){
        $res=Db::table('Deskmate')
            ->whereOr('id','=',$id)
            ->whereOr('from','=',$id)
            ->value('plan');
        return $res;
    }
    public function get_tell($id){
        $row=Db::table('Deskmate')
            ->whereOr('id','=',$id)
            ->whereOr('from','=',$id)
            ->find();
        DB::table('Deskmate')
            ->whereOr('id','=',$id)
            ->whereOr('from','=',$id)
            ->update(['is_read'=>$id==$row['to_id']?1:0]);
        return json(['code'=>0,'message'=>'查询悄悄话成功','data'=>$row['tell']]);
    }
    public function get_tell_status($id){
        $res=Db::table('Deskmate')
            ->whereOr('id','=',$id)
            ->whereOr('from','=',$id)
            ->find();
        $comment="";
        if($res['tell']!=null){
            if($res['is_read']==0){
                if($res['to_id']==$id){
                    $comment="您有未读消息";
                }else{
                    $comment="对方未读您的消息";
                }
            }else{
                if($res['to_id']==$id){
                    $comment="暂无未读消息";
                }else{
                    $comment="对方已读您的消息";
                }
            }
        }
        return $comment;
    }
    public function change_plan(){
        $id=Request::param('id');
        $plan=Request::param('plan');
//        return json(['id'=>$id,'plan'=>$plan]);
        $res=Db::table('Deskmate')
            ->whereOr('id','=',$id)
            ->whereOr('from','=',$id)
            ->update(['plan'=>$plan]);
        if($res==1){
            return json(['code'=>0,'message'=>'更改学习计划成功','data'=>$plan]);
        }else{
            return json(['code'=>1,'message'=>'更改学习计划失败','data'=>$res]);
        }
    }
    public function change_tell(){
        $id=Request::param('id');
        $tell=Request::param('tell');
//        return json(['id'=>$id,'plan'=>$plan]);
        $row=Db::table('Deskmate')
            ->whereOr('id','=',$id)
            ->whereOr('from','=',$id)
            ->find();
        $res=Db::table('Deskmate')
            ->whereOr('id','=',$id)
            ->whereOr('from','=',$id)
            ->update(['tell'=>$tell,'is_read'=>0,'to_id'=>$row['id']==$id?$row['from']:$row['id']]);
        $status=$this->get_tell_status($id);
        if($res==1){
            return json(['code'=>0,'message'=>'发送悄悄话成功','data'=>$status]);
        }else{
            return json(['code'=>1,'message'=>'发送悄悄话失败','data'=>$res]);
        }
    }
}