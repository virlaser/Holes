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
            return $this->fetch('index');
        } else {
            $this->redirect('/hole');
        }
    }

    public function sortLike(Request $request) {
        $user = Session::get('user');
    }

    public function sortDislike(Request $request) {
        return ;
    }

    public function sortComment(Request $request) {
        return ;
    }

    public function sortReport(Request $request) {
        return ;
    }

    public function sortFlag(Request $request) {
        return ;
    }

    public function sortCheck(Request $request) {
        return ;
    }

}