<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/7
 * Time: 下午8:39
 */

namespace app\api\controller;


use think\Controller;
use app\api\common;
use think\Db;
use think\Request;

class User extends Controller {

    // 使用微信登录
    public function login(Request $request) {

        $nickName = $request->param('nick');
        $avatar = $request->param('avaurl');
        $code = $request->param('code');
        $gender = $request->param('sex');

        $appId = 'wxed70f591f216227e';
        $appSecret = 'bc6baf64baa48060581fbd0cc9f007f4';
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appId.'&secret='.$appSecret.'&js_code='.$code.'&grant_type=authorization_code';
        $json = common\curl_get_https($url);

        $openid = $json['openid'];
        $session_key = $json['session_key'];

        if(!$openid){
            $data = [
                'status' => 0,
                'message' => '获取用户认证出错'
            ];
            return json($data);
        }

        $find = Db::name('user')
            ->where('openid', '=', $openid)
            ->find();

        // 用户未注册，将用户添加到数据库
        if(!$find) {
            $result = Db::name('user')
                ->insert([
                    'nickname' => $nickName,
                    'avatar' => $avatar,
                    'gender' => $gender,
                    'openid' => $openid,
                    'session_key' => $session_key
                ]);
            // 插入失败，返回错误状态码
            if(!$result) {
                $data = [
                    'status' => 0,
                    'message' => '系统错误'
                ];
                return json($data);
            } else {
                $data = [
                    'name' => $nickName,
                    'openid' => $openid,
                    'imgurl' => $avatar,
                    'sex' => $gender,
                    'status' => 1,
                    'message' => '成功'
                ];
                return json($data);
            }
        } else {
            $data = [
                'name' => $nickName,
                'openid' => $openid,
                'imgurl' => $avatar,
                'sex' => $gender,
                'status' => 1,
                'message' => '成功'
            ];
            return json($data);
        }

    }

    // 用户信息
    public function info() {
        return;
    }

    // 用户发的帖子
    public function posts() {
        return;
    }

    // 用户点赞的帖子
    public function likes() {
        return;
    }

    // 用户消息
    public function notifications() {
        return;
    }

}