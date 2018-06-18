<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/27
 * Time: 下午11:01
 */

namespace app\hole\controller;


use think\Controller;
use think\Cookie;
use think\Db;
use think\Request;
use app\hole\common;

class User extends Controller {

    public function index(Request $request) {
        $isLogin = common\isLogin($request);
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            $user = Db::name('user')
                ->where([
                    'id' => $isLogin['user']
                ])
                ->field('nickname, avatar, create_time')
                ->find();
            $myNum = Db::name('content')
                ->where([
                    'userV' => $isLogin['user'],
                    'is_delete' => 0
                ])
                ->count();
            $activeNum = Db::name('operate')
                ->where([
                    'type' => ['IN', [1, 2, 3, 4]],
                    'from_user' => $isLogin['user']
                ])
                ->count();
            $infoNum = Db::name('operate')
                ->where([
                    'to_user' => $isLogin['user'],
                    'flag' => 0,
                    'from_user' => ['NEQ', $isLogin['user']]
                ])
                ->count();

            $date1 = date("Y-m-d");
            $date2 = explode(' ', $user['create_time'])[0];
            $d1 = strtotime($date1);
            $d2 = strtotime($date2);
            $days = round(($d1-$d2)/3600/24)+1;
            $user['time'] = $days;
            $message = [
                'myNum' => $myNum,
                'activeNum' => $activeNum,
                'infoNum' => $infoNum
            ];
            $this->assign('user', $user);
            $this->assign('message', $message);
            return $this->fetch();
        } else {
            return $this->fetch('login');
        }
    }

    public function my(Request $request) {
        $isLogin = common\isLogin($request);
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            $contents = Db::name('content')
                ->where([
                    'userV' => $isLogin['user'],
                    'is_delete' => 0
                ])
                ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
                ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
                ->order('hole_content.create_time desc')
                ->paginate(6, true);
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
        } else {
            return $this->fetch('login');
        }
    }

    public function myApi(Request $request) {
        $isLogin = common\isLogin($request);
        $contents = Db::name('content')
            ->where([
                'userV' => $isLogin['user'],
                'is_delete' => 0
            ])
            ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
            ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
            ->order('hole_content.create_time desc')
            ->paginate(6, true);
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

    public function delete(Request $request) {
        $isLogin = common\isLogin($request);
        $contentId = $request->param('contentId');
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            $user = Db::name('content')
                ->where([
                    'id' => $contentId
                ])
                ->field('userV')
                ->find();
            if($user['userV'] == $isLogin['user']) {
                Db::name('content')
                    ->where([
                        'id' => $contentId,
                        'userV' => $isLogin['user']
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
            }
        }
        $data = [
            'status' => 'fail',
            'message' => '认证错误'
        ];
        return json($data);
    }

    public function active(Request $request) {
        $isLogin = common\isLogin($request);
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            $data = array();
            $content = Db::name('operate')
                ->where([
                    'hole_operate.from_user' => $isLogin['user'],
                    'hole_operate.type' => ['IN', [1, 2, 3, 4]]
                ])
                ->join('hole_content', 'hole_operate.object_id=hole_content.id')
                ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
                ->field('hole_user.nickname, hole_user.avatar, hole_content.*, hole_operate.type, hole_operate.create_time as operate_time, count(hole_content.id)')
                ->group('hole_content.id')
                ->order('operate_time desc')
                ->paginate(6, true);
            foreach ($content as $e) {
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
        } else {
            return $this->fetch('login');
        }
    }

    public function activeApi(Request $request) {
        $isLogin = common\isLogin($request);
        $data = array();
        $contentId = Db::name('operate')
            ->where([
                'hole_operate.from_user' => $isLogin['user'],
                'hole_operate.type' => ['IN', [1, 2, 3, 4]]
            ])
            ->join('hole_content', 'hole_operate.object_id=hole_content.id')
            ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
            ->field('hole_user.nickname, hole_user.avatar, hole_content.*, hole_operate.type, hole_operate.create_time as operate_time, count(hole_content.id)')
            ->group('hole_content.id')
            ->order('operate_time desc')
            ->paginate(6, true);
        foreach ($contentId as $e) {
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

    public function info(Request $request) {
        $isLogin = common\isLogin($request);
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            $data = array();
            $content = Db::name('operate')
                ->where([
                    'to_user' => $isLogin['user'],
                    'type' => ['IN', [1,3]],
                    'hole_operate.flag' => 0,
                    'from_user' => ['NEQ', $isLogin['user']]
                ])
                ->join('hole_content', 'hole_operate.object_id=hole_content.id')
                ->join('hole_user', 'hole_operate.from_user=hole_user.id', 'LEFT')
                ->field('hole_user.nickname, hole_user.avatar, hole_content.*, hole_operate.type, hole_operate.create_time as operate_time')
                ->order('operate_time desc')
                ->paginate(6, true);
            $user = Db::name('user')
                ->where([
                    'id' => $isLogin['user']
                ])
                ->field('nickname')
                ->find();
            foreach ($content as $e) {
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
                Db::name('operate')
                    ->where([
                        'object_id' => $e['id'],
                        'type' => $e['type']
                    ])
                    ->update([
                        'flag' => 1
                    ]);
                // 数据查询返回的数据集不能动态添加数据，因此重新构造数据集
                $e['like_flag'] = $like_flag;
                $e['dislike_flag'] = $dislike_flag;
                $e['comment_flag'] = $comment_flag;
                array_push($data, $e);
            }
            $this->assign('contents', $data);
            $this->assign('myNick', $user['nickname']);
            return $this->fetch();
        } else {
            return $this->fetch('login');
        }
    }

    public function infoApi(Request $request) {
        $isLogin = common\isLogin($request);
        $contentList = array();
        $content = Db::name('operate')
            ->where([
                'to_user' => $isLogin['user'],
                'type' => ['IN', [1,3]],
                'hole_operate.flag' => 0,
                'from_user' => ['NEQ', $isLogin['user']]
            ])
            ->join('hole_content', 'hole_operate.object_id=hole_content.id')
            ->join('hole_user', 'hole_operate.from_user=hole_user.id', 'LEFT')
            ->field('hole_user.nickname, hole_user.avatar, hole_content.*, hole_operate.type, hole_operate.create_time as operate_time')
            ->order('operate_time desc')
            ->paginate(6, true);
        $user = Db::name('user')
            ->where([
                'id' => $isLogin['user']
            ])
            ->field('nickname')
            ->find();
        foreach ($content as $e) {
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
            Db::name('operate')
                ->where([
                    'object_id' => $e['id'],
                    'type' => $e['type']
                ])
                ->update([
                    'flag' => 1
                ]);
            // 数据查询返回的数据集不能动态添加数据，因此重新构造数据集
            $e['like_flag'] = $like_flag;
            $e['dislike_flag'] = $dislike_flag;
            $e['comment_flag'] = $comment_flag;
            array_push($contentList, $e);
        }
        $data = [
            'status' => 'success',
            'message' => '获取用户通知成功',
            'contentList' => $contentList,
            'myNick' => $user['nickname']
        ];
        return json($data);
    }

    public function login() {
        return $this->fetch();
    }

    public function doLogin(Request $request) {
        // todo 增加是否激活的判断
        $userMail = $request->param('userMail');
        $userPassword = $request->param('userPassword');
        $user = Db::name('user')
            ->where([
                'mail' => $userMail,
                'password' => md5($userPassword)
            ])
            ->find();
        if($user) {
            // todo cookie 安全性
            $identity = md5($user['mail'] . $user['password']);
            Db::name('user')
                ->where([
                    'mail' => $userMail,
                    'password' => md5($userPassword)
                ])
                ->update([
                    'identity' => $identity
                ]);
            common\setUserV($identity);
            $this->redirect('/user');
        } else {
            $errorMessage = "用户名或密码错误";
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function register() {
        return $this->fetch();
    }

    public function doRegister(Request $request) {
        $userName = $request->param('userName');
        $userMail = $request->param('userMail');
        $userPassword = $request->param('userPassword');
        $isRegisted = Db::name('user')
            ->where('mail', '=', $userMail)
            ->find();
        if($isRegisted) {
            $errorMessage = "此邮箱已经被注册";
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        } else {
            Db::name('user')
                ->insert([
                    'nickname' => $userName,
                    'mail' => $userMail,
                    'password' => md5($userPassword)
                ]);
            $errorMessage = "注册成功";
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function logout() {
        $identity = Cookie::get('hole_userV');
        Cookie::delete('userV', 'hole_');
        Db::name('user')
            ->where('identity', '=', $identity)
            ->update([
                'identity' => ' '
            ]);
        $this->redirect('/hole');
    }

    public function activate(Request $request) {
        
    }

}