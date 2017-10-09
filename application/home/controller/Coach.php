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
use app\home\model\ClassPlan;
use app\home\model\ClassScore;
use app\home\model\ClassContent;
use app\home\model\ClassTrain;
use app\home\model\WeekContent;
use app\home\model\WeekTrain;
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
		$userId = input('did', session('userId'));
		$year = input('year', date('Y'));
		$month = input('month', date('n'));
		$month = $month<10 ? '0'.intval($month) : $month;
		$res = array('normal' => [], 'late' => [], 'rest' => []);
		if($year.$month > date("Ym")){
			return json_encode($res);
		}
		if($year.$month < date("201710")){
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
		if($year.$month == date("201710")){
			for ($i=9; $i<=$days; $i++) {//当天不计算缺卡
				$i = $i<10 ? '0'.$i : $i;
				if(!in_array($year.'-'.$month.'-'.$i, $all_days)){//所有的缺卡
					$res['rest'][] = intval($i);//缺卡
				}
			}
		}else{
			for ($i=1; $i<=$days; $i++) {//当天不计算缺卡
				$i = $i<10 ? '0'.$i : $i;
				if(!in_array($year.'-'.$month.'-'.$i, $all_days)){//所有的缺卡
					$res['rest'][] = intval($i);//缺卡
				}
			}
		}

		if(IS_POST) {
			return json_encode($res);
		}else{
			$this->assign('did',$userId);
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
	public function weekplan(){
		$userId = input('did', session('userId'));
		$id = input('id');
		$time = input('time', date('Y年m月'));
		$res = [];
		if($id){//每周总结
			$res = WeekSummary::getList($userId, $time);
		}else{//每周计划
			$res = WeekPlan::getList($userId, $time);
		}
		if(IS_POST) {
			return json_encode($res);
		}else{
			$edit_button = 0;
			if($userId != session('userId')){
				$edit_button = 1;
			}
			$this->assign('id',$id);
			$this->assign('edit_button',$edit_button);
			$this->assign('res',$res);
			return $this->fetch();
		}
	}
	/**
	 * 课时计划列表
	 */
	public function classplan(){
		$userId = input('did', session('userId'));
		$time = input('time', date('Y年m月'));
		$res = [];
		$res = ClassPlan::getList($userId, $time);
		//var_dump($res);die;
		if(IS_POST) {
			return json_encode($res);
		}else{
			$edit_button = 0;
			if($userId != session('userId')){
				$edit_button = 1;
			}
			$this->assign('edit_button',$edit_button);
			$this->assign('res',$res);
			return $this->fetch();
		}
	}
	/**
	 * 每周计划发布/编辑
	 */
	public function pweekplan(){
		if(IS_POST) {
			$data = input('post.');
			//var_dump($data);die;
			$weekPlanModel = new WeekPlan();
			$data['start'] = strtotime($data['start']);
			$data['end'] = strtotime($data['end']);
			$days = isset($data['days']) ? $data['days'] : null;
			unset($data['days']);
			if(empty($data['id'])) {//新增
				$data['userid'] = session('userId');
				unset($data['id']);
				$info = $weekPlanModel->save($data);
			}else{//修改
				$info = $weekPlanModel->save($data,['id'=>$data['id']]);
			}
			//成功||未修改内容
			if($info || empty($weekPlanModel->getError())) {
				//周计划内容
				$train_id_old = WeekContent::where(['pid' => $weekPlanModel->id])->field('train_id')->select();
				foreach ($train_id_old as $old_id) {
					WeekTrain::where('id', 'in', json_decode($old_id['train_id'], true))->delete();
				}
				WeekContent::where(['pid' => $weekPlanModel->id])->delete();
				$plan_id = [];
				if($days) {
					foreach ($days as $key => $val) {
						if($val){
							$msg1 = ['type' => $key + 1, 'pid' => $weekPlanModel->id];
							$insert_content = ['type' => $key + 1, 'contentself' => $val['contentself'], 'load' => $val['load'], 'duration' => $val['duration'], 'pid' => $weekPlanModel->id];
							if (empty(WeekContent::where($msg1)->find())) {
								$weekContentModel = new WeekContent();
								$save = $weekContentModel->save($insert_content);
								$plan_id[] = $weekContentModel->id;
								if ($save) {
									$train_id = [];
									if(isset($val['content'])) {
										foreach ($val['content'] as $k => $v) {
											//$msg2 = ['pose' => $v[3], 'pid' => $weekContentModel->id];
											$insert_train = ['group' => $v[0], 'num' => $v[1], 'distance' => $v[2], 'pose' => $v[3], 'detail' => $v[4], 'order' => $k + 1, 'pid' => $weekContentModel->id];
											//if (empty(WeekTrain::where($msg2)->find())) {
											$weekTrainModel = new WeekTrain();
											$a=$weekTrainModel->save($insert_train);
											$train_id[] = $weekTrainModel->id;
											//}
										}
									}
									if ($train_id) {
										$weekContentModel->save(['train_id' => json_encode($train_id)], ['id' => $weekContentModel->id]);
									}
								}
							}
						}
					}
				}
				if($plan_id){
					$weekPlanModel->save(['plan_id' => json_encode($plan_id)],['id'=> $weekPlanModel->id]);
				}
				return $this->success("保存成功",Url('weekPlan'));
			}else{
				return $this->error($weekPlanModel->getError());
			}
		}else{
			$id = input('id');
			$res = [];
			$contents = [];
			if($id){
				$res = WeekPlan::getModelById($id);
				$contents = $res['contents'];
			}
			//var_dump($res);die;
			$this->assign('contents',json_encode($contents));
			$this->assign('res', $res);
			return $this->fetch();
		}
	}
	/**
	 * 课时计划发布/编辑
	 */
	public function pclassplan(){
		if(IS_POST) {
			$data = input('post.');
			//var_dump($data);die;
			$classPlanModel = new ClassPlan();
			$data['start'] = strtotime($data['start']);
			$mark = isset($data['mark']) ? $data['mark'] : null;
			$parts = isset($data['parts']) ? $data['parts'] : null;
			unset($data['mark']);
			unset($data['parts']);
			if(empty($data['id'])) {//新增
				$data['userid'] = session('userId');
				unset($data['id']);
				$info = $classPlanModel->save($data);
			}else{//修改
				$info = $classPlanModel->save($data,['id'=>$data['id']]);
			}
			//成功||未修改内容
			if($info || empty($classPlanModel->getError())) {
				//课时计划成绩
				ClassScore::where(['pid' => $classPlanModel->id])->delete();
				$score_id = [];
				if($mark){
					foreach($mark as $key => $val){
						$msg = ['userid'=>$val['id'], 'pid'=>$classPlanModel->id];
						$insert = ['userid'=>$val['id'], 'name'=>$val['name'], 'score'=>$val['time'], 'good'=>$val['good'], 'order'=>$key+1, 'pid'=>$classPlanModel->id];
						if(empty(ClassScore::where($msg)->find())) {
							$classScoreModel = new ClassScore();
							$classScoreModel->save($insert);
							$score_id[] = $classScoreModel->id;
						}
					}
				}

				if($score_id){
					$classPlanModel->save(['score_id' => json_encode($score_id)],['id'=> $classPlanModel->id]);
				}

				//课时计划内容
				$train_id_old = ClassContent::where(['pid' => $classPlanModel->id])->field('train_id')->select();
				foreach ($train_id_old as $old_id) {
					ClassTrain::where('id', 'in', json_decode($old_id['train_id'], true))->delete();
				}
				ClassContent::where(['pid' => $classPlanModel->id])->delete();
				$plan_id = [];
				if($parts) {
					foreach ($parts as $key => $val) {
						if($val){
							$msg1 = ['type' => $key + 1, 'pid' => $classPlanModel->id];
							$insert_content = ['type' => $key + 1, 'contentself' => $val['contentself'], 'load' => $val['load'], 'strength' => $val['strength'], 'duration' => $val['duration'], 'pid' => $classPlanModel->id];
							if (empty(ClassContent::where($msg1)->find())) {
								$classContentModel = new ClassContent();
								$save = $classContentModel->save($insert_content);
								$plan_id[] = $classContentModel->id;
								if ($save) {
									$train_id = [];
									if(isset($val['content'])) {
										foreach ($val['content'] as $k => $v) {
											//$msg2 = ['pose' => $v[3], 'pid' => $classContentModel->id];
											$insert_train = ['group' => $v[0], 'num' => $v[1], 'distance' => $v[2], 'pose' => $v[3], 'detail' => $v[4], 'order' => $k + 1, 'pid' => $classContentModel->id];
											//if (empty(ClassTrain::where($msg2)->find())) {
											$classTrainModel = new ClassTrain();
											$classTrainModel->save($insert_train);
											$train_id[] = $classTrainModel->id;
											//}
										}
									}
									if ($train_id) {
										$classContentModel->save(['train_id' => json_encode($train_id)], ['id' => $classContentModel->id]);
									}
								}
							}
						}
					}
				}
				if($plan_id){
					$classPlanModel->save(['plan_id' => json_encode($plan_id)],['id'=> $classPlanModel->id]);
				}
				return $this->success("保存成功",Url('weekPlan'));
			}else{
				return $this->error($classPlanModel->getError());
			}
		}else{
			$id = input('id');
			$res = [];
			$contents = [];
			$userId = session('userId');
			$score = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->order("name")->column('name','userid');
			if($id){
				$res = ClassPlan::getModelById($id);
				$contents = $res['contents'];
				$score_users = [];
				foreach($res['score'] as $model) {
					$score_users[] = $model['userid'];
				}
				foreach($score as $userid => $name) {
					if(!in_array($userid, $score_users)) {
						$res['score'][$userid]['userid'] = $userid;
						$res['score'][$userid]['name'] = $name;
						$res['score'][$userid]['score'] = '';
					}
				}
				$res['score'] = array_values($res['score']);
			}else{
				$this->assign('score', $score);
			}
			//var_dump($res);die;
			$this->assign('contents',json_encode($contents));
			$this->assign('res', $res);
			return $this->fetch();
		}
	}
	/**
	 * 每周总结发布/编辑
	 */
	public function pweeksummary(){
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
	public function dweekplan(){
		$id = input('id');
		$info['views'] = array('exp','`views`+1');
		WeekPlan::where('id',$id)->update($info);
		$res = WeekPlan::getModelById($id);
		$this->assign('contents',json_encode($res['contents']));
		$this->assign('res',$res);
		return $this->fetch();
	}
	/**
	 * 课时计划 详情
	 */
	public function dclassplan(){
		$id = input('id');
		$info['views'] = array('exp','`views`+1');
		ClassPlan::where('id',$id)->update($info);
		$res = ClassPlan::getModelById($id);
		$userId = session('userId');
		$score = WechatUser::where(['coach_id' => $userId, 'member_type' => WechatUser::MEMBER_TYPE_STUDENT])->order("name")->column('name','userid');
		$score_users = [];
		foreach($res['score'] as $model) {
			$score_users[] = $model['userid'];
		}
		foreach($score as $userid => $name) {
			if(!in_array($userid, $score_users)) {
				$res['score'][$userid]['userid'] = $userid;
				$res['score'][$userid]['name'] = $name;
				$res['score'][$userid]['score'] = '';
			}
		}
		$res['score'] = array_values($res['score']);
		$this->assign('contents',json_encode($res['contents']));
		$this->assign('res',$res);
		return $this->fetch();
	}
	/**
	 * 每周总结 详情
	 */
	public function dweeksummary(){
		$id = input('id');
		$info['views'] = array('exp','`views`+1');
		WeekSummary::where('id',$id)->update($info);
		$res = WeekSummary::getModelById($id);
		//var_dump($res);die;
		$this->assign('res',$res);
		return $this->fetch();
	}
	/**
	 * 删除
	 */
	public function del(){
		$type = input('type');
		$id = input('id');
		if($type == 0){
			$Model = new ClassPlan();
		}elseif($type == 1){
			$Model = new WeekPlan();
		}elseif($type == 2){
			$Model = new WeekSummary();
		}else{
			return $this->error("参数错误");
		}
		$data['status'] = '-1';
		$info = $Model->where('id',$id)->update($data);
		if($info) {
			return $this->success("删除成功");
		}else{
			return $this->error("删除失败");
		}
	}
}