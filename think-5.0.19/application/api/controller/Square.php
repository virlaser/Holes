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
        // 查出帖子对应的点赞等信息以及分页
        $lists = Db::table('hole_content')
            ->where('is_delete', '=', 0)
            ->join('hole_user', 'hole_content.user_id=hole_user.id')
            ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
            ->paginate(10, true);
        foreach($lists as $e) {
            // 判断此用户是否点赞过帖子
            $like_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 1
                ])
                ->find();
            $like_flag = $like_flag?1:0;
            // 判断此用户是否点踩过帖子
            $dislike_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 2
                ])
                ->find();
            $dislike_flag = $dislike_flag?1:0;
            // 判断此用户是否评论过帖子
            $comment_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 3
                ])
                ->find();
            $comment_flag = $comment_flag?1:0;
            // 判断此用户是否举报过帖子
            $report_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 4
                ])
                ->find();
            $report_flag = $report_flag?1:0;
            // 判断此帖子是不是此用户写的
            $my_flag = ($e['user_id']==$userId)?1:0;
            // 数据查询返回的数据集不能动态添加数据，因此重新构造数据集
            $e['like_flag'] = $like_flag;
            $e['dislike_flag'] = $dislike_flag;
            $e['comment_flag'] = $comment_flag;
            $e['report_flag'] = $report_flag;
            $e['my_flag'] = $my_flag;
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
    public function report(Request $request) {
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
        $contentId = $request->param('content_id');
        // 查看用户是否已经举报过
        $result = Db::name('operate')
            ->where([
                'from_user' => $userId,
                'object_id' => $contentId,
                'type' => 4
            ])
            ->find();
        if($result){
            $data = [
                'status' => 'success',
                'message' => '已举报'
            ];
            return json($data);
        } else {
            $to_user = Db::name('content')
                ->where('id','=', $contentId)
                ->field('user_id')
                ->find();
            // 举报不通知用户，因此 flag 为默认值 0
            Db::name('operate')
                ->insert([
                    'type' => 4,
                    'from_user' => $userId,
                    'to_user' => $to_user['user_id'],
                    'object_id' => $contentId
                ]);
            // 帖子表中举报用户加一
            Db::name('content')
                ->where('id', '=', $contentId)
                ->setInc('report_num', 1);
            $data = [
                'status' => 'success',
                'message' => '举报成功'
            ];
            return json($data);
        }
    }

    // 删除自己发布的帖子
    public function delete(Request $request) {
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
        $contentId = $request->param('content_id');
        $result = Db::name('content')
            ->where('id', '=', $contentId)
            ->field('user_id')
            ->find();
        if($result['user_id']==$userId) {
            // 删除帖子
            $flag = Db::name('content')
                ->where('id', '=', $contentId)
                ->update([
                    'is_delete' => 1
                ]);
            if($flag) {
                $data = [
                    'status' => 'success',
                    'message' => '删除成功'
                ];
                return json($data);
            } else {
                $data = [
                    'status' => 'fail',
                    'message' => '删除失败'
                ];
                return json($data);
            }
        }
    }

    // 帖子详情
    public function detail() {
        return;
    }

}