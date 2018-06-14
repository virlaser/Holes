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
        $isLogin = common\isLogin($request);
        $contents = Db::name('content')
            ->where([
                'verified' => 5,
                'is_delete' => 0,
                'flag' => 0
            ])
            ->join('hole_user', 'hole_content.userV=hole_user.id', 'LEFT')
            ->field('hole_content.*, hole_user.nickname, hole_user.avatar')
            ->order('hole_content.create_time desc')
            ->paginate(10, true);
        $data = array();
        foreach ($contents as $e) {
            $like_flag = Db::name('operate')
                ->where([
                    'from_user' => $isLogin['user'],
                    'object_id' => $e['id'],
                    'type' => 1
                ])
                ->find();
            $like_flag = $like_flag ? 1 : 0;
            // 判断此用户是否点踩过帖子
            $dislike_flag = Db::name('operate')
                ->where([
                    'from_user' => $isLogin['user'],
                    'object_id' => $e['id'],
                    'type' => 2
                ])
                ->find();
            $dislike_flag = $dislike_flag ? 1 : 0;
            // 判断此用户是否评论过帖子
            $comment_flag = Db::name('operate')
                ->where([
                    'from_user' => $isLogin['user'],
                    'object_id' => $e['id'],
                    'type' => 3
                ])
                ->find();
            $comment_flag = $comment_flag ? 1 : 0;
            // 数据查询返回的数据集不能动态添加数据，因此重新构造数据集
            $e['like_flag'] = $like_flag;
            $e['dislike_flag'] = $dislike_flag;
            $e['comment_flag'] = $comment_flag;
            array_push($data, $e);
        }
        $this->assign('contents', $data);
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
                'verified' => $isLogin['type']=='userV'?5:0,
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