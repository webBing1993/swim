<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/14
 * Time: 14:55
 */

namespace app\home\model;


use think\Model;

class Collect extends Model {
    protected $insert = [
        'status' => 0,
        'create_time' => NOW_TIME,
    ];

    //关联查询新闻
    public function news(){
        return $this->hasOne('News','id','news_id');
    }

    //关联查询通知
    public function notice(){
        return $this->hasOne('Notice','id','notice_id');
    }

    //查询课程
    public function learn(){
        return $this->hasOne('Learn','id','learn_id');
    }

}