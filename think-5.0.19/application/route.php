<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    // 树洞主页
    'index' => 'hole/index/index',
    // 树洞主页动态加载帖子 api
    'contentApi' => 'hole/index/contentApi',
    // 用户发帖页面
    'create' => 'hole/index/create',
    // 用户发帖逻辑
    'doCreate' => 'hole/index/doCreate',
    // 加载特定的标签内容
    'tag' => 'hole/index/tag',
    // 前端动态请求标签内容接口
    'tagApi' => 'hole/index/tagApi',
    // 用户评论页面
    'comment' => 'hole/index/comment',
    // 用户评论逻辑
    'doComment' => 'hole/index/doComment',
    // 帖子详情页面动态加载评论 api
    'commentApi' => 'hole/index/commentApi',
    // 帖子详情
    'detail' => 'hole/index/detail',
    // 用户操作逻辑，点赞 点踩 评论 给评论点赞 给评论点踩 举报
    'operate' => 'hole/index/operate',
    // 用户信息页面
    'user' => 'hole/user/index',
    // 用户"我的帖子"页面
    'my' => 'hole/user/my',
    // 用户"我的帖子"页面动态加载帖子接口
    'myApi' => 'hole/user/myApi',
    // 用户删除自己帖子逻辑
    'delete' => 'hole/user/delete',
    // 用户动态页面
    'active' => 'hole/user/active',
    // 用户"我的动态"页面动态加载帖子接口
    'activeApi' => 'hole/user/activeApi',
    // 用户通知页面
    'info' => 'hole/user/info',
    // 用户通知页面动态加载通知接口
    'infoApi' => 'hole/user/infoApi',
    // 用户审帖页面
    'check' => 'hole/check/index',
    // 用户审帖后端逻辑
    'doCheck' => 'hole/check/doCheck',
    // 用户登录页面
    'login' => 'hole/user/login',
    // 用户登录后端逻辑
    'doLogin' => 'hole/user/doLogin',
    // 用户注册页面
    'register' => 'hole/user/register',
    // 用户账号激活
    'activate' => 'hole/user/activate',
    // 用户注册后端逻辑
    'doRegister' => 'hole/user/doRegister',
    // 用户退出登录
    'logout' => 'hole/user/logout',
    // 用户找回密码页面
    'find' => 'hole/user/find',
    // 用户找回密码后端逻辑
    'doFind' => 'hole/user/doFind',
    // 给用户发送验证码，用来用户找回密码
    'captcha' => 'hole/user/doCaptcha',
    // 修改用户信息页面
    'upload' => 'hole/user/upload',
    // 用户上传信息处理逻辑
    'doUpload' => 'hole/user/doUpload',
];
