<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/6/1
 * Time: 下午5:58
 */

namespace app\hole\common;

use think\Cookie;
use think\Db;
use think\Request;

function isLogin(Request $request) {
    $userT = $request->cookie('hole_userT');
    $userV = $request->cookie('hole_userV');
    if($userV) {
        $user = Db::name('user')
            ->where('identity', '=', $userV)
            ->field('id')
            ->find();
        if($user) {
           $data = [
               'message' => '用户已登录',
               'type' => 'userV',
               'user' => $user['id'],
               'status' => 'success'
           ];
           return $data;
        } else {
            $data = [
                'message' => '登录认证信息错误',
                'type' => 'userV',
                'user' => null,
                'status' => 'fail'
            ];
            return $data;
        }
    } elseif ($userT) {
        $data = [
            'message' => '用户未登录',
            'type' => 'userT',
            'user' => $userT,
            'status' => 'success'
        ];
        return $data;
    } else {
        $data = [
            'message' => '无用户信息',
            'type' => 'userT',
            'user' => null,
            'status' => 'fail'
        ];
        return $data;
    }
}

// hash time
function setUserT(Request $request) {
    $userT = $request->cookie('hole_userT');
    if(!$userT) {
        $microTime = explode(" ", microtime());
        $time = $microTime[1] . ($microTime[0] * 1000);
        $time = explode(".", $time)[0];
        $userT = md5($time);
        Cookie::set('userT', $userT, ['prefix' => 'hole_', 'expire' => 60 * 60 * 24 * 365]);
    }
}

// hash username and password
function setUserV($identity) {
    Cookie::set('userV', $identity, ['prefix' => 'hole_', 'expire' => 60*60*24*30*3]);
}

function sendMail($emailAddress) {
    $data = [
        'message' => '邮件发送成功',
        'status' => 'success'
    ];
    return $data;
}
