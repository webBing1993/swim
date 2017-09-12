<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
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
		$chiefCoachModel = WechatUser::where(['department' => WechatDepartment::DEPARTMENT_CHIEF_COACH])->find();
		$headCoachList = WechatUser::where(['department' => WechatDepartment::DEPARTMENT_HEAD_COACH])->select();
		foreach($headCoachList as $key => $model){
			$headCoachList[$key]['count'] = WechatUser::where(['coach_id' => $model['userid']])->count();
			$headCoachList[$key]['assistant'] = WechatUser::where(['coach_id' => $model['userid']])->column('name','userid');
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
		$userList = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->select();
		foreach($userList as $key => $model){
			$userList[$key]['age'] = $model['identity'] ? date("Y")-substr($model['identity'], 6, 4)+1 : '_ ';
			$userList[$key]['count'] = WechatUserSign::where(['userid' => $model['userid']])->count();
		}
		//var_dump($userList);die;
		$this->assign('coachModel',$coachModel);
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
		$coachModel = WechatUser::where(['userid' => $userId])->find();
		$coachModel['age'] = $coachModel['identity'] ? date("Y")-substr($coachModel['identity'], 6, 4)+1 : '_ ';
		$this->assign('coachModel',$coachModel);
		return $this->fetch();
	}
}