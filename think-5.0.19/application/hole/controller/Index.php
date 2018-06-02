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
use think\Request;

class Index extends Controller {

    public function index(Request $request) {
        common\setUserT($request);
        return $this->fetch();
    }

    public function create(Request $request) {
        return $this->fetch('create');
    }
    public function doCreate(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $content = $request->param('content');
        // if choose, $hide = 'on'
        $hide = $request->param('hide');
        $hide = $hide == 'on' ? 1 : 0;
        Db::name('content')
            ->insert([
                'content' => $content,
                $isLogin['type']=='userV'?'userV':'userT' => $isLogin['user']?$isLogin['user']:0,
                'hide' => $hide,
            ]);
        return $this->fetch('index');
    }

    public function comment() {
        return $this->fetch();
    }

    public function detail() {
        return $this->fetch();
    }

}