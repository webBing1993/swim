<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/17
 * Time: 16:22
 */
namespace app\admin\model;
use think\Model;
use think\Collection;

class ClassTrain extends Base
{
    public $insert = [
        'create_time' => NOW_TIME,
    ];

}