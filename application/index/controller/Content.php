<?php

namespace app\index\controller;

use think\facade\Request;
use think\Controller;
use think\Db;

class Content extends Controller
{
    public function create(Request $request)
    {
        $content = $request::param('content');
        $openid = $request::param('openid');
        if (empty($request::param('title'))) {
            $title = "notitle";
        } else {
            $title = $request::param('title');
            //取出文章数，加一
        }
        if (!empty($request::param('img_url'))) {
            $img_url = $request::param('img_url');
        } else {
            $img_url = null;
        }
        $flag1 = 0;
        $imgs = [];
        if ($img_url != null) {
            $imgs = json_decode(html_entity_decode(stripslashes($img_url)), true);
        }
//图片安全检测
        foreach ($imgs as $value) {
            if (imgSecCheck($value) != 0) {
                $flag1 = imgSecCheck($value);
                break;
            }
        }
//unset($value); // 最后取消掉引用

        $flag2 = msgCheck($content);
        $tt = '';
        if ($flag2 == 0 && $flag1 == 0) {
            $conn = db_connect();

//取出文章数，加一
            $search_result = $conn->query("select `topic_count` from `User` where openid='$openid' ");
            $row = $search_result->fetch_row();
            $content_count1 = $row[0];
            $content_count1 = (int)$content_count1 + 1;

//话题板块文章数加1
            if ($title != 'notitle') {
                $search_result = $conn->query("select `comments_count` from `title` where `name`='$title' ");
                $row = $search_result->fetch_row();
                $content_count2 = $row[0];
                $content_count2 = (int)$content_count2 + 1;
                $update_result = $conn->query("UPDATE `title` SET `comments_count`=$content_count2 where `name`='$title'");
            }


//把文章标题和内容存入数据库
            $insert_result = $conn->query("insert into `topic`(`title`,`content`,`topic_time`,`topic_user_id`,`img_url`) values('$title','$content',now(),'$openid','$img_url')");
//更新数据库文章数
            $update_result = $conn->query("UPDATE `user` SET topic_count=$content_count1 WHERE openid = '$openid'");
//    get_usercontent($openid);
            $conn->close();
            $tt='success';
        } else if ($flag1 != 0) {
            $tt='img_error';
        } else {

            $tt='msg_error';
        }
        return json(['code'=>0,'message'=>'发布成功','data'=>$tt]);
    }
    public function getcontentbyid(Request $request){
        $id = $request::param('id');
        $openid = $request::param('openid');
        $conn = db_connect();
        $result = $conn->query("select * from `topic` where `id`=$id");
        $row = $result->fetch_row();
        $array = array(
            'id' => $row[0],
            'title' => $row[1],
            'content' => $row[2],
            'count' => $row[3],
            'time' => $row[5],
            'openid' => $row[6],
            'thumbs' => getthumb_by_topicid($row[0]),
            'thumbflag' => getthumbflag_by_openid_topicid($openid, $row[0]),
            'nickname' => getnickname_by_openid($row[6]),
            'avatarUrl' => getavatar_by_openid($row[6]),
            'img_url' => $row[12]
        );
        return json(['data'=>$array]);
    }

    public function delete($id)
    {
        Db::table('topic')->where('id', $id)->delete();
        Db::table('thumbs')->where('thumb_topic_id', $id)->delete();
        $res1 = Db::table('comment')->where('comment_topic_id', $id)->columu('id');
        for ($count = 0; $count < count($res1); $count++) {
            Db::table('se_comment')->where('comment_comment_id', $res1[$count]);
        }
        $res2 = Db::table('comment')->where('comment_topic_id', $id)->delete();
    }

    public function getall(Request $request)
    {
        $openid = $request::param('openid');
        $flag=false;
        if (!empty($request::param('last_id'))) {
            $last_id = $request::param('last_id');
            $flag = true;
        }
        $conn = db_connect();
        if ($flag) {
            $select_result = $conn->query("select * from `topic` where `id`<$last_id order by `id` desc limit 10 ");
        } else {
            $select_result = $conn->query("select * from `topic` order by `id` desc limit 10 ");
        }
        if (!$select_result) {
            die("查询失败");
        }
        $content_array = array();

        for ($count = 0; $row = $select_result->fetch_row(); $count++) {
            $content_array[$count] = array(
                'id' => $row[0],
                'title' => $row[1],
                'content' => $row[2],
                'commentshow' => get_one_comment_by_topicid($row[0]),
                'count' => $row[3],
                'time' => $row[5],
                'openid' => $row[6],
                'thumbs' => getthumb_by_topicid($row[0]),
                'thumbflag' => getthumbflag_by_openid_topicid($openid, $row[0]),
                'nickname' => getnickname_by_openid($row[6]),
                'avatarUrl' => getavatar_by_openid($row[6]),
                'img_url' => $row[12]
            );
        }
        return json(['code'=>0,'message'=>'获取帖子列表成功','data'=>$content_array]);
    }
}