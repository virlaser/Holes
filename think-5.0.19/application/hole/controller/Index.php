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
use think\Exception;
use think\Request;

class Index extends Controller {

    // 树洞主页
    public function index(Request $request) {
        // 如果用户未登录就给用户种植未登录用户的标识 cookie
        common\setUserT($request);
        // 如果用户未登录就得到未登录的标识，用来记录用户操作
        // 如果用户登录了就得到用户的 id
        $isLogin = common\isLogin($request);
        try {
            // 得到标签
            $tags = Db::name('content')
                ->where([
                    'tag' => ['NEQ', '']
                ])
                ->field('count(tag) as tagNum, tag')
                ->group('tag')
                ->order('tagNum desc')
                ->paginate(6, true);
            // 得到置顶的帖子
            $topContents = Db::name('content')
                ->where([
                    // 判断帖子是不是置顶的帖子
                    'flag' => 1
                ])
                ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_content.create_time desc')
                ->select();
            // 得到普通的帖子
            $contents = Db::name('content')
                ->where([
                    // 未登录用户发的帖子需要三个人审核通过才能发表
                    // 登录用户的帖子创建时 verified 就等于 3
                    'verified' => 3,
                    // 判断此帖子是不是被用户删除
                    'is_delete' => 0,
                    'flag' => 0
                ])
                ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_content.create_time desc')
                // 分页，简单模式，上拉自动加载
                ->paginate(10, true);
            $data = common\getContentFlag($isLogin, $contents);
            $data2 = common\getContentFlag($isLogin, $topContents);
            $this->assign('contents', $data);
            $this->assign('topContents', $data2);
            $this->assign('tags', $tags);
            return $this->fetch();
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    // 用来前端动态请求主页帖子
    public function contentApi(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        try {
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0,
                    'flag' => 0
                ])
                ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_content.create_time desc')
                ->paginate(10, true);
            $data = common\getContentFlag($isLogin, $contents);
            return json($data);
        } catch (Exception $e) {
            $data = [
                'status' => 'fail',
                'message' => '系统错误，请稍后再试'
            ];
            return json($data);
        }
    }

