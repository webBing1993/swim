<?php
/**
 * File: Sign.php
 * User: Administrator
 * Date: 2017-08-15 9:44
 */
namespace app\home\controller;
use app\home\model\WechatDepartment;
use app\home\model\UserClass;
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

		/*$did = input('get.did');
		$dp3 = WechatDepartment::get($did);
		$dp2 = WechatDepartment::get($dp3['parentid']);
		$dp1 = WechatDepartment::get($dp2['parentid']);
		$data = array(
				'dp1_id' => $dp1['id'],
				'dp1_name' => $dp1['name'],
				'dp2_id' => $dp2['id'],
				'dp2_name' => $dp2['name'],
				'dp3_id' => $dp3['id'],
				'dp3_name' => $dp3['name'],
		);
		$this->assign('depart',$data);

		$list = Db::table('sw_wechat_department_user')->where('departmentid',$did)->order(['order'=>'desc','id'])->field('userid')->select();  // 获取 用户列表
		foreach($list as $key => $value){
			$User = Db::table('sw_wechat_user')->where('userid',$value['userid'])->field('id,header,name,avatar')->find();
			$list[$key]['name'] = $User['name'];
			$list[$key]['id'] = $User['id'];
			if (empty($User['header'])){   //  头像
				if (empty($User['avatar'])){
					$list[$key]['header'] = '';
				}else{
					$list[$key]['header'] = $User['avatar'];
				}
			}else{
				$list[$key]['header'] = $User['header'];
			}
		}
		$this->assign('list',$list);
		dump($list);
		$this->assign('did',$did);*/
		return $this->fetch();
	}
	/**
	 * 我的签到
	 */
	public function mysign() {

		return $this->fetch();
	}
}