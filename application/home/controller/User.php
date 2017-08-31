<?php


namespace app\home\controller;
use app\home\model\WechatDepartment;
use app\home\model\WechatUser;
use app\home\model\WechatTag;
use app\home\model\WechatUserTag;

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
     * member_type: 2
	 */
	public function student () {
        $userid = input('did', session('userId'));
        $res = WechatUser::where('userid',$userid)->find();
        ($res['gender'] == 1) ? $res['gender_text'] = "男" : $res['gender_text'] = "女";
        $res['birthday_year'] = $res['identity'] ? substr($res['identity'], 6, 4) : null;
        $res['birthday_month'] = $res['identity'] ? substr($res['identity'], 10, 2) : null;
        if($userid != session('userId')){
            $res['edit_button'] = 1;
        }
        $this->assign('res',$res);
		return $this->fetch();
	}
	/**
	 * 个人信息(教练)
     * member_type: 1
	 */
	public function tutor () {
        $userid = input('did', session('userId'));
        $res = WechatUser::where('userid',$userid)->find();
        $tag_id = WechatUserTag::where(['userid' => $userid])->value('tagid');
        ($res['gender'] == 1) ? $res['gender_text'] = "男" : $res['gender_text'] = "女";
        if($userid != session('userId')){
            $res['edit_button'] = 1;
        }
        if($tag_id == WechatTag::TAG_HEAD_COACH){
            $res['tag'] = 1;
        }
        $this->assign('res',$res);
		return $this->fetch();
	}
	/**
	 * 个人信息编辑(教练)
	 */
	public function tutoredit () {
        if(IS_POST) {
            $data = input('post.');
            $res = WechatUser::where('id',$data['id'])->update($data);
            if($res) {
                return $this->success("修改成功");
            }else{
                return $this->error("修改失败");
            }
        }else {
            $id = input('id');
            $res = WechatUser::get($id);
            $this->assign('res',$res);
            return $this->fetch();
        }
	}

	/**
	 * 个人信息编辑(学员)
	 */
	public function studentedit () {
        if(IS_POST) {
            $data = input('post.');
            $res = WechatUser::where('id',$data['id'])->update($data);
            if($res) {
                return $this->success("修改成功");
            }else{
                return $this->error("修改失败");
            }
        }else {
            $id = input('id');
            $res = WechatUser::get($id);
            $this->assign('res',$res);
            return $this->fetch();
        }
	}
	/**
	 * 我的收藏
	 */
	public function myCollect(){
		return $this->fetch();
	}
}