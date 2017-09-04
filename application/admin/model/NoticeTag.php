<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/1
 * Time: 15:55
 */
namespace app\admin\model;
use think\Model;

class NoticeTag extends Base {
    public $insert = [
        'create_time' => NOW_TIME,
    ];

}