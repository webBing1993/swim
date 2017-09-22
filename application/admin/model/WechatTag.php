<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/5/20
 * Time: 16:03
 */

namespace app\admin\model;


use think\Model;

class WechatTag extends Base {
    const TAG_LEADER = 1;
    const TAG_HEAD_COACH = 2;
    const TAG_ASSISTANT = 3;
    const TAG_STUDENT = 4;
    const TAG_VISITOR = 5;
    const TAG_STUDENT_READY = 6;
    const TAG_TO_METYPE = [
        self::TAG_LEADER  => WechatUser::MEMBER_TYPE_NULL,//领导
        self::TAG_HEAD_COACH  => WechatUser::MEMBER_TYPE_COACH,//主教练
        self::TAG_ASSISTANT  => WechatUser::MEMBER_TYPE_COACH,//助教
        self::TAG_STUDENT  => WechatUser::MEMBER_TYPE_STUDENT,//学员
        self::TAG_VISITOR  => WechatUser::MEMBER_TYPE_NULL,//游客
        self::TAG_STUDENT_READY  => WechatUser::MEMBER_TYPE_STUDENT,//预备学员
    ];
    public function Wechat_user() {
        return $this->belongsToMany('WechatUser','WechatTag');
    }
}