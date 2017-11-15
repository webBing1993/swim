<?php
/**
 * File: Statistics.php
 * User: Administrator
 * Date: 2017-08-09 9:30
 */
namespace app\home\controller;
use app\home\model\WechatUser;
use app\home\model\WechatTag;
use app\home\model\WechatUserTag;
use app\home\model\WechatUserSign;
use app\home\model\WechatDepartment;
use app\home\model\WechatDepartmentUser;
use think\Db;
/**
 * 签到概况
 */
class Statistics extends Base{
	/**
	 * 首页
	 */
	public function index() {
		ini_set("memory_limit","-1");
		$userId = session('userId');
		$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
		$start_time = input('start', date('Y-m-d'));
		$end_time = input('end', date('Y-m-d'));
		if($tag_id == WechatTag::TAG_LEADER) {
			$c_type = input('c_type');
			$s_type = input('s_type');
			$pid = input('pid', 0);
		}elseif($tag_id == WechatTag::TAG_HEAD_COACH){
            $s_type = input('s_type');
            if(!isset($s_type)){
                $c_type = 2;
                $s_type = 2;
            }
			$pid = input('pid', 0);
		}elseif($tag_id == WechatTag::TAG_ASSISTANT){
			$s_type = 2;
			$pid = $userId;
		}else{
			return $this ->fetch('user/null');
		}
        $query = Db::field('distinct wus.userid, wus.coach_id, wus.member_type, wus.mobile, wus.date, wus.status, wu.department, wu2.department coach_department')
            ->table('sw_wechat_user_sign wus')
            ->join('sw_wechat_user wu','wus.userid = wu.userid')
            ->join('sw_wechat_user wu2','wus.coach_id = wu2.userid')
            ->where('date',['>=',$start_time],['<=',$end_time],'and');
		$coachModel = [];
		$userModel = [];
		if(isset($c_type)){
			if($c_type == 1){
				$query->where(['wu.department' => WechatDepartment::DEPARTMENT_HEAD_COACH]);
				$coachModel = WechatDepartmentUser::where(['departmentid' => WechatDepartment::DEPARTMENT_HEAD_COACH])->column('userid');
			}elseif($c_type == 2){
				if($tag_id == WechatTag::TAG_HEAD_COACH){//主教练权限
					$coachModel = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_COACH])->column('userid');
                    $query->where(['wus.coach_id' => $userId, 'wus.member_type' => WechatUser::MEMBER_TYPE_COACH]);
					//$query->where('userid', 'in', $coachModel);
                    if($s_type == 2){
                        $coach_ids = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_COACH])->column('userid');
                        $query->whereOr('wus.coach_id', 'in', $coach_ids);
                        $userModel = WechatUser::where('coach_id', 'in', $coach_ids)->column('userid');
                    }
				}else{//领导权限
					$query->where(['wu.department' => WechatDepartment::DEPARTMENT_ASSISTANT]);
					$coachModel = WechatDepartmentUser::where(['departmentid' => WechatDepartment::DEPARTMENT_ASSISTANT])->column('userid');
				}
			}else{
				$coachModel = WechatDepartmentUser::where(['departmentid' => WechatDepartment::DEPARTMENT_HEAD_COACH])->whereOr(['departmentid' => WechatDepartment::DEPARTMENT_ASSISTANT])->column('userid');
			}
		}elseif(isset($s_type)){
			if($pid){
				$query->where(['wus.coach_id' => $pid]);
				$userModel = WechatUser::where(['coach_id' => $pid])->column('userid');
			}else{
				if($s_type == 1){
					$query->where(['wu2.department' => WechatDepartment::DEPARTMENT_HEAD_COACH]);
					$userModel = WechatDepartmentUser::where(['departmentid' => WechatDepartment::DEPARTMENT_HEAD_STUDENT])->column('userid');
				}elseif($s_type == 2){
					if($tag_id == WechatTag::TAG_HEAD_COACH){//主教练权限
						$coach_ids = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_COACH])->column('userid');
						$query->where('wus.coach_id', 'in', $coach_ids);
						$userModel = WechatUser::where('coach_id', 'in', $coach_ids)->column('userid');
					}elseif($tag_id == WechatTag::TAG_ASSISTANT){//助教权限
						$query->where(['wus.coach_id' => $userId]);
						$userModel = WechatUser::where(['coach_id' => $userId])->column('userid');
					}else {//领导权限
						$query->where('wu2.department' , '<>', WechatDepartment::DEPARTMENT_HEAD_COACH);
						$userModel = WechatDepartmentUser::where(['departmentid' => WechatDepartment::DEPARTMENT_LONG_STUDENT])->whereOr(['departmentid' => WechatDepartment::DEPARTMENT_POTENTIAL_STUDENT])->column('userid');
					}
				}else{
                    $userModel = WechatDepartmentUser::where('departmentid',['>=',WechatDepartment::DEPARTMENT_HEAD_STUDENT],['<=',WechatDepartment::DEPARTMENT_POTENTIAL_STUDENT],'and')->column('userid');
				}
			}
		}else{
            $coachModel = WechatDepartmentUser::where(['departmentid' => WechatDepartment::DEPARTMENT_HEAD_COACH])->whereOr(['departmentid' => WechatDepartment::DEPARTMENT_ASSISTANT])->column('userid');
            $userModel = WechatDepartmentUser::where('departmentid',['>=',WechatDepartment::DEPARTMENT_HEAD_STUDENT],['<=',WechatDepartment::DEPARTMENT_POTENTIAL_STUDENT],'and')->column('userid');
		}
//		var_dump($query->fetchSql()->select());die;
		$modelAll = $query->select();
