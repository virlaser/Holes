<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/7
 * Time: 下午9:08
 */

namespace app\api\controller;


use function app\api\common\isLogin;
use think\Controller;
use think\Db;
use think\Request;

class Square extends Controller {

    // 显示广场列表
    public function index(Request $request) {

        $data = array();

        if(isLogin($request)) {
            $userId = isLogin($request);
        } else {
            $data = [
                'status' => 0,
                'message' => '用户登录信息错误'
            ];
            return json($data);
        }

        $lists = Db::table('hole_content')
            ->join('hole_user', 'hole_content.user_id=hole_user.id')
            ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
            ->paginate(20, true);


        foreach($lists as $e) {
            $like_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 1
                ])
                ->find();
            $like_flag = $like_flag?1:0;

            $dislike_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 2
                ])
                ->find();
            $dislike_flag = $dislike_flag?1:0;

            $comment_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 3
                ])
                ->find();
            $comment_flag = $comment_flag?1:0;

            $e['like_flag'] = $like_flag;
            $e['dislike_flag'] = $dislike_flag;
            $e['comment_flag'] = $comment_flag;

            array_push($data, $e);
        }

        return json($data);
    }

    // 点赞
    public function like() {
        return;
    }

    // 点踩
    public function dislike() {
        return;
    }

    // 举报
    public function report() {
        return;
    }

    // 删除
    public function delete() {
        return;
    }

    // 帖子详情
    public function detail() {
        return;
    }

}