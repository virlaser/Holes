<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/27
 * Time: 下午11:01
 */

namespace app\hole\controller;


use think\Controller;

class User extends Controller {

    public function index() {
        return $this->fetch();
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

    public function register() {
        return $this->fetch();
    }

}