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
use think\Db;
/**
 * 学员频道
 */
class Student extends Base{
	/**
	 * 首页
	 */
	public function index() {
		$userId = session('userId');
		$userModel = WechatUser::where(['userid' => $userId])->find();
		$coach_id = $userModel['coach_id'];
		$coachModel = WechatUser::where(['userid' => $coach_id])->find();
		$age = $coachModel['identity'] ? date("Y")-substr($coachModel['identity'], 6, 4)+1 : '_ ';
		$tag_id = WechatUserTag::where(['userid' => $coach_id])->value('tagid');
		$tag_name = WechatTag::where(['tagid' => $tag_id])->value('tagname');
		$this->assign('userModel',$userModel);
		$this->assign('coachModel',$coachModel);
		$this->assign('age',$age);
		$this->assign('tag_name',$tag_name);
		return $this->fetch();
	}
	/**
	 * 我的签到
	 */
	public function mysign() {
		$userId = session('userId');
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
			$this->assign('normal',json_encode($res['normal']));
			$this->assign('late',json_encode($res['late']));
			$this->assign('absence',json_encode($res['absence']));
			return $this->fetch();
		}
	}
	/**
	 * 个人签到条形码
	 */
	public function code(){
		$userId = session('userId');
		$res = WechatUser::where(['userid' => $userId])->field('mobile, header, avatar')->find()->toArray();
		$data['mobile'] = $userId;
		if(empty($res['header'])){
			$data['header'] = param_to($res['avatar'], \think\Config::get('de_header'));
		}else{
			$data['header'] = get_cover($res['header'], 'path');
		}
		$this->assign('data',$data);
		return $this->fetch();
	}

}