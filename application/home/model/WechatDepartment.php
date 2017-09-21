<?php
/**
 * Created by PhpStorm.
 * User: stiff <1570004138@163.com>
 * Date: 2017/6/5
 * Time: 16:15
 */
namespace app\home\model;
use think\Model;

class WechatDepartment extends Model{
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
}