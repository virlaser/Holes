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
use think\Exception;
use think\Request;
use app\hole\common;

class User extends Controller {

    public function index(Request $request) {
        $isLogin = common\isLogin($request);
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            try {
                // 得到用户信息
                $user = Db::name('user')
                    ->where([
                        'id' => $isLogin['user']
                    ])
                    ->field('nickname, avatar, create_time')
                    ->find();
                // 得到用户发帖数
                $myNum = Db::name('content')
                    ->where([
                        'userV' => $isLogin['user'],
                        'is_delete' => 0
                    ])
                    ->count();
                // 得到用户动态数
                // 仅包含 点赞 点踩 评论 举报
                $activeNum = Db::name('operate')
                    ->where([
                        'type' => ['IN', [1, 2, 3, 4]],
                        'from_user' => $isLogin['user']
                    ])
                    ->count();
                // 得到用户通知数
                // 仅包含 点赞 点踩 评论 举报 的通知
                $infoNum = Db::name('operate')
                    ->where([
                        'to_user' => $isLogin['user'],
                        'flag' => 0,
                        'from_user' => ['NEQ', $isLogin['user']]
                    ])
                    ->count();
                // 计算用户从注册到今天的天数
                $date1 = date("Y-m-d");
                $date2 = explode(' ', $user['create_time'])[0];
                $d1 = strtotime($date1);
                $d2 = strtotime($date2);
                $days = round(($d1 - $d2) / 3600 / 24) + 1;
                $user['time'] = $days;
                $message = [
                    'myNum' => $myNum,
                    'activeNum' => $activeNum,
                    'infoNum' => $infoNum
                ];
                $this->assign('user', $user);
                $this->assign('message', $message);
                return $this->fetch();
            } catch (Exception $e) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
        } else {
            return $this->fetch('login');
        }
    }

    public function my(Request $request) {
        $isLogin = common\isLogin($request);
        // 得到用户发的帖子
        // 如果用户没有登录直接重定向到登录页面
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            try {
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
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 1
                        ])
                        ->find();
                    $like_flag = $like_flag ? 1 : 0;
                    $dislike_flag = Db::name('operate')
                        ->where([
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 2
                        ])
                        ->find();
                    $dislike_flag = $dislike_flag ? 1 : 0;
                    $comment_flag = Db::name('operate')
                        ->where([
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 3
                        ])
                        ->find();
                    $comment_flag = $comment_flag ? 1 : 0;
                    $e['like_flag'] = $like_flag;
                    $e['dislike_flag'] = $dislike_flag;
                    $e['comment_flag'] = $comment_flag;
                    array_push($data, $e);
                }
                $this->assign('contents', $data);
                return $this->fetch();
            } catch (Exception $e) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
        } else {
            return $this->fetch('login');
        }
    }

    // 前端动态加载用户发过的帖子 api
    public function myApi(Request $request) {
        $isLogin = common\isLogin($request);
        try {
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
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'object_id' => $e['id'],
                        'type' => 1
                    ])
                    ->find();
                $like_flag = $like_flag ? 1 : 0;
                $dislike_flag = Db::name('operate')
                    ->where([
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'object_id' => $e['id'],
                        'type' => 2
                    ])
                    ->find();
                $dislike_flag = $dislike_flag ? 1 : 0;
                $comment_flag = Db::name('operate')
                    ->where([
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'object_id' => $e['id'],
                        'type' => 3
                    ])
                    ->find();
                $comment_flag = $comment_flag ? 1 : 0;
                $e['like_flag'] = $like_flag;
                $e['dislike_flag'] = $dislike_flag;
                $e['comment_flag'] = $comment_flag;
                array_push($data, $e);
            }
            return json($data);
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function delete(Request $request) {
        $isLogin = common\isLogin($request);
        $contentId = $request->param('contentId');
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            try {
                $user = Db::name('content')
                    ->where([
                        'id' => $contentId
                    ])
                    ->field('userV')
                    ->find();
                if ($user['userV'] == $isLogin['user']) {
                    Db::name('content')
                        ->where([
                            'id' => $contentId,
                            'userV' => $isLogin['user']
                        ])
                        ->update([
                            // 用户删除帖子时只是把帖子的 is_delete 置为 1，然后删除所有用户对此帖子的操作记录
                            // 用户对此帖子的评论不做改变
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
            } catch (Exception $e) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
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
            try {
                $data = array();
                // 用户的动态
                // 得到用户的动态类型 点赞 点踩 评论 举报
                // 得到用户操作的帖子内容
                // 得到用户对帖子的操作状态
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
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 1
                        ])
                        ->find();
                    $like_flag = $like_flag ? 1 : 0;
                    $dislike_flag = Db::name('operate')
                        ->where([
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 2
                        ])
                        ->find();
                    $dislike_flag = $dislike_flag ? 1 : 0;
                    $comment_flag = Db::name('operate')
                        ->where([
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 3
                        ])
                        ->find();
                    $comment_flag = $comment_flag ? 1 : 0;
                    $e['like_flag'] = $like_flag;
                    $e['dislike_flag'] = $dislike_flag;
                    $e['comment_flag'] = $comment_flag;
                    array_push($data, $e);
                }
                $this->assign('contents', $data);
                return $this->fetch();
            } catch (Exception $e) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
        } else {
            return $this->fetch('login');
        }
    }

    // 前端动态加载用户动态的 api
    public function activeApi(Request $request) {
        $isLogin = common\isLogin($request);
        $data = array();
        try {
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
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'object_id' => $e['id'],
                        'type' => 1
                    ])
                    ->find();
                $like_flag = $like_flag ? 1 : 0;
                $dislike_flag = Db::name('operate')
                    ->where([
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'object_id' => $e['id'],
                        'type' => 2
                    ])
                    ->find();
                $dislike_flag = $dislike_flag ? 1 : 0;
                $comment_flag = Db::name('operate')
                    ->where([
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'object_id' => $e['id'],
                        'type' => 3
                    ])
                    ->find();
                $comment_flag = $comment_flag ? 1 : 0;
                $e['like_flag'] = $like_flag;
                $e['dislike_flag'] = $dislike_flag;
                $e['comment_flag'] = $comment_flag;
                array_push($data, $e);
            }
            return json($data);
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    // 得到给用户的通知
    public function info(Request $request) {
        $isLogin = common\isLogin($request);
        if($isLogin['type'] == 'userV' and $isLogin['status'] == 'success') {
            try {
                $data = array();
                // 得到通知来源用户昵称
                // 得到用户的哪一条帖子被操作
                // 得到用户对自己帖子的操作状态
                $content = Db::name('operate')
                    ->where([
                        'to_user' => $isLogin['user'],
                        // 只有用户的帖子被点赞或者评论时用户才会有通知
                        'type' => ['IN', [1, 3]],
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
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 1
                        ])
                        ->find();
                    $like_flag = $like_flag ? 1 : 0;
                    $dislike_flag = Db::name('operate')
                        ->where([
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 2
                        ])
                        ->find();
                    $dislike_flag = $dislike_flag ? 1 : 0;
                    $comment_flag = Db::name('operate')
                        ->where([
                            $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                            'object_id' => $e['id'],
                            'type' => 3
                        ])
                        ->find();
                    $comment_flag = $comment_flag ? 1 : 0;
                    // 将用户看过的动态标记为已经查看，以后将不会再次加载此动态
                    Db::name('operate')
                        ->where([
                            'object_id' => $e['id'],
                            'type' => $e['type']
                        ])
                        ->update([
                            'flag' => 1
                        ]);
                    $e['like_flag'] = $like_flag;
                    $e['dislike_flag'] = $dislike_flag;
                    $e['comment_flag'] = $comment_flag;
                    array_push($data, $e);
                }
                $this->assign('contents', $data);
                $this->assign('myNick', $user['nickname']);
                return $this->fetch();
            } catch (Exception $e) {
                $errorMessage = '系统错误，请稍后再试';
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
        } else {
            return $this->fetch('login');
        }
    }

    // 前端动态获取用户通知的 api
    public function infoApi(Request $request) {
        $isLogin = common\isLogin($request);
        $contentList = array();
        try {
            $content = Db::name('operate')
                ->where([
                    'to_user' => $isLogin['user'],
                    'type' => ['IN', [1, 3]],
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
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'object_id' => $e['id'],
                        'type' => 1
                    ])
                    ->find();
                $like_flag = $like_flag ? 1 : 0;
                $dislike_flag = Db::name('operate')
                    ->where([
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                        'object_id' => $e['id'],
                        'type' => 2
                    ])
                    ->find();
                $dislike_flag = $dislike_flag ? 1 : 0;
                $comment_flag = Db::name('operate')
                    ->where([
                        $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
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
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function login() {
        return $this->fetch();
    }

    public function doLogin(Request $request) {
        $userMail = $request->param('userMail');
        $userPassword = $request->param('userPassword');
        try {
            $user = Db::name('user')
                ->where([
                    'mail' => $userMail,
                    // 存储用户的 md5 密码
                    'password' => md5($userPassword),
                    // 只有邮箱激活后的用户才能登陆
                    'activate' => 1
                ])
                ->find();
            if ($user) {
                // todo cookie 安全性
                // 登陆用户的本地 cookie 标识就是用户的邮箱加密码的 md5
                // 可以考虑给用户标识设置过期时间
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
                $errorMessage = "用户名或密码错误(请确认您是否通过点击邮箱收到的链接激活了账号）";
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
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
        try {
            $isRegisted = Db::name('user')
                ->where('mail', '=', $userMail)
                ->find();
            if ($isRegisted) {
                $errorMessage = "此邮箱已经被注册";
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            } else {
                $identity = md5($userMail . $userPassword);
                Db::name('user')
                    ->insert([
                        'nickname' => $userName,
                        'mail' => $userMail,
                        'password' => md5($userPassword),
                        'identity' => $identity
                    ]);
                // todo 异步邮件发送
                $data = common\sendMail($userMail, $userName, $identity);
                if ($data['status'] == 'success') {
                    $errorMessage = "注册成功，请登录您的邮箱激活您的账号。如果没有收到邮件，可以尝试在邮箱垃圾桶中寻找。";
                } else {
                    $errorMessage = "注册失败，给您发送邮件时出了点问题，请稍后再试";
                }
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function logout() {
        $identity = Cookie::get('hole_userV');
        // 用户登出，删除用户本地 cookie 标识
        Cookie::delete('userV', 'hole_');
        try {
            Db::name('user')
                ->where('identity', '=', $identity)
                ->update([
                    'identity' => ' '
                ]);
            $this->redirect('/hole');
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function activate(Request $request) {
        $identity = $request->param('identity');
        try {
            $result = Db::name('user')
                ->where([
                    'identity' => $identity,
                    'activate' => 0
                ])
                ->update([
                    'activate' => 1
                ]);
            if ($result) {
                $errorMessage = "您的账号激活成功";
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            } else {
                $errorMessage = "您的账号激活失败（您可能已经激活过账号，可以直接登录)";
                $this->assign('errorMessage', $errorMessage);
                return $this->fetch('message/error');
            }
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function find() {
        return $this->fetch();
    }

    public function doFind(Request $request) {
        $mail = $request->param('mail');
        $captcha = $request->param('captcha');
        $password = $request->param('userPassword');
        try {
            // 判断用户填写的邮箱是否注册过
            $result = Db::name('user')
                ->where([
                    'mail' => $mail
                ])
                ->field('captcha')
                ->find();
            if ($result['captcha'] = $captcha) {
                // 用户验证码对比成功后清除原有验证码
                Db::name('user')
                    ->where([
                        'mail' => $mail
                    ])
                    ->update([
                        'identity' => '',
                        'password' => md5($password),
                        'captcha' => ''
                    ]);
                $errorMessage = "密码重置成功";
            } else {
                $errorMessage = "验证码或邮箱错误";
            }
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function doCaptcha(Request $request) {
        try {
            $userMail = $request->param('userMail');
            // 生成随机 4 位验证码到邮箱
            $charts = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz0123456789";
            $max = strlen($charts);
            $captcha = "";
            for ($i = 0; $i < 4; $i++) {
                $captcha .= $charts[mt_rand(0, $max - 1)];
            }
            $result = Db::name('user')
                ->where([
                    'mail' => $userMail
                ])
                ->update([
                    'captcha' => $captcha
                ]);
            $data = [
                'status' => 'fail'
            ];
            if ($result) {
                $data = common\sendCaptcha($userMail, $captcha);
                if ($data['status'] == 'success') {
                    $errorMessage = "验证码发送成功，请登录您的邮箱查看验证码。如果没有收到邮件，可以尝试在邮箱垃圾桶中寻找。";
                } else {
                    $errorMessage = "验证码发送失败，给您发送邮件时出了点问题，请稍后再试";
                }
            } else {
                $errorMessage = "该邮箱未注册";
            }
            $data2 = [
                'status' => $data['status'] == 'success' ? 'success' : 'fail',
                'message' => $errorMessage
            ];
            return json($data2);
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    public function upload() {
        return $this->fetch();
    }

    public function doUpload(Request $request) {
        $img = isset($_POST['img'])? $_POST['img'] : '';

        // 获取图片
        list($type, $data) = explode(',', $img);

        // 判断类型
        if(strstr($type,'image/jpeg')!=''){
            $ext = '.jpg';
        }elseif(strstr($type,'image/gif')!=''){
            $ext = '.gif';
        }elseif(strstr($type,'image/png')!=''){
            $ext = '.png';
        }

        // 生成的文件名
        $photo = time().$ext;

        // 生成文件
        file_put_contents($photo, base64_decode($data), true);

        // 返回
        header('content-type:application/json;charset=utf-8');
        $ret = array('img'=>$photo);
        return json_encode($ret);
    }
}