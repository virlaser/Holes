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

function sendMail($emailAddress, $userName, $identity) {
    $mail = new PHPMailer(true);
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
