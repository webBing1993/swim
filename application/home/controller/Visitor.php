<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
/**
 * 游客频道
 */
class Visitor extends Base{
	/**
	 * 首页
	 */
	public function index() {
		$this->jssdk();
		return $this->fetch();
	}

}