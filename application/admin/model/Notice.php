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
    public $insert = [
        'likes' => 0,
        'views' => 0,
        'collect' => 0,
        'comments' => 0,
        'create_time' => NOW_TIME,
    ];
    
}