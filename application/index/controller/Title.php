<?php

namespace app\index\controller;
use think\facade\Request;
use think\Controller;
use think\Db;
class Title extends Controller
{
    public function gettitles(Request $request){
        $conn = db_connect();
        $select_result = $conn->query("select * from `title` order by `comments_count` desc,`weight` desc ");
        if (!$select_result) {
            die("查询失败");
        }
        $content_array = array();
//$content_array[0] = array(
//    'flag' => true,
//    'name' => '推荐',
//    'icon' => 'http://cos.songyb.xyz/WeChatApp/tuijian3.png',
//    'tag' => 'topic'
//);
        for ($count = 0; $row = $select_result->fetch_row(); $count++) {
            $content_array[$count] = array(
                'id' => $row[0],
                'flag' => false,
                'name' => $row[1],
                'icon' => $row[2],
                'comments_count' => $row[3],
                'tag' => $row[4],
                'available' => $row[5]
//        'sec_titles'=>get_sectitles_by_title_id($row[0])
            );
        }
        return json(['code'=>0,'message'=>'查询板块成功','data'=>$content_array]);
    }
}