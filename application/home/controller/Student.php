<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
/**
 * 学员频道
 */
class Student extends Base{
	/**
	 * 首页
	 */
	public function index() {

		return $this->fetch();
	}
	/**
	 * 我的签到
	 */
	public function mysign() {

		return $this->fetch();
	}
}