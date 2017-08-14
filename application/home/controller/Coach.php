<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 15:56
 */

namespace app\home\controller;

/**
 * Class Coach
 * @package 新闻动态
 */
class Coach extends Base {
    /**
     * 主页
     */
    public function index(){
		return $this->fetch();
	}
	/**
	 * 学员签到情况（按学员）
	 */
	public function sign(){
		return $this->fetch();
	}
	/**
	 * 学员详情
	 */
	public function detail(){
		return $this->fetch();
	}
	/**
	 * 学员签到情况（按时间）
	 */
	public function allsign(){
		return $this->fetch();
	}
	/**
	 * 个人签到情况
	 */
	public function mysign(){
		return $this->fetch();
	}
	/**
	 * 个人签到条形码
	 */
	public function code(){
		return $this->fetch();
	}
}