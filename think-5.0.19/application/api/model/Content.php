<?php
/**
 * Created by PhpStorm.
 * User: vlaser
 * Date: 2018/5/11
 * Time: 上午7:54
 */

namespace app\api\model;


use think\Model;

class Content extends Model {

    public function getCreateTimeAttr($time) {
        return $time;
    }

    public function getUpdateTimeAttr($time) {
        return $time;
    }

    public function user() {
        return $this->belongsTo('User')
            ->bind([
                'nickname',
                'avatar',
            ]);
    }

}