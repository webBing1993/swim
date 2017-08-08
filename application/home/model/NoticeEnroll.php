<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2017/8/8
 * Time: 11:29
 */

namespace app\home\model;


use think\Model;

class NoticeEnroll extends Model {
    protected $insert = [
        'create_time' => NOW_TIME
    ];
}