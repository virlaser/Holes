<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/7
 * Time: 下午8:59
 */

namespace app\api\common;


use think\Db;
use think\Exception;
use think\Request;

// 判断用户是否注册，如果注册则返回userId
// todo 判断用户登录时间是否过期
function isLogin(Request $request) {
    try {
        $openId = $request->param('user_openid');
        $find = Db::name('user')
            ->where('openid', '=', $openId)
            ->find();
        if ($find)
            return $find['id'];
        else
            return false;
    } catch (Exception $exception) {
        $data = [
            'status' => 'fail',
            'message' => $exception->getMessage()
        ];
        return json($data);
    }
}

// 发送请求给微信服务器获取 openId
function curl_get_https($url) {
    // 启动一个CURL会话
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // 返回api的json对象
    $tmpInfo = curl_exec($curl);
    // 关闭URL请求
    curl_close($curl);
    //返回 array 对象
    return json_decode($tmpInfo, true);
}