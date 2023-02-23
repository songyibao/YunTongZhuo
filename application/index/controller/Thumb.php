<?php

namespace app\index\controller;
use think\facade\Request;
use think\Controller;
use think\Db;
class Thumb extends Controller
{
    public function getunread(Request $request){
        $openid = $request::param('openid');
        $conn = db_connect();
        $topic_result = $conn->query("select * from `topic` where `topic_user_id`='$openid' and `thumbs_up`>0 order by `topic_time` desc");
//$topic_id_array = $topic_result->fetch_all();
        $flag = 0;
        for ($count = 0; $row = $topic_result->fetch_row(); $count++) {
            if (get_unread_thumbs_by_topicid($row[0], $openid) != null) {
                $topic_id_array[$flag] = array(
                    'id' => $row[0],
                    'content' => $row[2],
                    'nickname' => getnickname_by_openid($row[6]),
                    'thumbs' => get_unread_thumbs_by_topicid($row[0], $openid),
                    'count' => get_unread_thumbs_count_by_topicid($row[0], $openid)
                );
                $flag++;
            }
        }
        return json(['code'=>0,'message'=>'获取点赞记录成功','data'=>json_encode($topic_id_array, JSON_UNESCAPED_UNICODE)]);
    }
    public function thumbup(Request $request){
        $openid = $request::param('openid');
        $res='';
        if (empty($request::param('comment_id'))) {
            $topic_id = $request::param('topic_id');
            $conn = db_connect();
            $thumbs = get_thumbs($topic_id, true) + 1;
            $result = $conn->query("insert into `thumbs`(`thumb_user_id`,`thumb_topic_id`) values ('$openid','$topic_id')");
            $result = $conn->query("update `topic` set `thumbs_up`='$thumbs' where `id`=$topic_id");
            if ($result) {
                $res="点赞成功";
            } else {
                $res="点赞失败";
            }
        } else {
            $comment_id = $_GET['comment_id'];
            $conn = db_connect();
            $thumbs = get_thumbs($comment_id, false) + 1;
            $result = $conn->query("insert into `thumbs`(`thumb_user_id`,`thumb_comment_id`) values ('$openid','$comment_id')");
            $result = $conn->query("update `comment` set `thumbs_up`='$thumbs' where `id`=$comment_id");
            if ($result) {
                $res="点赞成功";
            } else {
                $res="点赞失败";
            }
        }
        return json(['code'=>0,'message'=>'点赞成功','data'=>$res]);
    }
    public function thumbdown(Request $request){
        $openid = $request::param('openid');
        $res = '';
        if (empty($request::param('comment_id'))) {
            $topic_id = $request::param('topic_id');
            $conn = db_connect();
            $thumbs = get_thumbs($topic_id, true) - 1;
            $result = $conn->query("delete from `thumbs` where `thumb_user_id`='$openid' and `thumb_topic_id`=$topic_id");
            $result = $conn->query("update `topic` set `thumbs_up`='$thumbs' where `id`=$topic_id");
            if ($result) {
                $res="取赞成功";
            } else {
                $res="取赞失败";
            }
        } else {
            $comment_id = $_GET['comment_id'];
            $conn = db_connect();
            $thumbs = get_thumbs($comment_id, false) - 1;
            $result = $conn->query("delete from `thumbs` where `thumb_user_id`='$openid' and `thumb_comment_id`=$comment_id");
            $result = $conn->query("update `comment` set `thumbs_up`='$thumbs' where `id`=$comment_id");
            if ($result) {
                $res="取赞成功";
            } else {
                $res="取赞失败";
            }
        }
        return json(['code'=>0,'message'=>'取消点赞成功','data'=>$res]);
    }
}