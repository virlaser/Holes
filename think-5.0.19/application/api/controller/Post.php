<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/7
 * Time: 下午9:09
 */

namespace app\api\controller;


use think\Db;
use think\Exception;
use think\Request;
use function app\api\common\isLogin;

class Post {

    // 用户发帖
    public function create(Request $request) {
            if (isLogin($request)) {
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
            if ($result) {
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

    // 用户评论帖子
    public function comment(Request $request) {
        try {
            if (isLogin($request)) {
                $userId = isLogin($request);
            } else {
                $data = [
                    'status' => 'fail',
                    'message' => '用户登录信息错误'
                ];
                return json($data);
            }
            $contentId = $request->param('content_id');
            $hide = $request->param('hide');
            $content = $request->param('content');
            $toUser = $request->param('user_id');
            $result = Db::name('comment')
                ->insert([
                    'content' => $content,
                    'content_id' => $contentId,
                    'user_id' => $userId,
                    'hide' => $hide
                ]);
            // 将帖子评论数加一
            $result2 = Db::name('content')
                ->where('id', '=', $contentId)
                ->setInc('comment_num', 1);
            if ($result and $result2) {
                // 将用户操作计入数据表以标识用户是否点赞过，通知发帖用户
                Db::name('operate')
                    ->insert([
                        'type' => 3,
                        'from_user' => $userId,
                        'to_user' => $toUser,
                        'object_id' => $contentId
                    ]);
                $data = [
                    'status' => 'success',
                    'message' => '评论成功'
                ];
                return json($data);
            } else {
                $data = [
                    'status' => 'fail',
                    'message' => '评论失败'
                ];
                return json($data);
            }
        } catch (Exception $exception) {
            $data = [
                'status' => 'fail',
                'message' => $exception->getMessage()
            ];
            return json($data);
        }
    }

}