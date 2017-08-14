<?php


namespace app\home\controller;
use app\home\model\WechatDepartment;
use app\home\model\WechatUser;

/**
 * 个人中心
 */
class User extends Base{
    /**
     * 首页
     */
    public function index() {
        $userid = session('userId');
        if(IS_POST) {
            $data = array(
                'header' => input('header')
            );
            $res = WechatUser::where('userid',$userid)->update($data);
            if($res) {
                return $this->success("头像修改成功");
            }else {
                return $this->error("修改失败");
            }
        }else {
            $user = WechatUser::where('userid',$userid)->find();
            $this->assign('user',$user);
            return $this->fetch();
        }
    }

    /**
     * 个人信息
     */
    public function personal() {
        $userid = session('userId');
        $res = WechatUser::where('userid',$userid)->find();
        switch ($res['gender']) {
            case 0:
                $res['gender_text'] = "未定义";
                break;
            case 1:
                $res['gender_text'] = "男";
                break;
            case 2:
                $res['gender_text'] = "女";
                break;
            default:
                break;
        }
        $this->assign('res',$res);
        return $this->fetch();
    }
	/**
	 * 个人信息(学员)
	 */
	public function student () {
		return $this->fetch();
	}
	/**
	 * 个人信息(教练)
	 */
	public function tutor () {
		return $this->fetch();
	}
	/**
	 * 个人信息编辑(教练)
	 */
	public function tutoredit () {
		return $this->fetch();
	}

	/**
	 * 个人信息编辑(学员)
	 */
	public function studentedit () {
		return $this->fetch();
	}

}