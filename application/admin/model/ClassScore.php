<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/17
 * Time: 15:31
 */
namespace app\admin\model;
use think\Model;
use think\Collection;

class ClassScore extends Base
{
    public $insert = [
        'create_time' => NOW_TIME,
    ];

}