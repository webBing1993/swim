<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/5/19
 * Time: 9:43
 */

namespace app\admin\model;


use think\Model;

class WechatUser extends Base
{
    const MEMBER_TYPE_NULL = 0;
    const MEMBER_TYPE_COACH = 1;
    const MEMBER_TYPE_STUDENT = 2;
    const MEMBER_TYPE_ARRAY = [
        self::MEMBER_TYPE_NULL  => '未定义',
        self::MEMBER_TYPE_COACH  => '教练',
        self::MEMBER_TYPE_STUDENT => '学员',
    ];

    public function WechatMessage() {
        return $this->belongsTo('WechatMessage');
    }

    public function Wechat_department() {
        return $this->belongsToMany('Wechat_department','Wechat_department_user');
    }

    public function Wechat_tag() {
        return $this->belongsToMany('Wechat_tag','Wechat_user_tag');
    }
}