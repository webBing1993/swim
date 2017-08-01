<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 16:12
 */

namespace app\home\controller;
use app\admin\model\News;
use app\home\model\Apply;
use app\home\model\Browse;
use app\home\model\Collect;
use app\home\model\Comment;
use app\home\model\Like;
use app\admin\model\Push;
use app\home\model\Picture;
use app\home\model\WechatUser;
use think\Controller;

use app\home\model\Notice as NoticeModel;
use think\Db;

/**
 * Class Notice
 * @package  通知   活动
 */
class Notice extends Base {
    /**
     * 主页
     */
	public function index(){
		return $this->fetch();
	}
	/**
	 * 详情页
	 */
	public function detail(){
		return $this->fetch();
	}

}