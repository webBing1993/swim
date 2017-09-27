<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/23
 * Time: 19:26
 */
namespace app\admin\model;


use think\Model;

class UserClass extends Model {
    protected $insert = [
        'create_time' => NOW_TIME,
    ];


}