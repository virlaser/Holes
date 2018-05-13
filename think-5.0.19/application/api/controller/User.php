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
use function app\api\common\isLogin;

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
                'status' => 'fail',
                'message' => '获取用户openid出错'
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
                    'status' => 'fail',
                    'message' => '系统错误'
                ];
                return json($data);
            } else {
                // 得到用户 id
                $id = Db::name('user')
                    ->where('openid', '=', $openid)
                    ->field('id, create_time')
                    ->find();
                $data = [
                    'name' => $nickName,
                    'openid' => $openid,
                    'imgurl' => $avatar,
                    'sex' => $gender,
                    'userid' => $id['id'],
                    'createtime' => strtotime($id['create_time'])
                ];
                return json($data);
            }
        } else {
            $id = Db::name('user')
                ->where('openid', '=', $openid)
                ->field('id, create_time')
                ->find();
            $data = [
                'name' => $nickName,
                'openid' => $openid,
                'imgurl' => $avatar,
                'sex' => $gender,
                'userid' => $id['id'],
                'createtime' => strtotime($id['create_time'])
            ];
            return json($data);
        }

    }

    // 用户信息
    public function info(Request $request) {
        $data = array();
        // 检查用户登录
        if(isLogin($request)) {
            $userId = isLogin($request);
        } else {
            $data = [
                'status' => 'fail',
                'message' => '用户登录信息错误'
            ];
            return json($data);
        }
        // 查找用户的帖子
        $contentNum = Db::name('content')
            ->where([
                'user_id' => $userId,
                'is_delete' => 0
            ])
            ->count();
        // 查找用户通知
        $notificationNum = Db::name('operate')
            ->where([
                'to_user' => $userId,
                'flag' => 0
            ])
            ->count();
        // 查找用户动态
        $activityNum = Db::name('operate')
            ->where('from_user', '=', $userId)
            ->count();
        $data = [
            'contentNum' => $contentNum,
            'notificationNum' => $notificationNum,
            'activityNum' => $activityNum
        ];
        return json($data);
    }

    // 用户发的帖子
    public function posts(Request $request) {
        $data = array();
        // 检查用户登录
        if(isLogin($request)) {
            $userId = isLogin($request);
        } else {
            $data = [
                'status' => 'fail',
                'message' => '用户登录信息错误'
            ];
            return json($data);
        }
        // 查出帖子对应的点赞等信息以及分页
        // todo 页面数据倒序排列
        $lists = Db::table('hole_content')
            ->where([
                'is_delete' => 0,
                'user_id' => $userId
            ])
            ->join('hole_user', 'hole_content.user_id=hole_user.id')
            ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
            ->paginate(10, true);
        foreach($lists as $e) {
            // 判断此用户是否点赞过帖子
            $like_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 1
                ])
                ->find();
            $like_flag = $like_flag?1:0;
            // 判断此用户是否点踩过帖子
            $dislike_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 2
                ])
                ->find();
            $dislike_flag = $dislike_flag?1:0;
            // 判断此用户是否评论过帖子
            $comment_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 3
                ])
                ->find();
            $comment_flag = $comment_flag?1:0;
            // 判断此用户是否举报过帖子
            $report_flag = Db::name('operate')
                ->where([
                    'from_user' => $userId,
                    'object_id' => $e['id'],
                    'type' => 4
                ])
                ->find();
            $report_flag = $report_flag?1:0;
            // 判断此帖子是不是此用户写的
            $my_flag = ($e['user_id']==$userId)?1:0;
            // 数据查询返回的数据集不能动态添加数据，因此重新构造数据集
            $e['like_flag'] = $like_flag;
            $e['dislike_flag'] = $dislike_flag;
            $e['comment_flag'] = $comment_flag;
            $e['report_flag'] = $report_flag;
            $e['my_flag'] = $my_flag;
            array_push($data, $e);
        }
        return json($data);
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