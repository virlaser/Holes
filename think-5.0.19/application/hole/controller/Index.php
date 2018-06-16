<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/21
 * Time: 下午3:25
 */

namespace app\hole\controller;


use think\Controller;
use app\hole\common;
use think\Db;
use think\Request;

class Index extends Controller {

    public function index(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contents = Db::name('content')
            ->where([
                'verified' => 5,
                'is_delete' => 0,
                'flag' => 0
            ])
            ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
            ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
            ->order('hole_content.create_time desc')
            ->paginate(10, true);
        $data = array();
        foreach ($contents as $e) {
            $like_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                    'object_id' => $e['id'],
                    'type' => 1
                ])
                ->find();
            $like_flag = $like_flag ? 1 : 0;
            // 判断此用户是否点踩过帖子
            $dislike_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                    'object_id' => $e['id'],
                    'type' => 2
                ])
                ->find();
            $dislike_flag = $dislike_flag ? 1 : 0;
            // 判断此用户是否评论过帖子
            $comment_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
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
        $this->assign('contents', $data);
        return $this->fetch();
    }

    public function contentApi(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contents = Db::name('content')
            ->where([
                'verified' => 5,
                'is_delete' => 0,
                'flag' => 0
            ])
            ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
            ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
            ->order('hole_content.create_time desc')
            ->paginate(10, true);
        $data = array();
        foreach ($contents as $e) {
            $like_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                    'object_id' => $e['id'],
                    'type' => 1
                ])
                ->find();
            $like_flag = $like_flag ? 1 : 0;
            // 判断此用户是否点踩过帖子
            $dislike_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                    'object_id' => $e['id'],
                    'type' => 2
                ])
                ->find();
            $dislike_flag = $dislike_flag ? 1 : 0;
            // 判断此用户是否评论过帖子
            $comment_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
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
    }

    public function create() {
        return $this->fetch('create');
    }

    public function doCreate(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $content = $request->param('content');
        // if choose, $hide = 'on'
        $hide = $request->param('hide');
        $hide = $hide == 'on' ? 1 : 0;
        Db::name('content')
            ->insert([
                'content' => $content,
                $isLogin['type']=='userV'?'userV':'userT' => $isLogin['user']?$isLogin['user']:0,
                'verified' => $isLogin['type']=='userV'?5:0,
                'hide' => $hide,
            ]);
        $this->redirect('/hole');
    }

    public function comment(Request $request) {
        common\setUserT($this->request);
        $isLogin = common\isLogin($request);
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            $contentId = $request->param('contentId');
            $this->assign('contentId', $contentId);
            return $this->fetch();
        } else {
            $this->redirect('/login');
        }
    }

    public function doComment(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $content = $request->param('content');
        $contentId = $request->param('contentId');
        $hide = $request->param('hide');
        $to_user = Db::name('content')
            ->where([
                'id' => $contentId
            ])
            ->find();
        Db::name('comment')
            ->insert([
                'content' => $content,
                'content_id' => $contentId,
                'userV' => $isLogin['user'],
                'hide' => $hide
            ]);
        Db::name('operate')
            ->insert([
                'type' => 3,
                'from_user' => $isLogin['user'],
                'to_user' => $to_user['userV']?$to_user['userV']:0,
                'object_id' => $contentId
            ]);
        Db::name('content')
            ->where([
                'id' => $contentId
            ])
            ->setInc('comment_num');
        $data = [
            'status' => 'success',
            'message' => '评论成功'
        ];
        return json($data);
    }

    public function detail(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contentId = (int) $request->param('contentId');
        $data = array();
        $data2 = array();
        $content = Db::name('content')
            ->where([
                'hole_content.id' => $contentId
            ])
            ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
            ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
            ->find();
        $like_flag = Db::name('operate')
            ->where([
                $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                'object_id' => $content['id'],
                'type' => 1
            ])
            ->find();
        $like_flag = $like_flag ? 1 : 0;
        $dislike_flag = Db::name('operate')
            ->where([
                $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                'object_id' => $content['id'],
                'type' => 2
            ])
            ->find();
        $dislike_flag = $dislike_flag ? 1 : 0;
        $comment_flag = Db::name('operate')
            ->where([
                $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                'object_id' => $content['id'],
                'type' => 3
            ])
            ->find();
        $comment_flag = $comment_flag ? 1 : 0;
        $content['like_flag'] = $like_flag;
        $content['dislike_flag'] = $dislike_flag;
        $content['comment_flag'] = $comment_flag;
        array_push($data, $content);

        $comments = Db::name('comment')
            ->where([
                'content_id' => $contentId
            ])
            ->join('hole_user', 'hole_comment.userV=hole_user.id')
            ->field('hole_comment.*, hole_user.nickname')
            ->order('hole_comment.create_time desc')
            ->paginate(5, true);
        foreach($comments as $comment) {
            $like_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                    'object_id' => $comment['id'],
                    'type' => 5
                ])
                ->find();
            $like_flag = $like_flag ? 1 : 0;
            $dislike_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                    'object_id' => $comment['id'],
                    'type' => 6
                ])
                ->find();
            $dislike_flag = $dislike_flag ? 1 : 0;
            $comment['like_flag'] = $like_flag;
            $comment['dislike_flag'] = $dislike_flag;
            array_push($data2, $comment);
        }
        $this->assign('content', $data[0]);
        $this->assign('comments', $data2);
        return $this->fetch();
    }

    public function commentApi(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contentId = (int) $request->param('contentId');
        $data = array();
        $comments = Db::name('comment')
            ->where([
                'content_id' => $contentId
            ])
            ->join('hole_user', 'hole_comment.userV=hole_user.id')
            ->field('hole_comment.*, hole_user.nickname')
            ->order('hole_comment.create_time desc')
            ->paginate(5, true);
        foreach($comments as $comment) {
            $like_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                    'object_id' => $comment['id'],
                    'type' => 5
                ])
                ->find();
            $like_flag = $like_flag ? 1 : 0;
            $dislike_flag = Db::name('operate')
                ->where([
                    $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                    'object_id' => $comment['id'],
                    'type' => 6
                ])
                ->find();
            $dislike_flag = $dislike_flag ? 1 : 0;
            $comment['like_flag'] = $like_flag;
            $comment['dislike_flag'] = $dislike_flag;
            array_push($data, $comment);
        }
        return json($data);
    }

    // todo 换为 redis
    public function operate(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contentId = $request->param('contentId');
        $type = $request->param('type');

        // 点赞/取消赞
        if($type == 1) {
            $result = Db::name('operate')
                ->where([
                    'type' => 1,
                    $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                    'object_id' => $contentId
                ])
                ->find();
            if($result) {
                Db::name('operate')
                    ->where([
                        'type' => 1,
                        $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                        'object_id' => $contentId
                    ])
                    ->delete();
                Db::name('content')
                    ->where([
                        'id' => $contentId
                    ])
                    ->setDec('like_num');
                $data = [
                    'status' => 'success',
                    'message' => '取消赞成功'
                ];
                return json($data);
            } else {
                $toUser = Db::name('content')
                    ->where([
                        'id' => $contentId,
                        'userV' => ['>', 0],
                    ])
                    ->field('userV')
                    ->find();
                Db::name('operate')
                    ->insert([
                        'type' => 1,
                        $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                        'to_user' => $toUser['userV']?$toUser['userV']:0,
                        'object_id' => $contentId
                    ]);
                Db::name('content')
                    ->where([
                        'id' => $contentId
                    ])
                    ->setInc('like_num');
                $data = [
                    'status' => 'success',
                    'message' => '点赞成功'
                ];
                return json($data);
            }
        }

        // 点踩/取消踩
        if($type == 2) {
            $result = Db::name('operate')
                ->where([
                    'type' => 2,
                    $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                    'object_id' => $contentId
                ])
                ->find();
            if($result) {
                Db::name('operate')
                    ->where([
                        'type' => 2,
                        $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                        'object_id' => $contentId
                    ])
                    ->delete();
                Db::name('content')
                    ->where([
                        'id' => $contentId
                    ])
                    ->setDec('dislike_num');
                $data = [
                    'status' => 'success',
                    'message' => '取消踩成功'
                ];
                return json($data);
            } else {
                Db::name('operate')
                    ->insert([
                        'type' => 2,
                        $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                        // 点踩不通知用户
                        'to_user' => 0,
                        'object_id' => $contentId
                    ]);
                Db::name('content')
                    ->where([
                        'id' => $contentId
                    ])
                    ->setInc('dislike_num');
                $data = [
                    'status' => 'success',
                    'message' => '点踩成功'
                ];
                return json($data);
            }
        }

        // 评论
        if($type == 3) {

        }

        // 举报
        if($type == 4) {

        }

        // 给评论点赞
        if($type == 5) {
            $result = Db::name('operate')
                ->where([
                    'type' => 5,
                    $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                    'object_id' => $contentId
                ])
                ->find();
            if($result) {
                Db::name('operate')
                    ->where([
                        'type' => 5,
                        $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                        'object_id' => $contentId
                    ])
                    ->delete();
                Db::name('comment')
                    ->where([
                        'id' => $contentId
                    ])
                    ->setDec('like_num');
                $data = [
                    'status' => 'success',
                    'message' => '取消赞成功'
                ];
                return json($data);
            } else {
                $toUser = Db::name('comment')
                    ->where([
                        'id' => $contentId,
                    ])
                    ->field('userV')
                    ->find();
                Db::name('operate')
                    ->insert([
                        'type' => 5,
                        $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                        'to_user' => $toUser['userV']?$toUser['userV']:0,
                        'object_id' => $contentId
                    ]);
                Db::name('comment')
                    ->where([
                        'id' => $contentId
                    ])
                    ->setInc('like_num');
                $data = [
                    'status' => 'success',
                    'message' => '点赞成功'
                ];
                return json($data);
            }
        }

        // 给评论点踩
        if($type == 6) {
            $result = Db::name('operate')
                ->where([
                    'type' => 6,
                    $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                    'object_id' => $contentId
                ])
                ->find();
            if($result) {
                Db::name('operate')
                    ->where([
                        'type' => 6,
                        $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                        'object_id' => $contentId
                    ])
                    ->delete();
                Db::name('comment')
                    ->where([
                        'id' => $contentId
                    ])
                    ->setDec('dislike_num');
                $data = [
                    'status' => 'success',
                    'message' => '取消踩成功'
                ];
                return json($data);
            } else {
                Db::name('operate')
                    ->insert([
                        'type' => 6,
                        $isLogin['type']=='userT'?'identity':'from_user' => $isLogin['user'],
                        // 点踩不通知用户
                        'to_user' => 0,
                        'object_id' => $contentId
                    ]);
                Db::name('comment')
                    ->where([
                        'id' => $contentId
                    ])
                    ->setInc('dislike_num');
                $data = [
                    'status' => 'success',
                    'message' => '点踩成功'
                ];
                return json($data);
            }
        }

        // 给帖子审阅
        if($type == 7) {

        }
    }

}