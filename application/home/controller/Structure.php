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
	 * 详情页
	 */
	public function detail() {

		return $this->fetch();
	}

}