//		var_dump($modelAll);die;
//        var_dump($userModel);die;
		$all = [WechatUser::MEMBER_TYPE_COACH=>[],WechatUser::MEMBER_TYPE_STUDENT=>[]];
		$allUserId = [WechatUser::MEMBER_TYPE_COACH=>[],WechatUser::MEMBER_TYPE_STUDENT=>[]];
		$res = [WechatUser::MEMBER_TYPE_COACH=>[],WechatUser::MEMBER_TYPE_STUDENT=>[]];
		if($modelAll) {
			foreach ($modelAll as $model) {
				$all[$model['member_type']][$model['userid']]['day'][] = $model['date'];
				$all[$model['member_type']][$model['userid']]['coach_id'] = $model['coach_id'];
				$allUserId[$model['member_type']][] = $model['userid'];
				if ($model['status'] == WechatUserSign::STATUS_NORMAL) {//正常
					$res[$model['member_type']]['normal'][$model['userid']][] = $model['date'];
				}
				if ($model['status'] == WechatUserSign::STATUS_LATE) {//迟到
					$res[$model['member_type']]['late'][$model['userid']][] = $model['date'];
				}
			}
		}
		$dt_start = strtotime($start_time);
		if($dt_start < strtotime(date('2017-11-01'))){
			$dt_start = strtotime(date('2017-11-01'));
		}
		$dt_end = strtotime($end_time);
		$all_days = [];
		while ($dt_start<=$dt_end){
			if($dt_start <= strtotime(date('Y-m-d'))){
				$all_days[] = date('Y-m-d',$dt_start);
			}
			$dt_start = strtotime('+1 day',$dt_start);
		}
		//var_dump($all_days);die;
//		var_dump($allUserId);die;
//			var_dump($all);
//			var_dump($res);die;
		//教练情况
		if($coachModel){
			foreach($coachModel as $id){
				foreach($all_days as $day){
					if (in_array($id, $allUserId[WechatUser::MEMBER_TYPE_COACH])){
						foreach($all[WechatUser::MEMBER_TYPE_COACH] as $key => $val) {
							if ($id == $key) {
								if (!in_array($day, $val['day'])) {//所有的缺卡=休息
									$res[WechatUser::MEMBER_TYPE_COACH]['absence'][$id][] = $day;//缺卡
								}
							}
						}
					}else{
						$res[WechatUser::MEMBER_TYPE_COACH]['absence'][$id][] = $day;//缺卡
					}
				}
			}
		}
		//var_dump($res);die;
		//学员情况
		if($userModel){
			foreach($userModel as $id){//所有学员
				foreach($all_days as $day){//所有日期
					if (in_array($id, $allUserId[WechatUser::MEMBER_TYPE_STUDENT])){//所有日期中至少有一次签到记录
						foreach($all[WechatUser::MEMBER_TYPE_STUDENT] as $key => $val) {//签到的所有学员
							if ($id == $key) {//找到这个学员
								if (!in_array($day, $val['day'])) {//所有的缺卡
									$coachSignModel = WechatUserSign::where(['userid' => $val['coach_id'], "date" => $day])->find();
									if($coachSignModel){//教练这天有上课
										$res[WechatUser::MEMBER_TYPE_STUDENT]['absence'][$id][] = $day;//缺卡
									}
								}
							}
						}
					}else{//所有日期中都没有一次签到记录
						$coach_id = WechatUser::where(['userid' => $id])->value('coach_id');
						$coachSignModel = WechatUserSign::where(['userid' => $coach_id, "date" => $day])->find();
						if($coachSignModel){//教练这天有上课
							$res[WechatUser::MEMBER_TYPE_STUDENT]['absence'][$id][] = $day;//缺卡
						}
					}
				}
			}
		}
		//var_dump($res);die;
		$coach = [0,0,0];
		$student = [0,0,0];
		if($res[WechatUser::MEMBER_TYPE_COACH]){
			foreach($res[WechatUser::MEMBER_TYPE_COACH] as $k => $v){
				if($k == 'normal'){
					$coach[0] = count($v, 1)-count($v, 0);
				}
				if($k == 'late'){
					$coach[1] = count($v, 1)-count($v, 0);
				}
				if($k == 'absence'){
					$coach[2] = count($v, 1)-count($v, 0);
				}
			}
		}
		if($res[WechatUser::MEMBER_TYPE_STUDENT]){
			foreach($res[WechatUser::MEMBER_TYPE_STUDENT] as $k => $v){
				if($k == 'normal'){
					$student[0] = count($v, 1)-count($v, 0);
				}
				if($k == 'late'){
					$student[1] = count($v, 1)-count($v, 0);
				}
				if($k == 'absence'){
					$student[2] = count($v, 1)-count($v, 0);
				}
			}
		}
		if(IS_POST) {
			return json_encode([$coach,$student]);
		}else{
			//var_dump($coach);var_dump($student);die;
			$headCoachList = WechatUser::where(['department' => WechatDepartment::DEPARTMENT_HEAD_COACH])->column('userid, name');
			if($tag_id == WechatTag::TAG_LEADER) {//领导
				$assistantCoachList = WechatUser::where(['department' => WechatDepartment::DEPARTMENT_ASSISTANT])->column('userid, name');
			}else{//主教练
				$assistantCoachList = WechatUser::where(['coach_id' => $userId])->column('userid, name');
			}
			$this->assign('tag_id',$tag_id);
			$this->assign('headCoachList',$headCoachList);
			$this->assign('assistantCoachList',$assistantCoachList);
			$this->assign('coach',json_encode($coach));
			$this->assign('student',json_encode($student));
			return $this->fetch();
		}
	}

}