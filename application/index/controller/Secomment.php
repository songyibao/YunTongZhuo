<?php

namespace app\index\controller;

use think\facade\Request;
use think\Controller;
use think\Db;
class Secomment extends Controller
{
    public function create(Request $request){
        $comment_id = $request::param('comment_id');
        $content = $request::param('comment_content');
        $comment_user_id = $request::param('openid');

        if (msgCheck($content) == 0) {
            $conn = db_connect();
//    $comment_count=getcount_by_topicid($comment_id);
//    $comment_count=$comment_count+1;
//    $result=$conn->query("UPDATE `topic` SET `comment_count`=$comment_count where `id`=$topic_id");
            $res = $conn->query("select `comment_topic_id` from `comment` where `id`=$comment_id");
            $res = $res->fetch_row();
            $topic_id = $res[0];
            $res2 = $conn->query("select `comment_count` from `topic` where `id`=$topic_id");
            $res2 = $res2->fetch_row();
            $res2 = $res2[0];
            $comment_count = $res2 + 1;
            $result = $conn->query("update `topic` set `comment_count`=$comment_count where `id`=$topic_id");
            $result = $conn->query("insert into `se_comment`(`content`,`comment_user_id`,`comment_comment_id`,`comment_time`) values ('$content','$comment_user_id','$comment_id',now())");
            if ($result) {
                return json(['data'=>'success']);
            }
        } else {
            return json(['data'=>'error']);
        }
    }
    public function getall(Request $request){
        $comment_id = $request::param('comment_id');
        $openid = $request::param('openid');
        $conn = db_connect();
        $select_result = $conn->query("select * from `se_comment` where `comment_comment_id`=$comment_id order by `comment_time` desc");

        if (!$select_result) {
            die("查询失败");
        }
        $content_array = array();
        for ($count = 0; $row = $select_result->fetch_row(); $count++) {
            $content_array[$count] = array(
                'id' => $row[0],
                'content' => $row[1],
//        'userid' => $row[2],
                'time' => $row[4],
                'nickname' => getnickname_by_openid($row[2]),
                'avatarUrl' => getavatar_by_openid($row[2]),
            );
        }

        return json(['data'=>$content_array]);

    }
}