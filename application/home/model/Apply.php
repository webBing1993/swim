<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/20
 * Time: 11:30
 */
namespace app\home\model;
use think\Model;

class Apply extends Model{
    protected $insert = [
        'status' => 0,
        'create_time' => NOW_TIME,
    ];
    //关联查询通知
    public function notice(){
        return $this->hasOne('Notice','id','notice_id');
    }
}