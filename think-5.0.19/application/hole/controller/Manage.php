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
        $result = Db::name('admin')
            ->where([
                'admin' => $user,
                'password' => $password
            ])
            ->find();
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
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0
                ])
                ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                ->order('id desc')
                ->paginate(20);
            $this->assign('contents' ,$contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortLike() {
        $user = Session::get('user');
        if($user) {
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0
                ])
                ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                ->order('like_num asc')
                ->paginate(20);
            $this->assign('flag', 'like');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortDislike() {
        $user = Session::get('user');
        if($user) {
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0
                ])
                ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                ->order('dislike_num asc')
                ->paginate(20);
            $this->assign('flag', 'dislike');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortComment() {
        $user = Session::get('user');
        if($user) {
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0
                ])
                ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                ->order('comment_num asc')
                ->paginate(20);
            $this->assign('flag', 'comment');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortReport() {
        $user = Session::get('user');
        if($user) {
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0
                ])
                ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                ->order('report_num asc')
                ->paginate(20);
            $this->assign('flag', 'report');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortTop() {
        $user = Session::get('user');
        if($user) {
            $contents = Db::name('content')
                ->where([
                    'verified' => 3,
                    'is_delete' => 0,
                    'flag' => 1
                ])
                ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                ->order('like_num asc')
                ->paginate(20);
            $this->assign('flag', 'top');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortTag() {
        $user = Session::get('user');
        if($user) {
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
            $this->assign('tags', $tags);
            $this->assign('flag', 'tag');
            return $this->fetch('tag');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortCheck() {
        $user = Session::get('user');
        if($user) {
            $contents = Db::name('content')
                ->where([
                    'verified' => ['<', 3],
                    'is_delete' => 0,
                    'flag' => 0
                ])
                ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                ->order('id asc')
                ->paginate(20);
            $this->assign('flag', 'check');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortUserId() {
        $user = Session::get('user');
        if($user) {
            $contents = Db::name('content')
                ->where([
                    'verified' => ['>', 2],
                    'is_delete' => 0,
                    'flag' => 0
                ])
                ->field('id, content, userV, userT, tag, like_num, dislike_num, comment_num, report_num')
                ->order('userV asc')
                ->paginate(20);
            $this->assign('flag', 'userId');
            $this->assign('contents', $contents);
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function doDelete(Request $request) {
        $user = Session::get('user');
        $contentId = $request->param('contentId');
        if($user) {
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

    public function doBan(Request $request) {
        $user = Session::get('user');
        $userId = $request->param('userId');
        if($user) {
            Db::name('user')
                ->where([
                    'id' => $userId
                ])
                ->update([
                    'ban' => 1
                ]);
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

    public function doTop(Request $request) {
        $user = Session::get('user');
        $contentId = $request->param('contentId');
        if($user) {
            $flag = Db::name('content')
                ->where([
                    'id' => $contentId
                ])
                ->field('flag')
                ->find();
            if($flag['flag'] == 1) {
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
        } else {
            $data = [
                'status' => 'fail',
                'message' => '权限错误'
            ];
            return json($data);
        }
    }

    public function doDeleteTag(Request $request) {
        $user = Session::get('user');
        $tag = $request->param('tag');
        if($user) {
            Db::name('content')
                ->where([
                    'tag' => $tag
                ])
                ->update([
                    'tag' => ''
                ]);
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

    public function test() {
        for($i=0;$i<1000000;$i++) {
//            Db::name('content')
//                ->insert([
//                    'content' => md5('这是第'.$i.'条内容'),
//                    'userT' => md5(rand(100, 10000)+$i),
//                    'verified' => 3,
//                    'tag' => 'test'.rand(1,10),
//                    'like_num' => rand(100, 10000),
//                    'dislike_num' => rand(10, 100),
//                    'comment_num' => rand(100, 1000),
//                    'report_num' => rand(10, 100)
//                ]);
//            Db::name('operate')
//                ->insert([
//                    'type' => rand(1,6),
//                    'identity' => md5(rand(100, 10000)),
//                    'from_user' => md5(rand(1000, 100000)),
//                    'to_user' => md5(rand(1000, 100000)),
//                    'object_id' => rand(1000, 100000)
//                ]);
        }
        return '<h1>finished</h1>';
    }

}