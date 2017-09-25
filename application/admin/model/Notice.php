<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/9
 * Time: 15:04
 */

namespace app\admin\model;


use think\Model;

class Notice extends Base {
    const TYPE_NOTICE = 1;
    const TYPE_ACTIVITY = 2;
    const TYPE_ARRAY = [
        self::TYPE_NOTICE  => '相关通知',
        self::TYPE_ACTIVITY  => '活动情况',
    ];
    public $insert = [
        'likes' => 0,
        'views' => 0,
        'collect' => 0,
        'comments' => 0,
        'create_time' => NOW_TIME,
    ];
    
}