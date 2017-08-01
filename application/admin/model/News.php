<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/21
 * Time: 15:16
 */

namespace app\admin\model;
use think\Model;

class News extends Base {
    public $insert = [
        'views' => 0,
        'collect' => 0,
        'comments' => 0,
        'create_time' => NOW_TIME,
        'status' => 1,
    ];
    
}