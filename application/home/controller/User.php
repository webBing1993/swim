<?php


namespace app\home\controller;
/**
 * 个人中心
 */
class User extends Base{
    /**
     * 首页
     */
    public function index() {

        return $this->fetch();
    }

    /**
     * 个人信息
     */
    public function personal() {

        return $this->fetch();
    }


}