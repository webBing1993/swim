<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/3 0003
 * Time: 下午 3:02
 */
/**
 * Created by PhpStorm.
 * User: stiff <1570004138@163.com>
 * Date: 2017/6/5
 * Time: 13:53
 */
namespace app\home\controller;


/**
 * 审核页面
 */
class Review extends Base{
    /**
     * @return mixed
     * 未审核列表
     */
    public function reviewlist() {

        return $this->fetch();
    }

    /**
     * @return mixed
     * 已审核列表
     */
    public function passlist() {

        return $this->fetch();
    }


}