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
use app\home\model\WeekSummary;
use app\home\model\WeekPlan;
use think\Db;
/**
 * Class Coach
 * @package 教练频道
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
			$headStudentModel = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->order('enrollday desc')->select();
			foreach($headStudentModel as $key => $model){
				$headStudentModel[$key]['age'] = $model['identity'] ? date("Y")-substr($model['identity'], 6, 4)+1 : '_ ';
				$headStudentModel[$key]['count'] = WechatUserSign::where(['userid' => $model['userid']])->count();
			}
			$this->assign('userModel', $userModel);
			$this->assign('tag', false);
			$this->assign('headStudentModel',$headStudentModel);
		}else{//助教
			$longStudentModel = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT, 'department' => WechatDepartment::DEPARTMENT_LONG_STUDENT])->order('enrollday desc')->select();
			$potentialStudentModel = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT, 'department' => WechatDepartment::DEPARTMENT_POTENTIAL_STUDENT])->order('enrollday desc')->select();
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
		if(!$userId){
			return "找不到学员";
		}
		$userModel = WechatUser::where(['userid' => $userId])->find();
		$userModel['age'] = $userModel['identity'] ? date("Y")-substr($userModel['identity'], 6, 4)+1 : '_ ';
		$year = input('year', date('Y'));
		$month = input('month', date('n'));
		$month = $month<10 ? '0'.intval($month) : $month;
		$res = array('normal' => [], 'late' => [], 'absence' => []);
		if($year.$month > date("Ym")){
			return json_encode($res);
		}
		if($year.$month == date("Ym")){
			$days = date('d')-1;
		}else{
			$days = date('t',strtotime($year.'-'.$month));
		}
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
		if(IS_POST) {
			return json_encode($res);
		}else{
			$this->assign('userModel',$userModel);
			$this->assign('normal',json_encode($res['normal']));
			$this->assign('late',json_encode($res['late']));
			$this->assign('absence',json_encode($res['absence']));
			$this->assign('late_count',count($res['late']));
			$this->assign('absence_count',count($res['absence']));
			$this->assign('did',$userId);
			return $this->fetch();
		}
	}
	/**
	 * 我的签到
	 */
	public function mysign() {
		$userId = session('userId');
		$year = input('year', date('Y'));
		$month = input('month', date('n'));
		$month = $month<10 ? '0'.intval($month) : $month;
		$res = array('normal' => [], 'late' => [], 'rest' => []);
		if($year.$month > date("Ym")){
			return json_encode($res);
		}
		if($year.$month == date("Ym")){
			$days = date('d')-1;
		}else{
			$days = date('t',strtotime($year.'-'.$month));
		}
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
				$res['rest'][] = intval($i);//缺卡
			}
		}
		if(IS_POST) {
			return json_encode($res);
		}else{
			$this->assign('normal',json_encode($res['normal']));
			$this->assign('late',json_encode($res['late']));
			$this->assign('rest',json_encode($res['rest']));
			return $this->fetch();
		}
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
		$date = input('date', date('Y-m-d'));
		$res = array('normal' => [], 'abnormal' =>['late' => [], 'absence' => []]);
		if(strtotime($date) < time()){
			$useridArr = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->order("name")->column('name','userid');
			//var_dump($useridArr);die;
			$modelAll = [];
			if($useridArr){
				$modelAll = WechatUserSign::where(['userid' => ['in', array_keys($useridArr)], "date" => $date])->order("create_time desc")->select();
				//var_dump($modelAll);die;
			}
			$all_users = [];
			if($modelAll) {
				foreach ($modelAll as $model) {
					$all_users[] = $model['userid'];
					if ($model['status'] == WechatUserSign::STATUS_NORMAL) {//正常
						$res['normal'][$model['userid']]['name'] = $model['name'];
						$res['normal'][$model['userid']]['time'] = date("H:i", $model['create_time']);
					}
					if ($model['status'] == WechatUserSign::STATUS_LATE) {//迟到
						$res['abnormal']['late'][$model['userid']]['name'] = $model['name'];
						$res['abnormal']['late'][$model['userid']]['time'] = date("H:i", $model['create_time']);
					}
				}
			}
			foreach ($useridArr as $uid => $name) {
				if(!in_array($uid, $all_users)) {//所有的缺卡
					$coachSignModel = WechatUserSign::where(['userid' => $userId, "date" => $date])->find();
					if($coachSignModel){//教练这天有上课=缺卡
						$res['abnormal']['absence'][$uid]['name'] = $name;
						$res['abnormal']['absence'][$uid]['time'] = "__:__";
					}
				}
			}
			//var_dump($res);die;
		}
		if(IS_POST) {
			return json_encode($res);
		}else{
			$this->assign('res',$res);
			return $this->fetch();
		}
	}
	/**
	 * 个人签到条形码
	 */
	public function code(){
		$userId = session('userId');
		$res = WechatUser::where(['userid' => $userId])->field('mobile, header, avatar')->find()->toArray();
		$data['mobile'] = $res['mobile'];
		if(empty($res['header'])){
			$data['header'] = param_to($res['avatar'], \think\Config::get('de_header'));
		}else{
			$data['header'] = get_cover($res['header'], 'path');
		}
		//var_dump($data);
//		$mobile = WechatUser::where(['userid' => $userId])->value('mobile');
//		$url = bar_code($mobile);
		$this->assign('data',$data);
		return $this->fetch();
	}
	/**
	 * 每周计划/每周总结 列表
	 */
	public function weekPlan(){
		$userId = session('userId');
		$did = input('did');
		$time = input('time', date('Y年m月'));
		$res = [];
		if($did){//每周总结
			$res = WeekSummary::getList($userId, $time);
		}else{//每周计划
			$res = WeekPlan::getList($userId, $time);
		}
		//var_dump($res);die;
		if(IS_POST) {
			return json_encode($res);
		}else{
			$this->assign('did',$did);
			$this->assign('res',$res);
			return $this->fetch();
		}
	}
	/**
	 * 课时计划列表
	 */
	public function classPlan(){
		$userId = session('userId');
		$did = input('did');
		$time = input('time', date('Y年m月'));
		$res = [];
		if($did){//每周总结
			$res = WeekSummary::getList($userId, $time);
		}else{//每周计划
			$res = WeekSummary::getList($userId, $time);
		}
		//var_dump($res);die;
		if(IS_POST) {
			return json_encode($res);
		}else{
			$this->assign('res',$res);
			return $this->fetch();
		}
	}
	/**
	 * 每周计划发布/编辑
	 */
	public function pweekPlan(){
		if(IS_POST) {
			$data = input('post.');
			$weekPlanModel = new WeekPlan();
			$data['start'] = strtotime($data['start']);
			$data['end'] = strtotime($data['end']);
			if(empty($data['id'])) {//新增
				$data['userid'] = session('userId');
				unset($data['id']);
				$info = $weekPlanModel->save($data);
			}else{//修改
				$info = $weekPlanModel->save($data,['id'=>input('id')]);
			}
			if($info) {
				return $this->success("保存成功",Url('weekPlan'));
			}else{
				if(empty($weekPlanModel->getError())) {//未修改内容
					return $this->success("保存成功",Url('weekPlan'));
				}else{
					return $this->error($weekPlanModel->getError());
				}
			}
		}else{
			$id = input('id');
			$res = [];
			if($id){
				$res = WeekPlan::getModelById($id);
			}
			$this->assign('res', $res);
			return $this->fetch();
		}
	}
	/**
	 * 课时计划发布/编辑
	 */
	public function pclassPlan(){
		return $this->fetch();
	}
	/**
	 * 每周总结发布/编辑
	 */
	public function pweekSummary(){
		if(IS_POST) {
			$data = input('post.');
			$weekSummaryModel = new WeekSummary();
			$data['start'] = strtotime($data['start']);
			$data['end'] = strtotime($data['end']);
			if(empty($data['id'])) {//新增
				$data['userid'] = session('userId');
				unset($data['id']);
				$info = $weekSummaryModel->save($data);
			}else{//修改
				$info = $weekSummaryModel->save($data,['id'=>input('id')]);
			}
			if($info) {
				return $this->success("保存成功",Url('weekPlan'));
			}else{
				if(empty($weekSummaryModel->getError())) {//未修改内容
					return $this->success("保存成功",Url('weekPlan'));
				}else{
					return $this->error($weekSummaryModel->getError());
				}
			}
		}else{
			$id = input('id');
			$res = [];
			if($id){
				$res = WeekSummary::getModelById($id);
			}
			$this->assign('res', $res);
			return $this->fetch();
		}
	}
	/**
	 * 每周计划 详情
	 */
	public function dweekPlan(){
		$id = input('id');
		$info['views'] = array('exp','`views`+1');
		WeekPlan::where('id',$id)->update($info);
		$res = WeekPlan::getModelById($id);
		//var_dump($res);die;
		$this->assign('res',$res);
		return $this->fetch();
	}
	/**
	 * 课时计划 详情
	 */
	public function dclassPlan(){
		return $this->fetch();
	}
	/**
	 * 每周总结 详情
	 */
	public function dweekSummary(){
		$id = input('id');
		$info['views'] = array('exp','`views`+1');
		WeekSummary::where('id',$id)->update($info);
		$res = WeekSummary::getModelById($id);
		//var_dump($res);die;
		$this->assign('res',$res);
		return $this->fetch();
	}
}