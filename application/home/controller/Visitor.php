<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
use app\home\model\WechatUser;
use app\home\model\WechatTag;
use app\home\model\WechatUserTag;
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
	public function index(){
		$userId = session('userId');
		$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
		$map = array(
				'sn.status' => 1,
				'sn.recommend' => 1,
				'snt.tagid' =>$tag_id,
		);
		$order = array("sn.create_time desc");
		$newsList = \think\Db::field('sn.id,sn.front_cover,sn.title,sn.create_time')
				->table('sw_notice sn')
				->join('sw_notice_tag snt','sn.id = snt.pid')
				->where($map)
				->order($order)
				->limit(2)
				->select();
		//$newsList = Notice::where(['status' => 1, 'recommend' => 1])->order('create_time desc')->field('id,front_cover,title,create_time')->limit(2)->select();
		$this->assign('tag_id',$tag_id);
		$this->assign('newsList',$newsList);
		return $this->fetch();
	}
	public function demo(){
		$userId = session('userId');
		$id = input('id', 3);
		if($id == 1){
			$tag_id = 1;
		}
		if($id == 2){
			$tag_id = 2;
		}
		if($id == 3){
			$tag_id = 4;
		}
		//$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
		$map = array(
				'sn.status' => 1,
				'sn.recommend' => 1,
				'snt.tagid' =>$tag_id,
		);
		$order = array("sn.create_time desc");
		$newsList = \think\Db::field('sn.id,sn.front_cover,sn.title,sn.create_time')
				->table('sw_notice sn')
				->join('sw_notice_tag snt','sn.id = snt.pid')
				->where($map)
				->order($order)
				->limit(2)
				->select();
		//$newsList = Notice::where(['status' => 1, 'recommend' => 1])->order('create_time desc')->field('id,front_cover,title,create_time')->limit(2)->select();
		$this->assign('tag_id',$tag_id);
		$this->assign('newsList',$newsList);
		return $this->fetch('index');
	}

	public function sign() {
		$msg = WechatUser::where(['mobile' => input('mobile')])->find();
		if(!empty($msg)) {
			$userId = $msg['userid'];
			$all_num = 0;
			$current_num = 0;
			$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
			//学员签到先判断教练是否签到
			if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
				if($tag_id == WechatTag::TAG_STUDENT_READY){
					$num = WechatUserSign::where(['userid' => $userId, "FROM_UNIXTIME(create_time, '%Y-%m')" => date('Y-m')])->count();
					if($num>=15){
						return $this->error("本月已满15节课");
					}
				}
				if($msg['coach_id']){
					$coachSign = WechatUserSign::checkUserSign($msg['coach_id']);
					if(empty($coachSign)){
						return $this->error("教练还未签到");
					}
				}
			}
			if($tag_id != WechatTag::TAG_STUDENT_SPECIAL){
				if(!$msg['class_id']){
					return $this->error("找不到班级");
				}
				$userClass = UserClass::where(['id'=>$msg['class_id']])->find();
				if(!$userClass){
					return $this->error("找不到班级！");
				}
			}
			if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
				//学员提前15分钟可以签到
				if($tag_id != WechatTag::TAG_STUDENT_SPECIAL){
					$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+15 minute')));
					$current_num = WechatUserSign::where(['coach_id' => $msg['coach_id'], 'class_id' => $msg['class_id'], 'date' => date('Y-m-d'), 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
					if($current_num>=25){
						return $this->error("名额已满");
					}
				}else{//特殊学员
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
						$response = array(
								"type" => WechatUser::MEMBER_TYPE_STUDENT,
								"coach_id" => $msg['coach_id'],
								"name" => $msg['name'],
								"num" => 1,
								"time" => date("H:i:s",$model->create_time),
								"status_txt" => "正常",
								"class_id" => $msg['class_id'],
						);
						return $this->success("签到成功", '', $response);
					}else {
						return $this->error("签到失败");
					}
				}
			}else{
				//教练提前15分钟可以签到
				$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+15 minute')));
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
						if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {//学员
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
								$response[] = array(
										"type" => WechatUser::MEMBER_TYPE_STUDENT,
										"coach_id" => $msg['coach_id'],
										"name" => $msg['name'],
										"num" => $current_num+1,
										"time" => date("H:i:s",$model->create_time),
										"status_txt" => "迟到",
										"class_id" => $msg['class_id'],
								);
								return $this->success("签到成功", '', $response);
							}else {
								return $this->error("签到失败");
							}
						}else{//教练
							$classList =  WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->field('class_id')->group('class_id')->select();
							if(!$classList){
								return $this->error("学员未设置班级");
							}
							$response = [];
							foreach($classList as $model){
								$data = array(
										'userid' => $userId,
										'name' => $msg['name'],
										'mobile' => input('mobile'),
										'class_id' => $model['class_id'],
										'date' => date('Y-m-d'),
										'status' => WechatUserSign::STATUS_LATE,
										'member_type' => $msg['member_type'],
										'coach_id' => $msg['coach_id'],
								);
								WechatUserSign::create($data);
								$all_num =  WechatUser::where(['coach_id' => $userId, 'class_id' => $model['class_id'], 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
								$all_num = $all_num>25 ? 25 : $all_num;
								$userClassModel = UserClass::where(['id'=>$model['class_id']])->find();
								$response[] = array(
										"type" => WechatUser::MEMBER_TYPE_COACH,
										"coach_id" => $userId,
										"name" => $msg['name'],
										"num" => $all_num,
										"class_id" => $model['class_id'],
										"class_name" => $userClassModel['start_time'].'-'.$userClassModel['end_time'],
								);
							}
							return $this->success("签到成功", '', $response);
						}
					}
				}else{//正常
					$userSign = WechatUserSign::checkUserSign($userId);
					if(!empty($userSign)){
						return $this->error("今天已签到");
					}
					if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {//学员
						$data = array(
								'userid' => $userId,
								'name' => $msg['name'],
								'mobile' => input('mobile'),
								'class_id' => $msg['class_id'],
								'date' => date('Y-m-d'),
								'member_type' => $msg['member_type'],
								'coach_id' => $msg['coach_id'],
						);
						if($model = WechatUserSign::create($data)) {//迟到
							$response[] = array(
									"type" => WechatUser::MEMBER_TYPE_STUDENT,
									"coach_id" => $msg['coach_id'],
									"name" => $msg['name'],
									"num" => $current_num+1,
									"time" => date("H:i:s",$model->create_time),
									"status_txt" => "正常",
									"class_id" => $msg['class_id'],
							);
							return $this->success("签到成功", '', $response);
						}else {
							return $this->error("签到失败");
						}
					}else {//教练
						$classList =  WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->field('class_id')->group('class_id')->select();
						if(!$classList){
							return $this->error("学员未设置班级");
						}
						$response = [];
						foreach($classList as $model){
							$data = array(
									'userid' => $userId,
									'name' => $msg['name'],
									'mobile' => input('mobile'),
									'class_id' => $model['class_id'],
									'date' => date('Y-m-d'),
									'member_type' => $msg['member_type'],
									'coach_id' => $msg['coach_id'],
							);
							WechatUserSign::create($data);
							$all_num =  WechatUser::where(['coach_id' => $userId, 'class_id' => $model['class_id'], 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
							$all_num = $all_num>25 ? 25 : $all_num;
							$userClassModel = UserClass::where(['id'=>$model['class_id']])->find();
							$response[] = array(
									"type" => WechatUser::MEMBER_TYPE_COACH,
									"coach_id" => $userId,
									"name" => $msg['name'],
									"num" => $all_num,
									"class_id" => $model['class_id'],
									"class_name" => $userClassModel['start_time'].'-'.$userClassModel['end_time'],
							);
						}
						return $this->success("签到成功", '', $response);
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
	public function lists(){
		$date = date('Y-m-d');
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
				$res[$userid.$class_id]['all_num'] = $res[$userid.$class_id]['all_num']>25 ? 25 : $res[$userid.$class_id]['all_num'];
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