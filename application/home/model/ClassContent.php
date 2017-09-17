<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/17
 * Time: 15:47
 */
namespace app\home\model;
use think\Model;
use think\Collection;

class ClassContent extends Model
{
    public $insert = [
        'create_time' => NOW_TIME,
    ];

}