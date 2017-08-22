<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
/**
 * 教练信息
 */
class Structure extends Base{
	/**
	 * 首页
	 */
	public function index() {

		return $this->fetch();
	}
	/**
	 * 教练详情页
	 */
	public function detail() {

		return $this->fetch();
	}
	/**
	 * 学员个人信息页
	 */
	public function student() {

		return $this->fetch();
	}
	/**
	 * 教练个人信息页
	 */
	public function coach() {

		return $this->fetch();
	}
}