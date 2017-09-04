<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/1
 * Time: 15:58
 */
namespace app\home\model;
use think\Model;

class NoticeTag extends Model {
    public $insert = [
        'create_time' => NOW_TIME,
    ];

}