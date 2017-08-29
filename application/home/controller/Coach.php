<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 15:56
 */

namespace app\home\controller;
use app\home\model\WechatUser;
use app\home\model\WechatTag;
use app\home\model\WechatUserTag;
use app\home\model\WechatDepartment;
use app\home\model\WechatUserSign;
use think\Db;
/**
 * Class Coach
 * @package 新闻动态
 */
class Coach extends Base {
    /**
     * 主页
     */
    public function index(){
		$userId = session('userId');
		$userModel = WechatUser::where(['userid' => $userId])->find();
		$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
		if($tag_id == WechatTag::TAG_HEAD_COACH){//主教练
			$headStudentModel = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->select();
			foreach($headStudentModel as $key => $model){
				$headStudentModel[$key]['age'] = $model['identity'] ? date("Y")-substr($model['identity'], 6, 4)+1 : '_ ';
				$headStudentModel[$key]['count'] = WechatUserSign::where(['userid' => $model['userid']])->count();
			}
			$this->assign('tag', false);
			$this->assign('headStudentModel',$headStudentModel);
		}else{//助教
			$longStudentModel = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT, 'department' => WechatDepartment::DEPARTMENT_LONG_STUDENT])->select();
			$potentialStudentModel = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT, 'department' => WechatDepartment::DEPARTMENT_POTENTIAL_STUDENT])->select();
			foreach($longStudentModel as $key => $model){
				$longStudentModel[$key]['age'] = $model['identity'] ? date("Y")-substr($model['identity'], 6, 4)+1 : '_ ';
				$longStudentModel[$key]['count'] = WechatUserSign::where(['userid' => $model['userid']])->count();
			}
			foreach($potentialStudentModel as $key => $model){
				$potentialStudentModel[$key]['age'] = $model['identity'] ? date("Y")-substr($model['identity'], 6, 4)+1 : '_ ';
				$potentialStudentModel[$key]['count'] = WechatUserSign::where(['userid' => $model['userid']])->count();
			}
			$this->assign('userModel', $userModel);
			$this->assign('tag', true);
			$this->assign('longStudentModel',$longStudentModel);
			$this->assign('potentialStudentModel',$potentialStudentModel);
		}
		return $this->fetch();
	}
	/**
	 * 学员签到情况（按学员）
	 */
	public function sign(){
		$userId = input('did');
		$userModel = WechatUser::where(['userid' => $userId])->find();
		$userModel['age'] = $userModel['identity'] ? date("Y")-substr($userModel['identity'], 6, 4)+1 : '_ ';
		$year = date("Y");
		$month = date("m");
		$days = date('d')-1;
		$res = array('normal' => [], 'late' => [], 'absence' => []);
		$modelAll = WechatUserSign::where(['userid' => $userId, "FROM_UNIXTIME(UNIX_TIMESTAMP(date),'%Y-%m')" => $year.'-'.$month])->select();
		$all_days = [];
		if($modelAll) {
			foreach ($modelAll as $model) {
				$all_days[] = $model['date'];
				if ($model['status'] == WechatUserSign::STATUS_NORMAL) {//正常
					$res['normal'][] = intval(date('j', strtotime($model['date'])));
				}
				if ($model['status'] == WechatUserSign::STATUS_LATE) {//迟到
					$res['late'][] = intval(date('j', strtotime($model['date'])));
				}
			}
		}
		for ($i=1; $i<=$days; $i++) {//当天不计算缺卡
			$i = $i<10 ? '0'.$i : $i;
			if(!in_array($year.'-'.$month.'-'.$i, $all_days)){//所有的缺卡
				$coach_id = WechatUser::where(['userid' => $userId])->value('coach_id');
				$coachSignModel = WechatUserSign::where(['userid' => $coach_id, "date" => $year.'-'.$month.'-'.$i])->find();
				if($coachSignModel){//教练这天有上课
					$res['absence'][] = intval($i);//缺卡
				}
			}
		}
		$this->assign('userModel',$userModel);
		$this->assign('normal',json_encode($res['normal']));
		$this->assign('late',json_encode($res['late']));
		$this->assign('absence',json_encode($res['absence']));
		$this->assign('late_count',count($res['late']));
		$this->assign('absence_count',count($res['absence']));
		$this->assign('did',$userId);
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
		$userId = session('userId');
		//$date = input('date', date('Y-m-d'));
		$date = input('date', '2017-08-24');
		$res = array('normal' => [], 'abnormal' =>['late' => [], 'absence' => []]);
		if(strtotime($date) < time()){
			$useridArr = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->column('name','userid');
			//var_dump($useridArr);die;
			$modelAll = [];
			if($useridArr){
				$modelAll = WechatUserSign::where(['userid' => ['in', array_keys($useridArr)], "date" => $date])->select();
				//var_dump($modelAll);die;
			}
			$all_users = [];
			if($modelAll) {
				foreach ($modelAll as $model) {
					$all_users[] = $model['userid'];
					if ($model['status'] == WechatUserSign::STATUS_NORMAL) {//正常
						$res['normal'][] = $model['name'];
					}
					if ($model['status'] == WechatUserSign::STATUS_LATE) {//迟到
						$res['abnormal']['late'][] = $model['name'];
					}
				}
			}
			foreach ($useridArr as $uid => $name) {
				if(!in_array($uid, $all_users)) {//所有的缺卡
					$coachSignModel = WechatUserSign::where(['userid' => $userId, "date" => $date])->find();
					if($coachSignModel){//教练这天有上课
						$res['abnormal']['absence'][] = $name;//缺卡
					}
				}
			}
			//var_dump($res);die;
		}
		$this->assign('res',$res);
		return $this->fetch();
	}
	/**
	 * 学员签到日期切换（按时间）
	 */
	public function changeSign(){
		$userId = session('userId');
		//$date = input('date', date('Y-m-d'));
		$date = input('date', '2017-08-24');
		$res = array('normal' => [], 'abnormal' =>['late' => [], 'absence' => []]);
		if(strtotime($date) < time()){
			$useridArr = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->column('name','userid');
			//var_dump($useridArr);die;
			$modelAll = [];
			if($useridArr){
				$modelAll = WechatUserSign::where(['userid' => ['in', array_keys($useridArr)], "date" => $date])->select();
				//var_dump($modelAll);die;
			}
			$all_users = [];
			if($modelAll) {
				foreach ($modelAll as $model) {
					$all_users[] = $model['userid'];
					if ($model['status'] == WechatUserSign::STATUS_NORMAL) {//正常
						$res['normal'][] = $model['name'];
					}
					if ($model['status'] == WechatUserSign::STATUS_LATE) {//迟到
						$res['abnormal']['late'][] = $model['name'];
					}
				}
			}
			foreach ($useridArr as $uid => $name) {
				if(!in_array($uid, $all_users)) {//所有的缺卡
					$coachSignModel = WechatUserSign::where(['userid' => $userId, "date" => $date])->find();
					if($coachSignModel){//教练这天有上课
						$res['abnormal']['absence'][] = $name;//缺卡
					}
				}
			}
			//var_dump($res);die;
		}
		return json_encode($res);
	}
	/**
	 * 个人签到条形码
	 */
	public function code(){
		$userId = session('userId');
		$mobile = WechatUser::where(['userid' => $userId])->value('mobile');
		$url = bar_code($mobile);
		$this->assign('url',$url);
		return $this->fetch();
	}
}