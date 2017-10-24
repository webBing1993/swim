<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/4/25
 * Time: 17:35
 */

namespace app\home\model;
use think\Model;

class WechatUser extends Model {

    const MEMBER_TYPE_NULL = 0;
    const MEMBER_TYPE_COACH = 1;
    const MEMBER_TYPE_STUDENT = 2;
    const MEMBER_TYPE_ARRAY = [
        self::MEMBER_TYPE_NULL  => '未定义',
        self::MEMBER_TYPE_COACH  => '教练',
        self::MEMBER_TYPE_STUDENT => '学员',
    ];

    public function checkUserExist($userId) {
        return $this->where('userId', $userId)->find();
    }

    public function departmentName() {
        return $this->hasOne("WechatDepartment","id","department");
    }

    public static function getName($userId) {
        return self::where('userId', $userId)->value('name');
    }
}