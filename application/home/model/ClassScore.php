<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/17
 * Time: 15:31
 */
namespace app\home\model;
use think\Model;
use think\Collection;

class ClassScore extends Model
{
    public $insert = [
        'good' => 0,
        'create_time' => NOW_TIME,
    ];

}