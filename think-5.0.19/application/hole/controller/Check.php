<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/28
 * Time: 下午9:01
 */

namespace app\hole\controller;


use think\Controller;
use think\Db;
use think\Exception;
use think\Request;

use app\hole\common;

class Check extends Controller {

    // 审帖页面
    public function index(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $userT = $isLogin['type']=='userT'?$isLogin['user']:0;
        try {
            // 得到用户审查过得帖子 id 列表
            $checked = Db::name('operate')
                ->where([
                    'type' => 7,
                    $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user']
                ])
                ->field('object_id')
                ->select();
            $checkArray = array();
            foreach ($checked as $item) {
                array_push($checkArray, $item['object_id']);
            }
            // 得到用户未审查过的一条帖子
            $content = Db::name('content')
                ->where([
                    'userV' => 0,
                    'userT' => ['NEQ', $userT],
                    'verified' => ['<', 3],
                    'id' => ['NOT IN', $checkArray]
                ])
                ->find();
            $this->assign('content', $content);
            return $this->fetch();
        } catch (Exception $e) {
            $errorMessage = '系统错误，请稍后再试';
            $this->assign('errorMessage', $errorMessage);
            return $this->fetch('message/error');
        }
    }

    // 用户审帖状态接口
    public function doCheck(Request $request) {
        common\setUserT($request);
        $isLogin = common\isLogin($request);
        $contentId = $request->param('contentId');
        $checkType = $request->param('checkType');
        try {
            Db::name('operate')
                ->insert([
                    'type' => 7,
                    $isLogin['type'] == 'userV' ? 'from_user' : 'identity' => $isLogin['user'],
                    'to_user' => 0,
                    // 审查帖子不通知用户
                    'flag' => 0,
                    'object_id' => $contentId
                ]);
            if ($checkType == 'yes') {
                Db::name('content')
                    ->where([
                        'id' => $contentId,
                    ])
                    ->setInc('verified');
            }
            if ($checkType == 'no') {
                Db::name('content')
                    ->where([
                        'id' => $contentId,
                    ])
                    ->setDec('verified');
            }
            $data = [
                'status' => 'success',
                'message' => '审帖成功'
            ];
            return json($data);
        } catch (Exception $e) {
            $data = [
                'status' => 'fail',
                'message' => '系统错误，请稍后再试'
            ];
            return json($data);
        }
    }

}