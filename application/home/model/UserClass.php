<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/23
 * Time: 19:26
 */
namespace app\home\model;


use think\Model;

class UserClass extends Model {
    protected $insert = [
        'create_time' => NOW_TIME,
    ];

    public static function getName($id) {
        $userClassModel = self::where(['id'=>$id])->find();
        return $userClassModel['start_time'].'-'.$userClassModel['end_time'];
    }

}