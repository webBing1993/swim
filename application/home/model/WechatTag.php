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

    const TAG_LEADER = 1;
    const TAG_HEAD_COACH = 2;
    const TAG_ASSISTANT = 3;
    const TAG_STUDENT = 4;
    const TAG_VISITOR = 5;
    const TAG_ARRAY = [
        self::TAG_LEADER  => '领导',
        self::TAG_HEAD_COACH  => '主教练',
        self::TAG_ASSISTANT  => '助教',
        self::TAG_STUDENT  => '学员',
        self::TAG_VISITOR  => '游客',
    ];

    public function tag(){
        return $this->hasOne('WechatUserTag','tagid','tagid');
    }
}