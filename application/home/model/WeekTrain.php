<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19
 * Time: 15:58
 */
namespace app\home\model;
use think\Model;
use think\Collection;

class WeekTrain extends Model
{
    public $insert = [
        'create_time' => NOW_TIME,
    ];

}