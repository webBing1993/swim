<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/5/19
 * Time: 14:07
 */

namespace app\admin\model;


use think\Model;

class WechatDepartment extends Base
{
    const DEPARTMENT_CHIEF_COACH = 8;
    const DEPARTMENT_HEAD_COACH = 9;
    const DEPARTMENT_ASSISTANT = 19;
    const DEPARTMENT_HEAD_STUDENT = 11;
    const DEPARTMENT_LONG_STUDENT = 12;
    const DEPARTMENT_POTENTIAL_STUDENT = 13;
    const DEPARTMENT_ARRAY = [
        self::DEPARTMENT_CHIEF_COACH  => '总教练',
        self::DEPARTMENT_HEAD_COACH  => '主教练',
        self::DEPARTMENT_ASSISTANT  => '助教',
        self::DEPARTMENT_HEAD_STUDENT  => '主力学员',
        self::DEPARTMENT_LONG_STUDENT  => '长期学员',
        self::DEPARTMENT_POTENTIAL_STUDENT  => '潜力学员',
    ];

    public function Wechat_user() {
        return $this->belongsToMany('WechatUser','WechatDepartmentUser');
    }
}