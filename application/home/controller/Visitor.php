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

	public function sign() {
		$mobile = input('mobile');
		$data = array(
				'userid' => session('userId'),
				'notice_id' => $mobile,
				'status' => 1,
		);
		$Model = new NoticeEnroll();
		$msg = $Model->where($data)->find();
		if(empty($msg)) {
			$res = $Model->create($data);
			if($res) {
				NoticeModel::where('id',$mobile)->setInc("enrollnum");
				return $this->success("报名成功");
			}else {
				return $this->error("报名失败");
			}
		}else {
			return $this->error("已存在报名信息");
		}
	}

}