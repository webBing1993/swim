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

    const STATUS_NORMAL = 1;
    const STATUS_LATE = 2;
    const STATU_ARRAY = [
        self::STATUS_NORMAL  => '正常',
        self::STATUS_LATE  => '迟到',
    ];

    protected $insert = [
        'create_time' => NOW_TIME,
        'status' => 1,
    ];

    public static function checkUserSign($userId) {
        $where = array(
            'userid' => $userId,
            'date' => date('Y-m-d')
        );
        $res = self::where($where)->find();
        return $res;
    }

}