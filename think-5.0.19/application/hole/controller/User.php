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
                    'type' => ['IN', [1, 2, 3, 4, 5, 6]],
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

    public function my() {
        return $this->fetch();
    }

    public function active() {
        return $this->fetch();
    }

    public function info() {
        return $this->fetch();
    }

    public function login() {
        return $this->fetch();
    }

    public function doLogin(Request $request) {
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
            return $this->fetch('index');
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

}