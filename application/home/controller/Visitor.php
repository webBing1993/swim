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
use think\Controller;
use think\Db;
use think\Collection;
/**
 * 游客频道
 */
class Visitor extends Base {
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
		$id = input('id', 3);
		if($id == 1){
			session('userId', '13588757228');
			$tag_id = 1;
		}
		if($id == 2){
			session('userId', 'tinasun1984');
			$tag_id = 2;
		}
		if($id == 3){
			session('userId', '18157133183');
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

	/*
	public function sign() {
		if(empty(input('mobile'))){
			return $this->error("无效的二维码");
		}
		if(!preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|8[0-9]|7[01678])\\d{8}$/',input('mobile'))){
			return $this->error("无效的二维码，请稍后再试");
		}
		$msg = WechatUser::where(['mobile' => input('mobile')])->find();
		if(empty($msg)) {
			return $this->error("找不到该学员，请稍后再试");
		}
		$userId = $msg['userid'];
		$user_name = WechatUser::getName($userId);
		$all_num = 0;
		$current_num = 0;
		$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
		//学员签到先判断教练是否签到
		if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
			if($tag_id == WechatTag::TAG_STUDENT_READY){
				$num = WechatUserSign::where(['userid' => $userId, "FROM_UNIXTIME(create_time, '%Y-%m')" => date('Y-m')])->count();
				if($num>=15){
					return $this->error($user_name."本月已满15节课");
				}
			}
			if($msg['coach_id']){
				$coach_name = WechatUser::getName($msg['coach_id']);
				$coachSign = WechatUserSign::checkUserSign($msg['coach_id']);
				if(empty($coachSign)){
					return $this->error($coach_name."教练还未签到");
				}
			}
		}else{
			$coach_name = WechatUser::getName($msg['userid']);
		}
		if($tag_id != WechatTag::TAG_STUDENT_SPECIAL){
			if(!$msg['class_id']){
				return $this->error($user_name."未设置上课时间");
			}
			$userClass = UserClass::where(['id'=>$msg['class_id']])->find();
			if(!$userClass){
				return $this->error($user_name."上课时间设置错误！");
			}
			$class_name = UserClass::getName($msg['class_id']);
		}
		if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
			//学员提前15分钟可以签到
			if($tag_id != WechatTag::TAG_STUDENT_SPECIAL){
				$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+15 minute')));
				$current_num = WechatUserSign::where(['coach_id' => $msg['coach_id'], 'class_id' => $msg['class_id'], 'date' => date('Y-m-d'), 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
//				if($current_num>=25){
//					return $this->error($coach_name."教练".$class_name."名额已满");
//				}
			}else{//特殊学员
				$userSign = WechatUserSign::checkUserSign($userId);
				if(!empty($userSign)){
					return $this->success("今天已签到");
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
					$response[] = array(
							"type" => WechatUser::MEMBER_TYPE_STUDENT,
							"coach_id" => $msg['coach_id'],
							"name" => $msg['name'],
							"num" => 1,
							"time" => date("H:i:s",$model->create_time),
							"status_txt" => "正常",
							"class_id" => 0,
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
			return $this->error($coach_name."教练".$class_name."未到签到时间");
		}else{
			if(time() > $start_time){
				if(time() > $end_time){//失败
					return $this->error($coach_name."教练".$class_name."已过签到时间");
				}else{//迟到
					$userSign = WechatUserSign::checkUserSign($userId);
					if(!empty($userSign)){
						return $this->success($user_name."今天已签到");
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
							//return $this->error("学员未设置班级");
							$classList =  WechatUser::where(['userid' => $userId])->field('class_id')->select();
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
							$response[] = array(
									"type" => WechatUser::MEMBER_TYPE_COACH,
									"coach_id" => $userId,
									"name" => $msg['name'],
									"num" => $all_num,
									"class_id" => $model['class_id'],
									"class_name" => UserClass::getName($model['class_id']),
							);
						}
						return $this->success("签到成功", '', $response);
					}
				}
			}else{//正常
				$userSign = WechatUserSign::checkUserSign($userId);
				if(!empty($userSign)){
					return $this->success($user_name."今天已签到");
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
						//return $this->error("学员未设置班级");
						$classList =  WechatUser::where(['userid' => $userId])->field('class_id')->select();
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
						$response[] = array(
								"type" => WechatUser::MEMBER_TYPE_COACH,
								"coach_id" => $userId,
								"name" => $msg['name'],
								"num" => $all_num,
								"class_id" => $model['class_id'],
								"class_name" => UserClass::getName($model['class_id']),
						);
					}
					return $this->success("签到成功", '', $response);
				}
			}
		}

	}*/
	/**
	 * 签到列表页
	 */
	public function fillDefect(){
		$coach_ids = input('CoachId', array());
//		if(empty($coach_ids)){
//			return $this->error('参数为空');
//		}
		$coach_ids = json_decode($coach_ids, true);
		$date = date('Y-m-d');
		$coachModel = WechatUserSign::where(["date" => $date, "member_type" => WechatUser::MEMBER_TYPE_COACH])->order("create_time desc")->group('userid')->column('userid');
		if($coachModel){
			$collection = new Collection($coachModel);
			$coachModel = $collection->toArray();
		}
		$coach_id_arr = array_diff($coachModel, $coach_ids);
		//var_dump($coach_id_arr);die;
		if($coach_id_arr){
			$response = [];
			foreach($coach_id_arr as $userId) {
				$msg = WechatUser::where(['userid' => $userId])->find();
				$classList = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->field('class_id')->group('class_id')->select();
				if (!$classList) {
					$classList = WechatUser::where(['userid' => $userId])->field('class_id')->select();
				}
				foreach ($classList as $model) {
					$current_num = WechatUserSign::where(['coach_id' => $userId, 'class_id' => $model['class_id'], 'date' => date('Y-m-d'), 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
					$all_num = WechatUser::where(['coach_id' => $userId, 'class_id' => $model['class_id'], 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
					$all_num = $all_num > 25 ? 25 : $all_num;
					$response[] = array(
							"type" => WechatUser::MEMBER_TYPE_COACH,
							"coach_id" => $userId,
							"name" => $msg['name'],
							"current_num" => $current_num,
							"all_num" => $all_num,
							"class_id" => $model['class_id'],
							"class_name" => UserClass::getName($model['class_id']),
					);
				}
			}
			return $this->success("加载成功", '', $response);
		}else{
			return $this->error("加载失败");
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
				/*$studentList = WechatUserSign::where(["date" => $date, "member_type" => WechatUser::MEMBER_TYPE_STUDENT, "coach_id" => $userid, 'class_id' => $class_id])->field("userid, name, status, FROM_UNIXTIME(create_time, '%H:%i:%s') create_time")->order("create_time desc")->limit(3)->select();
				$res[$userid.$class_id]['studentList'] = [];
				if($studentList){
					$collection = new Collection($studentList);
					$res[$userid.$class_id]['studentList'] = $collection->toArray();
				}*/
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

	/**
	 * 签到
	 * @return array
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 */
	public function newSign() {
		$date = date('Y-m-d');
		$coachModel = WechatUserSign::where(["date" => $date, "member_type" => WechatUser::MEMBER_TYPE_COACH])->order("create_time desc")->field("userid, name, class_id")->select();
		$response = [];
		if($coachModel){
			foreach($coachModel as $model){
				$userid = $model['userid'];
				$name = $model['name'];
				$class_id = $model['class_id'];
				$classModel = UserClass::where(['id' => $class_id])->field('start_time, end_time, name')->find();
				$response[$userid.$class_id]['class_name'] = $classModel['start_time'].'-'.$classModel['end_time'];
				$response[$userid.$class_id]['userid'] = $userid;
				$response[$userid.$class_id]['name'] = $name;
				$response[$userid.$class_id]['class_id'] = $class_id;
				$response[$userid.$class_id]['current_num'] = WechatUserSign::where(['coach_id' => $userid, 'class_id' => $class_id, 'date' => $date, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
				$response[$userid.$class_id]['all_num'] =  WechatUser::where(['coach_id' => $userid, 'class_id' => $class_id, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
				$response[$userid.$class_id]['all_num'] = $response[$userid.$class_id]['all_num']>25 ? 25 : $response[$userid.$class_id]['all_num'];
			}
		}
		$res = $response;
		$response = array_values($response);
		//var_dump($response);die;
		if(empty(input('mobile'))){
			return $this->error("无效的二维码", '', $response);
		}
		if(!preg_match('/^1(3[0-9]|4[57]|5[0-35-9]|8[0-9]|7[01678])\\d{8}$/',input('mobile'))){
			return $this->error("无效的二维码，请稍后再试", '', $response);
		}
		$msg = WechatUser::where(['userid' => input('mobile')])->find();
		if(empty($msg)) {
			return $this->error("找不到该学员，请稍后再试", '', $response);
		}
        $userId = $msg['userid'];
        $mobile = $msg['mobile'];
		$user_name = WechatUser::getName($userId);
		$all_num = 0;
		$current_num = 0;
		$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
		//学员签到先判断教练是否签到
		if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
			if($tag_id == WechatTag::TAG_STUDENT_READY){
				$num = WechatUserSign::where(['userid' => $userId, "FROM_UNIXTIME(create_time, '%Y-%m')" => date('Y-m')])->count();
				if($num>=15){
					return $this->error($user_name."本月已满15节课", '', $response);
				}
			}
			if($tag_id != WechatTag::TAG_STUDENT_SPECIAL){
				if(!empty($msg['coach_id'])){
					$coach_name = WechatUser::getName($msg['coach_id']);
					$coachSign = WechatUserSign::checkUserSign($msg['coach_id']);
					if(empty($coachSign)){
						return $this->error($coach_name."教练还未签到", '', $response);
					}
				}else{
					return $this->error($user_name."未设置教练", '', $response);
				}
			}
		}else{
			$coach_name = WechatUser::getName($msg['userid']);
		}
		if($tag_id != WechatTag::TAG_STUDENT_SPECIAL){
			if(!$msg['class_id']){
				return $this->error($user_name."未设置上课时间", '', $response);
			}
			$userClass = UserClass::where(['id'=>$msg['class_id']])->find();
			if(!$userClass){
				return $this->error($user_name."上课时间设置错误！", '', $response);
			}
			$class_name = UserClass::getName($msg['class_id']);
		}
		if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {
			//学员提前15分钟可以签到
			if($tag_id != WechatTag::TAG_STUDENT_SPECIAL){
				$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+15 minute')));
				$current_num = WechatUserSign::where(['coach_id' => $msg['coach_id'], 'class_id' => $msg['class_id'], 'date' => date('Y-m-d'), 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
				if($current_num>=25){
					return $this->error($coach_name."教练".$class_name."名额已满", '', $response);
				}
			}else{//特殊学员
                $userSign = WechatUserSign::checkUserSign($userId);
                if(!empty($userSign)){
                    return $this->result($response, 2, $user_name."今天已签到");
                }
                $coach_id = '';
                if($msg['name']){
                    $coach_id = 'xsfyyjxuxing439126';
                }
				$data = array(
						'userid' => $userId,
						'name' => $msg['name'],
						'mobile' => $mobile,
						'class_id' => $msg['class_id'],
						'date' => date('Y-m-d'),
						'member_type' => $msg['member_type'],
						'coach_id' => $coach_id,
				);
				if($model = WechatUserSign::create($data)) {//正常
					return $this->success($user_name."签到成功", '', $response);
				}else {
					return $this->error($user_name."签到失败", '', $response);
				}
			}
		}else{
			//教练提前15分钟可以签到
			$real_time = strtotime(date('Y-m-d H:i:s',strtotime('+15 minute')));
		}
		$start_time = strtotime($userClass['start_time']);
		$end_time = strtotime($userClass['end_time']);
		if($real_time < $start_time){
			return $this->error($coach_name."教练".$class_name."未到签到时间", '', $response);
		}else{
			if(time() > $start_time){
				if(time() > $end_time){//失败
					return $this->error($coach_name."教练".$class_name."已过签到时间", '', $response);
				}else{//迟到
					$userSign = WechatUserSign::checkUserSign($userId);
					if(!empty($userSign)){
						return $this->result($response, 2, $user_name."今天已签到");
					}
					if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {//学员
						$data = array(
								'userid' => $userId,
								'name' => $msg['name'],
								'mobile' => $mobile,
								'class_id' => $msg['class_id'],
								'date' => date('Y-m-d'),
								'status' => WechatUserSign::STATUS_LATE,
								'member_type' => $msg['member_type'],
								'coach_id' => $msg['coach_id'],
						);
						if($model = WechatUserSign::create($data)) {//迟到
							$res[$msg['coach_id'].$msg['class_id']]['current_num']++;
							$new_user = $res[$msg['coach_id'].$msg['class_id']];
							unset($res[$msg['coach_id'].$msg['class_id']]);
							$response = array_values($res);
							array_unshift($response, $new_user);
							return $this->success($user_name."签到成功", '', $response);
						}else {
							return $this->error($user_name."签到失败", '', $response);
						}
					}else{//教练
						$classList =  WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->field('class_id')->group('class_id')->select();
						if(!$classList){
							$classList =  WechatUser::where(['userid' => $userId])->field('class_id')->select();
						}
						foreach($classList as $model){
							$data = array(
									'userid' => $userId,
									'name' => $msg['name'],
									'mobile' => $mobile,
									'class_id' => $model['class_id'],
									'date' => date('Y-m-d'),
									'status' => WechatUserSign::STATUS_LATE,
									'member_type' => $msg['member_type'],
									'coach_id' => $msg['coach_id'],
							);
							WechatUserSign::create($data);
							$all_num =  WechatUser::where(['coach_id' => $userId, 'class_id' => $model['class_id'], 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
							$all_num = $all_num>25 ? 25 : $all_num;
							$new_coach = array(
									"class_name" => UserClass::getName($model['class_id']),
									"userid" => $userId,
									"name" => $msg['name'],
									"class_id" => $model['class_id'],
									"current_num" => 0,
									"all_num" => $all_num,
							);
							array_unshift($response, $new_coach);
						}
						return $this->success($user_name."教练签到成功", '', $response);
					}
				}
			}else{//正常
				$userSign = WechatUserSign::checkUserSign($userId);
				if(!empty($userSign)){
					return $this->result($response, 2, $user_name."今天已签到");
				}
				if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {//学员
					$data = array(
							'userid' => $userId,
							'name' => $msg['name'],
							'mobile' => $mobile,
							'class_id' => $msg['class_id'],
							'date' => date('Y-m-d'),
							'member_type' => $msg['member_type'],
							'coach_id' => $msg['coach_id'],
					);
					if($model = WechatUserSign::create($data)) {
						$res[$msg['coach_id'].$msg['class_id']]['current_num']++;
						$new_user = $res[$msg['coach_id'].$msg['class_id']];
						unset($res[$msg['coach_id'].$msg['class_id']]);
						$response = array_values($res);
						array_unshift($response, $new_user);
						return $this->success($user_name."签到成功", '', $response);
					}else {
						return $this->error($user_name."签到失败", '', $response);
					}
				}else {//教练
					$classList =  WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->field('class_id')->group('class_id')->select();
					if(!$classList){
						$classList =  WechatUser::where(['userid' => $userId])->field('class_id')->select();
					}
					foreach($classList as $model){
						$data = array(
								'userid' => $userId,
								'name' => $msg['name'],
								'mobile' => $mobile,
								'class_id' => $model['class_id'],
								'date' => date('Y-m-d'),
								'member_type' => $msg['member_type'],
								'coach_id' => $msg['coach_id'],
						);
						WechatUserSign::create($data);
						$all_num =  WechatUser::where(['coach_id' => $userId, 'class_id' => $model['class_id'], 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->count();
						$all_num = $all_num>25 ? 25 : $all_num;
						$new_coach = array(
								"class_name" => UserClass::getName($model['class_id']),
								"userid" => $userId,
								"name" => $msg['name'],
								"class_id" => $model['class_id'],
								"current_num" => 0,
								"all_num" => $all_num,
						);
						array_unshift($response, $new_coach);
					}
					return $this->success($user_name."教练签到成功", '', $response);
				}
			}
		}

	}

}