<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19
 * Time: 15:57
 */
namespace app\home\model;
use think\Model;
use think\Collection;

class WeekContent extends Model
{
    public $insert = [
        'create_time' => NOW_TIME,
    ];

}