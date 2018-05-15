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
use think\Exception;
use think\Request;

class Square extends Controller {

    // 显示主页帖子列表
    public function index(Request $request) {
        try {
            $data = array();
            if (isLogin($request)) {
                $userId = isLogin($request);
            } else {
                $data = [
                    'status' => 'fail',
                    'message' => '用户登录信息错误'
                ];
                return json($data);
            }
            // 查出帖子对应的点赞等信息以及以及发帖的用户信息，最后分页。置顶的帖子不算
            $lists = Db::table('hole_content')
                ->where([
                    'is_delete' => 0,
                    'flag' => 0
                ])
                ->join('hole_user', 'hole_content.user_id=hole_user.id')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_content.create_time desc')
                ->paginate(10, true);
            foreach ($lists as $e) {
                // 判断此用户是否点赞过帖子
                $like_flag = Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $e['id'],
                        'type' => 1
                    ])
                    ->find();
                $like_flag = $like_flag ? 1 : 0;
                // 判断此用户是否点踩过帖子
                $dislike_flag = Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $e['id'],
                        'type' => 2
                    ])
                    ->find();
                $dislike_flag = $dislike_flag ? 1 : 0;
                // 判断此用户是否评论过帖子
                $comment_flag = Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $e['id'],
                        'type' => 3
                    ])
                    ->find();
                $comment_flag = $comment_flag ? 1 : 0;
                // 判断此用户是否举报过帖子
                $report_flag = Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $e['id'],
                        'type' => 4
                    ])
                    ->find();
                $report_flag = $report_flag ? 1 : 0;
                // 判断此帖子是不是此用户写的
                $my_flag = ($e['user_id'] == $userId) ? 1 : 0;
                // 数据查询返回的数据集不能动态添加数据，因此重新构造数据集
                $e['like_flag'] = $like_flag;
                $e['dislike_flag'] = $dislike_flag;
                $e['comment_flag'] = $comment_flag;
                $e['report_flag'] = $report_flag;
                $e['my_flag'] = $my_flag;
                array_push($data, $e);
            }
            return json($data);
        } catch (Exception $exception){
            $data = [
                'status' => 'fail',
                'message' => $exception->getMessage()
            ];
            return json($data);
        }
    }

    // 显示主页置顶帖子列表
    public function top(Request $request) {
        try {
            $data = array();
            if (isLogin($request)) {
                $userId = isLogin($request);
            } else {
                $data = [
                    'status' => 'fail',
                    'message' => '用户登录信息错误'
                ];
                return json($data);
            }
            // 查出帖子对应的点赞等信息以及以及发帖的用户信息，最后分页
            $lists = Db::table('hole_content')
                ->where([
                    'is_delete' => 0,
                    'flag' => 1
                ])
                ->join('hole_user', 'hole_content.user_id=hole_user.id')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_content.create_time desc')
                ->select();
            foreach ($lists as $e) {
                // 判断此用户是否点赞过帖子
                $like_flag = Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $e['id'],
                        'type' => 1
                    ])
                    ->find();
                $like_flag = $like_flag ? 1 : 0;
                // 判断此用户是否点踩过帖子
                $dislike_flag = Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $e['id'],
                        'type' => 2
                    ])
                    ->find();
                $dislike_flag = $dislike_flag ? 1 : 0;
                // 判断此用户是否评论过帖子
                $comment_flag = Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $e['id'],
                        'type' => 3
                    ])
                    ->find();
                $comment_flag = $comment_flag ? 1 : 0;
                // 数据查询返回的数据集不能动态添加数据，因此重新构造数据集
                $e['like_flag'] = $like_flag;
                $e['dislike_flag'] = $dislike_flag;
                $e['comment_flag'] = $comment_flag;
                array_push($data, $e);
            }
            return json($data);
        } catch (Exception $exception){
            $data = [
                'status' => 'fail',
                'message' => $exception->getMessage()
            ];
            return json($data);
        }
    }

    // 用户对帖子点赞
    public function like(Request $request) {
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
            // 检查用户是否点赞过，没有就增加记录，有就删除记录
            $flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $contentId,
                    'type' => 1
                ])
                ->find();
            if ($flag) {
                // 删除用户点赞操作
                Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $contentId,
                        'type' => 1
                    ])
                    ->delete();
                // 将帖子的点赞记录减一
                Db::name('content')
                    ->where('id', '=', $contentId)
                    ->setDec('like_num', 1);
                $data = [
                    'status' => 'success',
                    'message' => '取消赞成功'
                ];
                return json($data);
            } else {
                // 添加用户操作，给 to_user 通知
                $toUser = Db::name('content')
                    ->where('id', '=', $contentId)
                    ->field('user_id')
                    ->find();
                $result = Db::name('operate')
                    ->insert([
                        'type' => 1,
                        'from_user' => $userId,
                        'to_user' => $toUser['user_id'],
                        'object_id' => $contentId,
                        'flag' => 0
                    ]);
                if ($result) {
                    // 增加点赞记录
                    Db::name('content')
                        ->where('id', '=', $contentId)
                        ->setInc('like_num', 1);
                    $data = [
                        'status' => 'success',
                        'message' => '点赞成功'
                    ];
                    return json($data);
                } else {
                    $data = [
                        'status' => 'fail',
                        'message' => '点赞失败'
                    ];
                    return json($data);
                }
            }
        } catch (Exception $exception) {
            $data = [
                'status' => 'fail',
                'message' => $exception->getMessage()
            ];
            return json($data);
        }
    }

    // 用户给帖子点踩
    public function dislike(Request $request) {
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
            $flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $contentId,
                    'type' => 2
                ])
                ->find();
            if ($flag) {
                Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $contentId,
                        'type' => 2
                    ])
                    ->delete();
                Db::name('content')
                    ->where('id', '=', $contentId)
                    ->setDec('dislike_num', 1);
                $data = [
                    'status' => 'success',
                    'message' => '取消踩成功'
                ];
                return json($data);
            } else {
                // 点踩不需要通知用户，将 to_user 设为 0
                $result = Db::name('operate')
                    ->insert([
                        'type' => 2,
                        'from_user' => $userId,
                        'to_user' => 0,
                        'object_id' => $contentId,
                        'flag' => 1
                    ]);
                if ($result) {
                    Db::name('content')
                        ->where('id', '=', $contentId)
                        ->setInc('dislike_num', 1);
                    $data = [
                        'status' => 'success',
                        'message' => '点踩成功'
                    ];
                    return json($data);
                } else {
                    $data = [
                        'status' => 'fail',
                        'message' => '点踩失败'
                    ];
                    return json($data);
                }
            }
        } catch (Exception $exception) {
            $data = [
                'status' => 'fail',
                'message' => $exception->getMessage()
            ];
            return json($data);
        }
    }

    // 用户举报帖子
    public function report(Request $request) {
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
            $result = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $contentId,
                    'type' => 4
                ])
                ->find();
            if ($result) {
                $data = [
                    'status' => 'success',
                    'message' => '已举报'
                ];
                return json($data);
            } else {
                // 举报帖子不需要通知用户，将 to_user 设为 0
                Db::name('operate')
                    ->insert([
                        'type' => 4,
                        'from_user' => $userId,
                        'to_user' => 0,
                        'object_id' => $contentId,
                        'flag' => 1
                    ]);
                Db::name('content')
                    ->where('id', '=', $contentId)
                    ->setInc('report_num', 1);
                $data = [
                    'status' => 'success',
                    'message' => '举报成功'
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

    // 删除帖子，只是把帖子的 is_delete 标志位变成 1 然后删除相应的操作
    public function delete(Request $request) {
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
            $result = Db::name('content')
                ->where('id', '=', $contentId)
                ->field('user_id')
                ->find();
            if ($result['user_id'] == $userId) {
                $flag = Db::name('content')
                    ->where('id', '=', $contentId)
                    ->update([
                        'is_delete' => 1
                    ]);
                // 删除用户对此帖子的操作，如：点赞，点踩，评论，举报
                // 用户从对评论点赞的通知进入仍然可以看到被删除的帖子
                Db::name('operate')
                    ->where([
                        'type' => ['in', [1, 2, 3, 4]],
                        'object_id' => $contentId
                    ])
                    ->delete();
                if ($flag) {
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
            } else {
                $data = [
                    'status' => 'fail',
                    'message' => '用户信息错误'
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

    // 帖子详情
    public function detail(Request $request) {
        try {
            $data = array();
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
            // 得到一个帖子下面的评论并对评论进行分页
            $lists = Db::table('hole_comment')
                ->join('hole_user', 'hole_comment.user_id=hole_user.id')
                ->where('content_id', '=', $contentId)
                ->field('hole_comment.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_comment.create_time desc')
                ->paginate(5, true);
            foreach ($lists as $list) {
                // 查看此用户是否对此评论点赞过
                $like_flag = Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'to_user' => $list['user_id'],
                        'object_id' => $list['id'],
                        'type' => 5
                    ])
                    ->find();
                $like_flag = $like_flag ? 1 : 0;
                $list['like_flag'] = $like_flag;
                array_push($data, $list);
            }
            return json($data);
        } catch (Exception $exception) {
            $data = [
                'status' => 'fail',
                'message' => $exception->getMessage()
            ];
            return json($data);
        }
    }

    // 对帖子的评论点赞
    public function commentlike(Request $request) {
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
            $commentId = $request->param('comment_id');
            // 检查用户是否点赞过，没有就增加记录，有就删除记录
            $flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $commentId,
                    'type' => 5
                ])
                ->find();
            if ($flag) {
                Db::name('operate')
                    ->where([
                        'from_user' => $userId,
                        'object_id' => $commentId,
                        'type' => 5
                    ])
                    ->delete();
                Db::name('comment')
                    ->where('id', '=', $commentId)
                    ->setDec('like_num', 1);
                $data = [
                    'status' => 'success',
                    'message' => '取消评论赞成功'
                ];
                return json($data);
            } else {
                // 增加用户操作，给 to_user 通知
                $toUser = Db::name('comment')
                    ->where('id', '=', $commentId)
                    ->field('user_id')
                    ->find();
                $result = Db::name('operate')
                    ->insert([
                        'type' => 5,
                        'from_user' => $userId,
                        'to_user' => $toUser['user_id'],
                        'object_id' => $commentId,
                        'flag' => 0
                    ]);
                if ($result) {
                    Db::name('comment')
                        ->where('id', '=', $commentId)
                        ->setInc('like_num', 1);
                    $data = [
                        'status' => 'success',
                        'message' => '增加评论赞成功'
                    ];
                    return json($data);
                } else {
                    $data = [
                        'status' => 'fail',
                        'message' => '增加评论赞失败'
                    ];
                    return json($data);
                }
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