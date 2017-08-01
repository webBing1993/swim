<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/18
 * Time: 17:09
 */
namespace app\admin\model;
use think\Model;
class PushReview extends Base{
    public $insert = [
        'review_time' => NOW_TIME,
    ];
}