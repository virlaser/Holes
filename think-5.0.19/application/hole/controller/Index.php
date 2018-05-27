<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/21
 * Time: 下午3:25
 */

namespace app\hole\controller;


use think\Controller;

class Index extends Controller {

    public function index() {
        return $this->fetch();
    }

    public function create() {
        return $this->fetch();
    }

    public function comment() {
        return $this->fetch();
    }

    public function detail() {
        return $this->fetch();
    }

}