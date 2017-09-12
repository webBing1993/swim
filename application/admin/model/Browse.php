<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/12
 * Time: 14:32
 */
namespace app\admin\model;
use think\Model;

class Browse extends Model {
    protected $insert = [
        'status' => 0,
        'create_time' => NOW_TIME,
        'score' => 1,
    ];
}