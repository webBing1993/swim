<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
/**
 * 签到频道
 */
class Sign extends Base{
	/**
	 * 首页
	 */
	public function index() {

		return $this->fetch();
	}

}