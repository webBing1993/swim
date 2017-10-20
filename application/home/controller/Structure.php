<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
use app\home\model\UserClass;
use app\home\model\WechatUser;
use app\home\model\WechatDepartment;
use app\home\model\WechatTag;
use app\home\model\WechatUserTag;
use app\home\model\WechatUserSign;
/**
 * 教练信息
 */
class Structure extends Base{
	/**
	 * 首页
	 */
	public function index() {
		$userId = session('userId');
		$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
		if($tag_id == WechatTag::TAG_HEAD_COACH) {//主教练
			$headCoachList = WechatUser::where(['userid' => $userId])->select();
		}else{
			$headCoachList = WechatUser::where(['department' => WechatDepartment::DEPARTMENT_HEAD_COACH])->select();
		}
		$chiefCoachModel = WechatUser::where(['department' => WechatDepartment::DEPARTMENT_CHIEF_COACH])->find();
		foreach($headCoachList as $key => $model){
			$headCoachList[$key]['count'] = WechatUser::where(['coach_id' => $model['userid'], 'member_type' => WechatUser::MEMBER_TYPE_COACH])->count();
			$headCoachList[$key]['assistant'] = WechatUser::where(['coach_id' => $model['userid'], 'member_type' => WechatUser::MEMBER_TYPE_COACH])->column('name','userid');
		}
		//var_dump($headCoachList);die;
		$this->assign('chiefCoachModel',$chiefCoachModel);
		$this->assign('headCoachList',$headCoachList);
		return $this->fetch();
	}
	/**
	 * 教练详情页
	 */
	public function detail() {
		$userId = input('did');
		$coachModel = WechatUser::where(['userid' => $userId])->find();
		$userModelAll = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->order('class_id, enrollday desc')->select();
		$userList = [];
		$class_arr = [];
		foreach($userModelAll as $key => $model){
			$userClassModel = UserClass::where(['id' => $model['class_id']])->field('start_time, end_time')->find();
			$class = $userClassModel['start_time'].' - '.$userClassModel['end_time'];
			$class_arr[$class] = $class;
			$userList[$class][$key]['userid'] = $model['userid'];
			$userList[$class][$key]['header'] = $model['header'];
			$userList[$class][$key]['avatar'] = $model['avatar'];
			$userList[$class][$key]['gender'] = $model['gender'];
			$userList[$class][$key]['name'] = $model['name'];
			$userList[$class][$key]['height'] = $model['height'];
			$userList[$class][$key]['weight'] = $model['weight'];
			$userList[$class][$key]['age'] = $model['identity'] ? date("Y")-substr($model['identity'], 6, 4)+1 : '_ ';
			$userList[$class][$key]['count'] = WechatUserSign::where(['userid' => $model['userid']])->count();
		}
		$class_arr = json_encode(array_values($class_arr));
		//var_dump($class_arr);die;
		$this->assign('coachModel',$coachModel);
		$this->assign('class_arr',$class_arr);
		$this->assign('userList',$userList);
		return $this->fetch();
	}
	/**
	 * 学员个人信息页
	 */
	public function student() {
		$userId = input('did');
		$userModel = WechatUser::where(['userid' => $userId])->find();
		$userModel['age'] = $userModel['identity'] ? date("Y")-substr($userModel['identity'], 6, 4)+1 : '_ ';
		$userModel['birthday_year'] = $userModel['identity'] ? substr($userModel['identity'], 6, 4) : null;
		$userModel['birthday_month'] = $userModel['identity'] ? substr($userModel['identity'], 10, 2) : null;
		$this->assign('userModel',$userModel);
		return $this->fetch();
	}
	/**
	 * 教练个人信息页
	 */
	public function coach() {
		$userId = input('did');
		$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
		$coachModel = WechatUser::where(['userid' => $userId])->find();
		if($tag_id == WechatTag::TAG_HEAD_COACH){
			$coachModel['tag'] = 1;
		}
		$coachModel['age'] = $coachModel['identity'] ? date("Y")-substr($coachModel['identity'], 6, 4)+1 : '_ ';
		$this->assign('coachModel',$coachModel);
		return $this->fetch();
	}
}