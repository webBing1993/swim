<?php


namespace app\home\controller;
use app\home\model\WechatDepartment;
use app\home\model\WechatUser;
use app\home\model\WechatTag;
use app\home\model\WechatUserTag;
use app\home\model\Collect;
use app\home\model\News as NewsModel;
use app\home\model\Notice as NoticeModel;
use app\home\model\CertificateReview;
use com\wechat\QYWechat;
use think\Config;
use think\Db;
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
            if($user['member_type'] == 2){
                $showflag = 1;
            }else{
                $showflag = 0;
            }
            $this->assign('showflag',$showflag);
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
        $res['birthday_year'] = !empty($res['identity']) ? substr($res['identity'], 6, 4) : null;
        $res['birthday_month'] = !empty($res['identity']) ? substr($res['identity'], 10, 2) : null;
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
            $model = WechatUser::where(['id' => $data['id']])->find();
            $param = array(
                'userid' => $model['userid'],
                'name' => $data['name'],
                //'mobile' => $data['mobile'],
                'gender' => $data['gender'],
                'extattr' => ['attrs' => array(
                    ["name" => "学历", "value" => $data['education']],
                )]
            );
            if(!empty($data['identity'])){
                $param['extattr']['attrs'][] = ["name" => "身份证号", "value" => $data['identity']];
            }
            if(!empty($data['technical_title'])){
                $param['extattr']['attrs'][] = ["name" => "技术职称", "value" => $data['technical_title']];
            }
            $Wechat = new QYWechat(Config::get('mail'));
            $rs = $Wechat->updateUser($param);
            if($rs){
                $res = WechatUser::where('id',$data['id'])->update($data);
                if($res) {
                    $data['social_certificate'] = isset($data['social_certificate'])?$data['social_certificate']:'';
                    $data['profession_certificate'] = isset($data['profession_certificate'])?$data['profession_certificate']:'';
                    $data['lifeguard_certificate'] = isset($data['lifeguard_certificate'])?$data['lifeguard_certificate']:'';
                    if($model['social_certificate'] != $data['social_certificate'] || $model['profession_certificate'] != $data['profession_certificate'] || $model['lifeguard_certificate'] != $data['lifeguard_certificate']){
                        $info = array(
                            'type' => CertificateReview::TYPE_CERTIFICATE,
                            'userid' => $model['userid'],
                            'name' => $data['name'],
                            'front_cover' => $data['social_certificate'],
                            'title' => $data['name'],
                            'social_certificate' => $data['social_certificate'],
                            'profession_certificate' => $data['profession_certificate'],
                            'lifeguard_certificate' => $data['lifeguard_certificate'],
                        );
                        CertificateReview::create($info);
                        WechatUser::where('id',$data['id'])->update(['certificate_status'=>0]);
                    }
                    return $this->success("修改成功");
                }else{
                    return $this->error("修改失败");
                }
            }else{
                return $this->error("企业号通讯录同步未开启，请联系管理员打开");
            }
        }else {
            $id = input('id');
            $res = WechatUser::get($id);
            $tag_id = WechatUserTag::where(['userid' => $res['userid']])->value('tagid');
            if($tag_id == WechatTag::TAG_HEAD_COACH){
                $res['tag'] = 1;
            }
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
            $model = WechatUser::where('id',$data['id'])->find();
            $param = array(
                'userid' => $model['userid'],
                'name' => $data['name'],
                //'mobile' => $data['mobile'],
                'gender' => $data['gender'],
                'extattr' => ['attrs' => array(
                    ["name" => "出生年月", "value" => $data['birthday']],
                    ["name" => "监护人", "value" => $data['guardian']],
                    ["name" => "备用电话", "value" => $data['emergency_call']],
                    ["name" => "身高", "value" => $data['height']],
                    ["name" => "体重", "value" => $data['weight']],
                    ["name" => "原教练", "value" => $data['ever_coach']],
                    ["name" => "家庭地址", "value" => $data['address']],
                    ["name" => "学历", "value" => $data['education']],
                    ["name" => "就读学校", "value" => $data['school']],
                    ["name" => "学籍号", "value" => $data['student_code']],
                    ["name" => "文化成绩", "value" => $data['achievement']],
                )]
            );
            $Wechat = new QYWechat(Config::get('mail'));
            $rs = $Wechat->updateUser($param);
            if($rs){
                $res = WechatUser::where('id',$data['id'])->update($data);
                if($res) {
                    return $this->success("修改成功");
                }else{
                    return $this->error("修改失败");
                }
            }else{
                return $this->error("企业号通讯录同步未开启，请联系管理员打开");
            }
        }else {
            $id = input('id');
            $res = WechatUser::get($id);
            $res['birthday_year'] = !empty($res['identity']) ? substr($res['identity'], 6, 4) : null;
            $res['birthday_month'] = !empty($res['identity']) ? substr($res['identity'], 10, 2) : null;
            $this->assign('res',$res);
            return $this->fetch();
        }
	}
    /**
     * 个人信息编辑(学员)
     */
    public function setHeader () {
        if(IS_POST) {
            $data = input('post.');
            $res = WechatUser::where('id',$data['id'])->update($data);
            //var_dump($data);die;
            if($res){
                return $this->success("修改成功");
            }else{
                return $this->error("修改失败");
            }
        }
    }
	/**
	 * 我的收藏
	 */
	public function myCollect(){
        $userId = session('userId');
        $order = array("create_time desc");
        $collectModelAll = Collect::where(['uid' => $userId])->order($order)->select();
        $res = [];
        foreach ($collectModelAll as $model) {
            $res[] = Db::name($model['table'])->where(['id' => $model['aid']])->field('id,title,create_time,'.$model['type'].' as tab')->find();
        }
        $this->assign('res',$res);
		return $this->fetch();
	}
    /**
     * 获取更多数据
     */
    public function listMore(){
        $data = input('post.');
        $Model = $data['tab'] ? new NoticeModel() : new NewsModel();
        $list = $Model->getMoreList($data);
        if($list){
            return $this->success("加载成功",'',$list);
        }else{
            return $this->error("加载失败");
        }
    }
    // 暂无权限
    public function null(){
        return $this->fetch();
    }
}