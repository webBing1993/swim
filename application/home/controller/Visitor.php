<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
use app\home\model\WechatUser;
use app\home\model\WechatUserSign;
use app\home\model\UserClass;
use think\Db;
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
		$userId = session('userId');
		//$mobile = "18767104335";
		$data = array(
				'userid' => $userId,
				'mobile' => $mobile,
		);
		$msg = WechatUser::where($data)->find();
		if(!empty($msg)) {
			//学员签到先判断教练是否签到
			if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
				if($msg['coach_id']){
					$coachSign = WechatUserSign::checkUserSign($msg['coach_id']);
				}
				if(empty($coachSign)){
					return $this->error("教练还未签到");
				}
			}
			if(!$msg['class_id']){
				return $this->error("未设置队伍");
			}
			$userClass = UserClass::where(['id'=>$msg['class_id']])->find();
			if(!$userClass){
				return $this->error("找不到队伍");
			}
			if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
				//学员提前三十分钟可以签到
				$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+30 minute')));
			}else{
				//教练提前六十分钟可以签到
				$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+60 minute')));
			}
			$start_time = strtotime($userClass['start_time']);
			$end_time = strtotime($userClass['end_time']);
			if($real_time < $start_time){
				return $this->error("未到签到时间");
			}else{
				if(time() > $start_time){
					if(time() > $end_time){//失败
						return $this->error("已过签到时间");
					}else{//迟到
						$userSign = WechatUserSign::checkUserSign($userId);
						if(!empty($userSign)){
							return $this->error("今天已签到");
						}
						$data = array(
								'userid' => $userId,
								'name' => $msg['name'],
								'mobile' => $mobile,
								'date' => date('Y-m-d'),
								'status' => WechatUserSign::STATUS_LATE,
						);
						if(WechatUserSign::create($data)) {//迟到
							return $this->success("签到成功", Url('Visitor/index'));
						}else {
							return $this->error("签到失败");
						}
					}
				}else{//正常
					$userSign = WechatUserSign::checkUserSign($userId);
					if(!empty($userSign)){
						return $this->error("今天已签到");
					}
					$data = array(
							'userid' => $userId,
							'name' => $msg['name'],
							'mobile' => $mobile,
							'date' => date('Y-m-d'),
					);
					if(WechatUserSign::create($data)) {//正常
						return $this->success("签到成功", Url('Visitor/index'));
					}else {
						return $this->error("签到失败");
					}
				}
			}
		}else {
			return $this->error("无效的条形码");
		}
	}

}