<?php
/**
 * File: Statistics.php
 * User: Administrator
 * Date: 2017-08-09 9:30
 */
namespace app\home\controller;
/**
 * 签到概况
 */
class Statistics extends Base{
	/**
	 * 首页
	 */
	public function index() {

		return $this->fetch();
	}

}