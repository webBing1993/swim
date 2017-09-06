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
use think\Db;
/**
 * 签到概况
 */
class Statistics extends Base{
	/**
	 * 首页
	 */
	public function index() {
		$start_time = input('start_time', date('Y-08-01'));
		$end_time = input('end_time', date('Y-m-d'));
		$coach_did = input('coach_did', 0);
		$student_did = input('student_did', 0);
		if(IS_POST) {
			/*$data = input('post.');
			$res = WechatUser::where('id',$data['id'])->update($data);
			if($res) {
				return $this->success("修改成功");
			}else{
				return $this->error("修改失败");
			}*/
		} else {
			$query = Db::field('wus.*, wu.member_type, wu.coach_id, wu.department, wu2.department coach_department')				->table('sw_wechat_user_sign wus')
					->join('sw_wechat_user wu','wus.userid = wu.userid')
					->join('sw_wechat_user wu2','wu.coach_id = wu2.userid')
					->where('date',['>=',$start_time],['<=',$end_time],'and');
			//$modelAll = WechatUserSign::where('date',['>=',$start_time],['<=',$end_time],'and')->select();
			//var_dump($modelAll);die;
			if($coach_did){
				$query->where(['departmentid' => $coach_did]);
			}else{
				$query->where(['member_type' => WechatUser::MEMBER_TYPE_COACH]);
				//$query->where('departmentid',['=',WechatDepartment::DEPARTMENT_HEAD_COACH],['=',WechatDepartment::DEPARTMENT_ASSISTANT],'or');
			}
			//var_dump($query->fetchSql()->select());die;
			$modelAll = $query->select();
			if($modelAll) {
				foreach ($modelAll as $model) {
					$all_days[$model['userid']][] = $model['date'];
					if ($model['status'] == WechatUserSign::STATUS_NORMAL) {//正常
						$res['normal'][$model['userid']][] = $model['date'];
					}
					if ($model['status'] == WechatUserSign::STATUS_LATE) {//迟到
						$res['late'][$model['userid']][] = $model['date'];
					}
				}
			}
//			var_dump($all_days);
//			var_dump($res);die;

			return $this->fetch();
		}
	}

}