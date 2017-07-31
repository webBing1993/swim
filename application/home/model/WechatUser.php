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

    public function checkUserExist($userId) {
        return $this->where('userId', $userId)->find();
    }

    public function departmentName() {

        return $this->hasOne("WechatDepartment","id","department");
    }
}