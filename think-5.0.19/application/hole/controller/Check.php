<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/28
 * Time: 下午9:01
 */

namespace app\hole\controller;


use think\Controller;

class Check extends Controller {

    public function index() {
        return $this->fetch();
    }

}