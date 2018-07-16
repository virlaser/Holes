<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/7/9
 * Time: 下午7:07
 */

namespace app\hole\controller;


use think\Controller;
use think\Db;
use think\Exception;
use think\Request;
use think\Session;

class Manage extends Controller {

    // 管理员登录页面
    public function adminLogin() {
        return $this->fetch('login');
    }

    // 管理员登录鉴权，session记录登录状态
    public function doLogin(Request $request) {
        $user = $request->param('user');
        $password = $request->param('password');
        $password = md5($password);
        try {
            $result = Db::name('admin')
                ->where([
                    'admin' => $user,
                    'password' => $password
                ])
                ->find();
        } catch(Exception $exception) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
        if($result) {
            Session::set('user', $user);
            $admin = Session::get('user');
            $this->assign('admin', $admin);
            // 鉴权成功，重定向到管理页面
            $this->redirect('/doManage');
        } else {
            $errorMessage = "用户名或密码错误";
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    // 返回管理页面
    public function doManage() {
        $user = Session::get('user');
        if($user) {
            try {
                $contents = Db::name('content')
                    ->where([
                        'verified' => 3,
                        'is_delete' => 0
                    ])
                    ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                    ->order('id desc')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('contents' ,$contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    // 按照点赞数递增排列
    public function sortLike() {
        $user = Session::get('user');
        if($user) {
            try {
                $contents = Db::name('content')
                    ->where([
                        'verified' => 3,
                        'is_delete' => 0
                    ])
                    ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                    ->order('like_num asc')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('flag', 'like');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    // 按照点踩数递增排列
    public function sortDislike() {
        $user = Session::get('user');
        if($user) {
            try {
                $contents = Db::name('content')
                    ->where([
                        'verified' => 3,
                        'is_delete' => 0
                    ])
                    ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                    ->order('dislike_num asc')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('flag', 'dislike');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    // 按照评论数递增排列
    public function sortComment() {
        $user = Session::get('user');
        if($user) {
            try {
                $contents = Db::name('content')
                    ->where([
                        'verified' => 3,
                        'is_delete' => 0
                    ])
                    ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                    ->order('comment_num asc')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('flag', 'comment');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    // 按照举报数递增排列
    public function sortReport() {
        $user = Session::get('user');
        if($user) {
            try {
                $contents = Db::name('content')
                    ->where([
                        'verified' => 3,
                        'is_delete' => 0
                    ])
                    ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                    ->order('report_num asc')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('flag', 'report');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    // 将置顶的帖子按照ID排列
    public function sortTop() {
        $user = Session::get('user');
        if($user) {
            try {
                $contents = Db::name('content')
                    ->where([
                        'verified' => 3,
                        'is_delete' => 0,
                        'flag' => 1
                    ])
                    ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                    ->order('like_num asc')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('flag', 'top');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    // 将标签按照出现的次数递增排列
    public function sortTag() {
        $user = Session::get('user');
        if($user) {
            try {
                $tags = Db::name('content')
                    ->where([
                        'verified' => ['>', 2],
                        'is_delete' => 0,
                        'tag' => ['NEQ', '']
                    ])
                    ->field('tag, count(*) as count')
                    ->order('count asc')
                    ->group('tag')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('tags', $tags);
            $this->assign('flag', 'tag');
            return $this->fetch('tag');
        } else {
            $this->redirect('/hole');
        }
    }

    // 将未审阅通过的帖子按照ID排列
    public function sortCheck() {
        $user = Session::get('user');
        if($user) {
            try {
                $contents = Db::name('content')
                    ->where([
                        'verified' => ['<', 3],
                        'is_delete' => 0,
                        'flag' => 0
                    ])
                    ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                    ->order('id asc')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('flag', 'check');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    // 将帖子按照登录用户的ID排列
    public function sortUserId() {
        $user = Session::get('user');
        if($user) {
            try {
                $contents = Db::name('content')
                    ->where([
                        'verified' => ['>', 2],
                        'is_delete' => 0,
                        'flag' => 0
                    ])
                    ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                    ->order('userV asc')
                    ->paginate(20);
            } catch (Exception $exception) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
            $this->assign('flag', 'userId');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    // 管理员删除帖子
    public function doDelete(Request $request) {
        $user = Session::get('user');
        $contentId = $request->param('contentId');
        if($user) {
            try {
                Db::name('content')
                    ->where([
                        'id' => $contentId
                    ])
                    ->update([
                        'is_delete' => 1
                    ]);
                Db::name('operate')
                    ->where([
                        'type' => ['IN', [1, 2, 3, 4]],
                        'object_id' => $contentId
                    ])
                    ->delete();
            } catch (Exception $exception){
                $data = [
                    'status' => 'fail',
                    'message' => '系统错误，请稍后再试'
                ];
                return json($data);
            }
            $data = [
                'status' => 'success',
                'message' => '删除成功'
            ];
            return json($data);
        } else {
            $data = [
                'status' => 'fail',
                'message' => '权限错误'
            ];
            return json($data);
        }
    }

    // 管理员封禁用户
    public function doBan(Request $request) {
        $user = Session::get('user');
        $userId = $request->param('userId');
        if($user) {
            try {
                Db::name('user')
                    ->where([
                        'id' => $userId
                    ])
                    ->update([
                        'ban' => 1
                    ]);
            } catch (Exception $exception) {
                $data = [
                    'status' => 'fail',
                    'message' => '系统错误，请稍后再试'
                ];
                return json($data);
            }
            $data = [
                'status' => 'success',
                'message' => '封禁成功'
            ];
            return json($data);
        } else {
            $data = [
                'status' => 'fail',
                'message' => '权限错误'
            ];
            return json($data);
        }
    }

    // 管理员对帖子置顶
    public function doTop(Request $request) {
        $user = Session::get('user');
        $contentId = $request->param('contentId');
        if($user) {
            try {
                $flag = Db::name('content')
                    ->where([
                        'id' => $contentId
                    ])
                    ->field('flag')
                    ->find();
                if ($flag['flag'] == 1) {
                    Db::name('content')
                        ->where([
                            'id' => $contentId
                        ])
                        ->update([
                            'flag' => 0
                        ]);
                    $data = [
                        'status' => 'success',
                        'message' => '取消置顶成功'
                    ];
                    return json($data);
                } else {
                    Db::name('content')
                        ->where([
                            'id' => $contentId
                        ])
                        ->update([
                            'flag' => 1
                        ]);
                    $data = [
                        'status' => 'success',
                        'message' => '置顶成功'
                    ];
                    return json($data);
                }
            } catch (Exception $exception) {
                $data = [
                    'status' => 'fail',
                    'message' => '系统错误，请稍后再试'
                ];
                return json($data);
            }
        } else {
            $data = [
                'status' => 'fail',
                'message' => '权限错误'
            ];
            return json($data);
        }
    }

    // 管理员删除标签
    public function doDeleteTag(Request $request) {
        $user = Session::get('user');
        $tag = $request->param('tag');
        if($user) {
            try {
                Db::name('content')
                    ->where([
                        'tag' => $tag
                    ])
                    ->update([
                        'tag' => ''
                    ]);
            } catch (Exception $exception) {
                $data = [
                    'status' => 'fail',
                    'message' => '系统错误，请稍后再试'
                ];
                return json($data);
            }
            $data = [
                'status' => 'success',
                'message' => '删除标签成功'
            ];
            return json($data);
        } else {
            $data = [
                'status' => 'fail',
                'message' => '权限错误'
            ];
            return json($data);
        }
    }

}