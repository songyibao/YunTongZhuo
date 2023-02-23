<?php

namespace app\index\controller;

use think\facade\Request;
use think\Controller;
use think\Db;
class Comment extends Controller
{
    public function create(Request $request){
        $topic_id = $request::param('topic_id');
        $content = $request::param('comment_content');
        $comment_user_id = $request::param('openid');

        if (msgCheck($content) == 0) {
            $conn = db_connect();
            $comment_count = getcount_by_topicid($topic_id);
            $comment_count = $comment_count + 1;
            $result = $conn->query("UPDATE `topic` SET `comment_count`=$comment_count where `id`=$topic_id");
            $result = $conn->query("insert into `comment`(`content`,`comment_user_id`,`comment_topic_id`,`comment_time`,`floor`) values ('$content','$comment_user_id',$topic_id,now(),$comment_count)");
            if ($result) {
                return json(['data'=>'success']);
            }
        } else {
            return json(['data'=>'error']);
        }
    }
    public function getcommentbyid(Request $request){
        $topic_id = $request::param('topic_id');
        $openid = $request::param('openid');
        $conn = db_connect();
        $select_result = $conn->query("select * from `comment` where `comment_topic_id`=$topic_id order by `comment_time` desc");

        if (!$select_result) {
            die("查询失败");
        }
        $content_array = array();
        for ($count = 0; $row = $select_result->fetch_row(); $count++) {
            $content_array[$count] = array(
                'id' => $row[0],
                'content' => $row[1],
                'userid' => $row[2],
                'time' => $row[4],
                'nickname' => getnickname_by_openid($row[2]),
                'avatarUrl' => getavatar_by_openid($row[2]),
                'thumbs' => getthumb_by_commentid($row[0]),
                'thumbflag' => getthumbflag_by_openid_commentid($openid, $row[0]),
                'comment' => get_se_comment_by_commentid($row[0]),
                'comment_count' => get_se_comment_count_by_commentid($row[0])
            );
        }
        return json(['data'=>$content_array]);
    }
    public function getspecomment(Request $request){
        $id = $request::param('id');
        $openid = $request::param('openid');
        $conn = db_connect();
        $result = $conn->query("select * from `comment` where `id`=$id");
        $row = $result->fetch_row();
        $array = array(
            'id' => $row[0],
            'content' => $row[1],
            'time' => $row[4],
            'thumbs' => getthumb_by_commentid($row[0]),
            'count' => get_se_comment_count_by_commentid($id),
            'thumbflag' => getthumbflag_by_openid_commentid($openid, $row[0]),
            'nickname' => getnickname_by_openid($row[2]),
            'avatarUrl' => getavatar_by_openid($row[2]),
        );

        return json(['data'=>$array]);
    }
}