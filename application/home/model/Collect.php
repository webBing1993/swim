<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/14
 * Time: 14:55
 */

namespace app\home\model;


use think\Model;

class Collect extends Model {
    protected $insert = [
        'status' => 0,
        'create_time' => NOW_TIME,
        'score' => 1,
    ];

    public function getCollect($type,$aid,$uid) {
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