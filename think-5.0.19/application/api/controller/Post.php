<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/7
 * Time: 下午9:09
 */

namespace app\api\controller;


use think\Db;
use think\Request;
use function app\api\common\isLogin;

class Post {

    // 发帖
    public function create(Request $request) {
        $data = array();
        // 检查用户登录
        if(isLogin($request)) {
            $userId = isLogin($request);
        } else {
            $data = [
                'status' => 'fail',
                'message' => '用户登录信息错误'
            ];
            return json($data);
        }
        // 用户是否匿名
        $hide = $request->param('hide');
        // 用户发送的内容
        $content = $request->param('content');
        $result = Db::name('content')
            ->insert([
                'content' => $content,
                'user_id' => $userId,
                'hide' => $hide,
            ]);
        if($result) {
            $data = [
                'status' => 'success',
                'message' => '添加帖子成功'
            ];
            return json($data);
        } else {
            $data = [
                'status' => 'fail',
                'message' => '添加帖子失败'
            ];
            return json($data);
        }
    }

    // 评论帖子
    public function comment() {
        return;
    }

}