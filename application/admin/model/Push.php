<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/18
 * Time: 16:17
 */
namespace app\admin\model;
use think\Model;
class Push extends Base{
    public $insert = [
        'create_time' => NOW_TIME,
    ];
    //获取后台用户名称
    public function user(){
        return $this->hasOne('Member','id','create_user');
    }
}