<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/7
 * Time: 下午8:59
 */

namespace app\api\common;


    function isLogin($userId, $identify) {
        return;
    }

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