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
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'util/PHPMailer.php';
require 'util/Exception.php';
require 'util/SMTP.php';

// 判断用户是否登录
// 登录用户，通过用户本地标识确定用户 id
// 未登录用户，通过用户本地标识确定用户 identity
function isLogin(Request $request) {
    $userT = $request->cookie('hole_userT');
    $userV = $request->cookie('hole_userV');
    try {
        if ($userV) {
            $user = Db::name('user')
                ->where('identity', '=', $userV)
                ->field('id')
                ->find();
            if ($user) {
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
                    // 如果用户本地登录标识被篡改导致查询不到用户信息，把用户当做未登录用户
                    'type' => 'userT',
                    'user' => '',
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
                'user' => '',
                'status' => 'fail'
            ];
            return $data;
        }
    } catch (\think\Exception $e) {
        $data = [
            'message' => '系统错误',
            'type' => 'userT',
            'user' => '',
            'status' => 'fail'
        ];
        return $data;
    }
}

// 未登录用户的标识是用户此次访问时间的 MD5
// 将此标识以 userT 的 cookie 存储在用户浏览器
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

// 登录用户的标识是用户的邮箱和密码的 MD5
// 将此标识以 userV 的 cookie 存储在用户浏览器
function setUserV($identity) {
    Cookie::set('userV', $identity, ['prefix' => 'hole_', 'expire' => 60*60*24*30*3]);
}

// 发送注册邮箱激活邮件
function sendMail($emailAddress, $userName, $identity) {
    $mail = new PHPMailer(true);
    // 通过对比用户的邮箱加上密码的 MD5 来确认激活的账号
    $href = config('domain') . '/activate?identity=' . $identity;
    try {
        $mail->isSMTP();
        $mail->Host = config('mail.host');
        $mail->SMTPAuth = true;
        $mail->Username = config('mail.userName');
        $mail->Password = config('mail.password');
        $mail->SMTPSecure = 'ssl';
        $mail->Port = config('mail.port');
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(config('mail.from'), '武理树洞');
        $mail->addAddress($emailAddress, $userName);

        $mail->isHTML(true);
        $mail->Subject = '武理树洞账号激活';
        $mail->Body = "<!DOCTYPE html>\n" .
            "<html lang=\"en\">\n" .
            "<head>\n" .
            "    <meta charset=\"UTF-8\">\n" .
            "    <title>树洞账号激活</title>\n" .
            "</head>\n" .
            "<style type=\"text/css\">\n" .
            "    .container {\n" .
            "        width : 80%;\n" .
            "        background-color : #faf6ef;\n" .
            "        border: 1px solid #705f5d;\n" .
            "    }\n" .
            "    p {\n" .
            "        text-align : center;\n" .
            "        color : #705f5d;\n" .
            "        margin: 20px;\n" .
            "    }\n" .
            "    a {\n" .
            "        text-decoration: none;\n" .
            "    }\n" .
            "</style>\n" .
            "<body>\n" .
            "    <div class=\"container\">\n" .
            "        <p>请点击以下链接激活您的树洞账号：</p>\n" .
            "        <a href=\"" . $href . "\">\n" .
            "            <p>https://ψ(｀∇´)ψ</p>\n" .
            "        </a>\n" .
            "    </div>\n" .
            "</body>\n" .
            "</html>";
        $mail->AltBody = "访问以下网址激活您的树洞账号：" . config('domain') . "/activate?identity=" . $identity;
        $mail->send();

        $data = [
            'status' => 'success',
            'message' => '邮件发送成功'
        ];
        return $data;
    } catch (Exception $e) {
        $data = [
            'status' => 'fail',
            'message' => '邮件发送失败'
        ];
        return $data;
    }
}

