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

    const TYPE_NEWS = 1;
    const TYPE_SWIM = 2;
    const TYPE_VIDEO = 3;
    const TYPE_ARRAY = [
        self::TYPE_NEWS  => '新闻动态',
        self::TYPE_SWIM  => '游泳百科',
        self::TYPE_VIDEO  => '在线视频',
    ];
    public $insert = [
        'views' => 0,
        'collect' => 0,
        'comments' => 0,
        'create_time' => NOW_TIME,
        'status' => 1,
    ];
    
}