<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/22
 * Time: 16:37
 */

namespace app\home\model;


use think\Model;

class WechatUserSign extends Model {
    protected $insert = [
        'create_time' => NOW_TIME,
        'score' => 1,
        'status' => 0,
    ];

    public function getLike($type,$aid,$uid) {
        $map = array(
            'type' => $type,
            'aid' => $aid,
            'uid' => $uid,
        );
        $like = $this->where($map)->find();
        ($like) ? $res = 1 : $res = 0;
        return $res;
    }
}