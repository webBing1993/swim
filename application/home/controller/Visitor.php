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
use think\Collection;
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
		$msg = WechatUser::where(['mobile' => input('mobile')])->find();
		if(!empty($msg)) {
			$userId = $msg['userid'];
			$all_num = 0;
			$current_num = 0;
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
				return $this->error("找不到班级");
			}
			$userClass = UserClass::where(['id'=>$msg['class_id']])->find();
			if(!$userClass){
				return $this->error("找不到班级！");
			}
			if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
				//学员提前15分钟可以签到
				$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+15 minute')));
				$current_num = WechatUserSign::where(['coach_id' => $msg['coach_id'], 'class_id' => $msg['class_id'], 'date' => date('Y-m-d'), 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
			}else{
				//教练提前15分钟可以签到
				$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+15 minute')));
				if($msg['class_id'] == 3){
					$all_num =  WechatUser::where(['coach_id' => $userId, 'class_id' => $msg['class_id'], 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
					$response[] = array(
							"type" => WechatUser::MEMBER_TYPE_COACH,
							"coach_id" => $userId,
							"name" => $msg['name'],
							"num" => $all_num,
							"class_id" => $msg['class_id'],
							"class_name" => $userClass['start_time'].'-'.$userClass['end_time'],
					);
					$userClassFour = UserClass::where(['id'=>4])->find();
					$all_num_four =  WechatUser::where(['coach_id' => $userId, 'class_id' => 4, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
					$response[] = array(
							"type" => WechatUser::MEMBER_TYPE_COACH,
							"coach_id" => $userId,
							"name" => $msg['name'],
							"num" => $all_num_four,
							"class_id" => 4,
							"class_name" => $userClassFour['start_time'].'-'.$userClassFour['end_time'],
					);
				}else{
					$all_num =  WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
					$response = array(
							"type" => WechatUser::MEMBER_TYPE_COACH,
							"coach_id" => $userId,
							"name" => $msg['name'],
							"num" => $all_num,
							"class_id" => $msg['class_id'],
							"class_name" => $userClass['start_time'].'-'.$userClass['end_time'],
					);
				}
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
								'mobile' => input('mobile'),
								'class_id' => $msg['class_id'],
								'date' => date('Y-m-d'),
								'status' => WechatUserSign::STATUS_LATE,
								'member_type' => $msg['member_type'],
								'coach_id' => $msg['coach_id'],
						);
						if($model = WechatUserSign::create($data)) {//迟到
							if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {//学员
								$response = array(
										"type" => WechatUser::MEMBER_TYPE_STUDENT,
										"coach_id" => $msg['coach_id'],
										"name" => $msg['name'],
										"num" => $current_num+1,
										"time" => date("H:i:s",$model->create_time),
										"status_txt" => "迟到",
										"class_id" => $msg['class_id'],
								);
							}else{//教练
								if($msg['class_id'] == 3){
									$data = array(
											'userid' => $userId,
											'name' => $msg['name'],
											'mobile' => input('mobile'),
											'class_id' => 4,
											'date' => date('Y-m-d'),
											'status' => WechatUserSign::STATUS_LATE,
											'member_type' => $msg['member_type'],
											'coach_id' => $msg['coach_id'],
									);
									WechatUserSign::create($data);
								}
								/*$response = array(
										"type" => WechatUser::MEMBER_TYPE_COACH,
										"coach_id" => $userId,
										"name" => $msg['name'],
										"num" => $all_num,
								);*/
							}
							return $this->success("签到成功", '', $response);
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
							'mobile' => input('mobile'),
							'class_id' => $msg['class_id'],
							'date' => date('Y-m-d'),
							'member_type' => $msg['member_type'],
							'coach_id' => $msg['coach_id'],
					);
					if($model = WechatUserSign::create($data)) {//正常
						if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {//学员
							$response = array(
									"type" => WechatUser::MEMBER_TYPE_STUDENT,
									"coach_id" => $msg['coach_id'],
									"name" => $msg['name'],
									"num" => $current_num+1,
									"time" => date("H:i:s",$model->create_time),
									"status_txt" => "正常",
									"class_id" => $msg['class_id'],
							);
						}else{//教练
							if($msg['class_id'] == 3){
								$data = array(
										'userid' => $userId,
										'name' => $msg['name'],
										'mobile' => input('mobile'),
										'class_id' => 4,
										'date' => date('Y-m-d'),
										'member_type' => $msg['member_type'],
										'coach_id' => $msg['coach_id'],
								);
								WechatUserSign::create($data);
							}
							/*$response = array(
									"type" => WechatUser::MEMBER_TYPE_COACH,
									"coach_id" => $userId,
									"name" => $msg['name'],
									"num" => $all_num,
							);*/
						}
						return $this->success("签到成功", '', $response);
					}else {
						return $this->error("签到失败");
					}
				}
			}
		}else {
			return $this->error("无效的二维码");
		}
	}
	/**
	 * 签到列表页
	 */
	public function lists(){//date('Y-m-d')
		$date = '2017-09-11';
		$coachModel = WechatUserSign::where(["date" => $date, "member_type" => WechatUser::MEMBER_TYPE_COACH])->order("create_time desc")->field("userid, name, class_id")->select();
		//var_dump($coachModel);die;
		$res = [];
		if($coachModel){
			foreach($coachModel as $model){
				$userid = $model['userid'];
				$name = $model['name'];
				$class_id = $model['class_id'];
				$classModel = UserClass::where(['id' => $class_id])->field('start_time, end_time, name')->find();
				$res[$userid.$class_id]['class_name'] = $classModel['start_time'].'-'.$classModel['end_time'];
				$res[$userid.$class_id]['userid'] = $userid;
				$res[$userid.$class_id]['name'] = $name;
				$res[$userid.$class_id]['class_id'] = $class_id;
				$res[$userid.$class_id]['current_num'] = WechatUserSign::where(['coach_id' => $userid, 'class_id' => $class_id, 'date' => $date, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
				$res[$userid.$class_id]['all_num'] =  WechatUser::where(['coach_id' => $userid, 'class_id' => $class_id, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
				$studentList = WechatUserSign::where(["date" => $date, "member_type" => WechatUser::MEMBER_TYPE_STUDENT, "coach_id" => $userid, 'class_id' => $class_id])->field("userid, name, status, FROM_UNIXTIME(create_time, '%H:%i:%s') create_time")->order("create_time desc")->limit(3)->select();
				$res[$userid.$class_id]['studentList'] = [];
				if($studentList){
					$collection = new Collection($studentList);
					$res[$userid.$class_id]['studentList'] = $collection->toArray();
				}
			}
		}
		//var_dump($res);die;
		$this->assign('res',$res);
		return $this->fetch();
	}
	/**
	 * 签到列表-更多
	 */
	public function listMore(){
		$userid = input('id');
		$class_id = input('cid');
		if(!$userid || !$class_id){
			return $this->error("加载失败");
		}
		$studentList = WechatUserSign::where(["date" => date('Y-m-d'), "member_type" => WechatUser::MEMBER_TYPE_STUDENT, "coach_id" => $userid, 'class_id' => $class_id])->field("userid, name, status, FROM_UNIXTIME(create_time, '%H:%i:%s') create_time")->order("create_time desc")->select();
		if($studentList){
			$collection = new Collection($studentList);
			return $this->success("加载成功",'',json_encode($collection->toArray()));
		}else{
			return $this->error("加载失败");
		}
	}

}