// 发送找回密码验证码
function sendCaptcha($emailAddress, $captcha) {
    $href = config('domain') . '/find';
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = config('mail.host');
        $mail->SMTPAuth = true;
        $mail->Username = config('mail.userName');
        $mail->Password = config('mail.password');
        $mail->SMTPSecure = 'ssl';
        $mail->Port = config('mail.port');
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(config('mail.from'), '武理树洞');
        $mail->addAddress($emailAddress);

        $mail->isHTML(true);
        $mail->Subject = '武理树洞密码找回';
        $mail->Body = "<!DOCTYPE html>\n" .
            "<html lang=\"en\">\n" .
            "<head>\n" .
            "    <meta charset=\"UTF-8\">\n" .
            "    <title>树洞密码找回</title>\n" .
            "</head>\n" .
            "<style type=\"text/css\">\n" .
            "    .container {\n" .
            "        width : 80%;\n" .
            "        background-color : #faf6ef;\n" .
            "        border: 1px solid #705f5d;\n" .
            "    }\n" .
            "    p {\n" .
            "        text-align : center;\n" .
            "        color : #705f5d;\n" .
            "        margin: 20px;\n" .
            "    }\n" .
            "    a {\n" .
            "        text-decoration: none;\n" .
            "    }\n" .
            "</style>\n" .
            "<body>\n" .
            "    <div class=\"container\">\n" .
            "        <p>您的验证码是：" . $captcha . " 。点击以下链接继续找回您的密码（在输入您上次填写的邮箱后直接输入验证码即可，不必重复点击验证码发送按钮）：</p>\n" .
            "        <a href=\"" . $href . "\">\n" .
            "            <p>https://ψ(｀∇´)ψ</p>\n" .
            "        </a>\n" .
            "    </div>\n" .
            "</body>\n" .
            "</html>";
        $mail->AltBody = "您的验证码是：" . $captcha . "；访问以下网址继续找回您的密码（不必重复点击验证码发送按钮）：" . $href ;
        $mail->send();

        $data = [
            'status' => 'success',
            'message' => '邮件发送成功'
        ];
        return $data;
    } catch (Exception $e) {
        $data = [
            'status' => 'fail',
            'message' => '邮件发送失败'
        ];
        return $data;
    }
}

// 得到当前用户对每一条帖子的 点赞 点踩 评论 状态
// 不进行异常捕捉，异常将会在被调用处被捕捉
function getContentFlag($isLogin, $contents) {
    $data = array();
    foreach ($contents as $e) {
        $flags = Db::name('operate')
            ->where([
                $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                'object_id' => $e['id'],
                // 查看用户是否 点赞，点踩，评论过帖子
                'type' => ['IN', [1, 2, 3]]
            ])
            ->field('type')
            ->select();
        $like_flag = 0;
        $dislike_flag = 0;
        $comment_flag = 0;
        if($flags)
            foreach ($flags as $flag) {
                if ($flag['type'] == 1)
                    $like_flag = 1;
                if ($flag['type'] == 2)
                    $dislike_flag = 1;
                if ($flag['type'] == 3)
                    $comment_flag = 1;
            }
        // 数据查询返回的数据集不能动态添加数据，因此重新构造数据集
        $e['like_flag'] = $like_flag;
        $e['dislike_flag'] = $dislike_flag;
        $e['comment_flag'] = $comment_flag;
        array_push($data, $e);
    }
    return $data;
}

// 得到用户对每一条评论的 点赞 点踩 状态
function getCommentFlag($isLogin, $comments) {
    $data = array();
    foreach ($comments as $e) {
        $flags = Db::name('operate')
            ->where([
                $isLogin['type']=='userV'?'from_user':'identity' => $isLogin['user'],
                'object_id' => $e['id'],
                // 查看用户是 点踩 点赞 过评论
                'type' => ['IN', [5, 6]]
            ])
            ->field('type')
            ->select();
        $like_flag = 0;
        $dislike_flag = 0;
        if($flags)
            foreach ($flags as $flag) {
                if ($flag['type'] == 5)
                    $like_flag = 1;
                if ($flag['type'] == 6)
                    $dislike_flag = 1;
            }
        $e['like_flag'] = $like_flag;
        $e['dislike_flag'] = $dislike_flag;
        array_push($data, $e);
    }
    return $data;
}