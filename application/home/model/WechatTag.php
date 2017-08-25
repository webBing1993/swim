<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/25
 * Time: 9:23
 */
namespace app\home\model;


use think\Model;

class WechatTag extends Model {

    public function tag(){
        return $this->hasOne('WechatUserTag','tagid','tagid');
    }
}