    // 标签详细页面
    public function tag(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $tag = $request->param('tag');
        try {
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0,
                    'flag' => 0,
                    'tag' => $tag
                ])
                ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_content.create_time desc')
                ->paginate(10, true);
            $data = common\getContentFlag($isLogin, $contents);
            $this->assign('contents', $data);
            $this->assign('tag', $tag);
            return $this->fetch();
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    // 动态加载标签详细页面帖子接口
    public function tagApi(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $tag = $request->param('tag');
        try {
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0,
                    'flag' => 0,
                    'tag' => $tag
                ])
                ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_content.create_time desc')
                ->paginate(10, true);
            $data = common\getContentFlag($isLogin, $contents);
            return json($data);
        } catch (Exception $e) {
            $data = [
                'status' => 'fail',
                'message' => '系统错误，请稍后再试'
            ];
            return json($data);
        }
    }

    // 发帖页面
    public function create() {
        return $this->fetch('create');
    }

    // 发帖逻辑
    public function doCreate(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $content = $request->param('content');
        // 如果用户发帖时选择了匿名，此处 hide == on
        $hide = $request->param('hide');
        $hide = $hide == 'on' ? 1 : 0;
        $tag = $request->param('tag');
        if($tag) {
            $tag = str_replace(array("_","=","+"," ","&","?",">","<","#","(",")","@","$","*","/","-"),"",$tag);
        }
        Db::name('content')
            ->insert([
                'content' => $content,
                $isLogin['type']=='userV'?'userV':'userT' => $isLogin['user']?$isLogin['user']:0,
                // 登录用户发帖可以直接显示
                // 未登录用户发帖后要被至少三个人同意发表帖子，帖子才能可见
                'verified' => $isLogin['type']=='userV'?3:0,
                'hide' => $hide,
                'tag' => $tag
            ]);
        $this->redirect('/hole');
    }

    // 评论页面
    public function comment(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        // 评论必须登录，否则重定向到登录页面
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            $contentId = $request->param('contentId');
            $this->assign('contentId', $contentId);
            return $this->fetch();
        } else {
            $this->redirect('/login');
        }
    }

    // 评论逻辑
    public function doComment(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $content = $request->param('content');
        $contentId = $request->param('contentId');
        $hide = $request->param('hide');
        try {
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
                    'to_user' => $to_user['userV'] ? $to_user['userV'] : 0,
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
        } catch (Exception $e) {
            $data = [
                'status' => 'fail',
                'message' => '系统错误，请稍后再试'
            ];
            return json($data);
        }
    }

    // 帖子详细信息页面
    public function detail(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contentId = (int) $request->param('contentId');
        try {
            $content = Db::name('content')
                ->where([
                    'hole_content.id' => $contentId
                ])
                ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                // 此处使用 select 否则公共函数中的 foreach 循环会出问题
                ->select();
            $data = common\getContentFlag($isLogin, $content);
            $comments = Db::name('comment')
                ->where([
                    'content_id' => $contentId
                ])
                ->join('hole_user', 'hole_comment.userV=hole_user.id')
                ->field('hole_comment.*, hole_user.nickname')
                ->order('hole_comment.create_time desc')
                ->paginate(5, true);
            $data2 = common\getCommentFlag($isLogin, $comments);
            // 详情页面只有一条帖子信息，因此取 $data[0]
            $this->assign('content', $data[0]);
            $this->assign('comments', $data2);
            return $this->fetch();
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    // 前端动态加载评论的接口
    public function commentApi(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contentId = (int) $request->param('contentId');
        try {
            $comments = Db::name('comment')
                ->where([
                    'content_id' => $contentId
                ])
                ->join('hole_user', 'hole_comment.userV=hole_user.id')
                ->field('hole_comment.*, hole_user.nickname')
                ->order('hole_comment.create_time desc')
                ->paginate(5, true);
            $data = common\getCommentFlag($isLogin, $comments);
            return json($data);
        } catch (Exception $e) {
            $data = [
                'status' => 'fail',
                'message' => '系统错误，请稍后再试'
            ];
            return json($data);
        }
    }

    // todo 换为 redis
    public function operate(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contentId = $request->param('contentId');
        $type = $request->param('type');
        try {
            // 点赞/取消赞
            if ($type == 1) {
                // 查看用户是否点赞过
                $result = Db::name('operate')
                    ->where([
                        'type' => 1,
                        $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
                        'object_id' => $contentId
                    ])
                    ->find();
                // 如果以前点赞过，删除以前的点赞操作，将帖子点赞数减一
                if ($result) {
                    Db::name('operate')
                        ->where([
                            'type' => 1,
                            $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
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
                    // 如果以前没有点赞过，查出被点赞帖子所属用户，给她通知，记录点赞操作，将帖子点赞数加一
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
                            $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
                            'to_user' => $toUser['userV'] ? $toUser['userV'] : 0,
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
            if ($type == 2) {
                $result = Db::name('operate')
                    ->where([
                        'type' => 2,
                        $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
                        'object_id' => $contentId
                    ])
                    ->find();
                if ($result) {
                    Db::name('operate')
                        ->where([
                            'type' => 2,
                            $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
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
                            $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
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

            // 举报
            if ($type == 4) {
                $result = Db::name('operate')
                    ->where([
                        $isLogin['type'] === 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'type' => 4,
                        'object_id' => $contentId
                    ])
                    ->find();
                if ($result) {
                    $data = [
                        'status' => 'fail',
                        'message' => '您已经举报过这条帖子'
                    ];
                    return json($data);
                } else {
                    Db::name('operate')
                        ->insert([
                            'type' => 4,
                            $isLogin['type'] === 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            // 举报不通知用户
                            'to_user' => 0,
                            'flag' => 0,
                            'object_id' => $contentId
                        ]);
                    Db::name('content')
                        ->where([
                            'id' => $contentId
                        ])
                        ->setInc('report_num');
                    $data = [
                        'status' => 'success',
                        'message' => '举报成功'
                    ];
                    return json($data);
                }
            }

            // 给评论点赞
            if ($type == 5) {
                $result = Db::name('operate')
                    ->where([
                        'type' => 5,
                        $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
                        'object_id' => $contentId
                    ])
                    ->find();
                if ($result) {
                    Db::name('operate')
                        ->where([
                            'type' => 5,
                            $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
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
                            $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
                            'to_user' => $toUser['userV'] ? $toUser['userV'] : 0,
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
            if ($type == 6) {
                $result = Db::name('operate')
                    ->where([
                        'type' => 6,
                        $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
                        'object_id' => $contentId
                    ])
                    ->find();
                if ($result) {
                    Db::name('operate')
                        ->where([
                            'type' => 6,
                            $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
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
                            $isLogin['type'] == 'userT' ? 'identity' : 'from_user' => $isLogin['user'],
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
        } catch (Exception $e) {
            $data = [
                'status' => 'fail',
                'message' => '系统错误，请稍后再试'
            ];
            return json($data);
        }
    }

}