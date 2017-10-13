<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/27
 * Time: 9:14
 */
namespace app\admin\controller;
use app\admin\model\UserClass as UserClassModel;
use app\admin\model\WechatDepartment;
use app\admin\model\WechatDepartmentUser;
use app\admin\model\WechatUser;
use app\admin\model\WechatTag;
use app\admin\model\WechatUserTag;
use com\wechat\QYWechat;
use org\Page;
use think\Collection;
use think\Config;

/**
 * 后台用户控制器
 */
class Userclass extends Admin
{
    /**
     * 班级列表
     */
    public function index(){
        $list = $this->lists('UserClass',[],'id');
        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 修改
     */
    public function edit(){
        if(IS_POST) {
            $data = input('post.');
            $userClassModel = new UserClassModel();
            $model = $userClassModel->save($data,['id'=> input('id')]);
            if($model) {
                return $this->success('修改成功',Url('index'));
            }else{
                return $this->get_update_error_msg($userClassModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            if(empty($id)){
                return $this->error();
            }else{
                $msg = UserClassModel::get($id);
                $this->assign('msg',$msg);
            }
            return $this->fetch();
        }
    }

    /**
     * 主教练列表
     */
    public function headcoach(){
        $list = WechatUser::where(['department' => WechatDepartment::DEPARTMENT_HEAD_COACH])->select();
        foreach($list as $key => $model){
            $userClassModel = UserClassModel::where(['id' => $model['class_id']])->find();
            $list[$key]['class'] = $userClassModel['start_time'].' - '.$userClassModel['end_time'];
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 学员列表
     */
    public function user(){
        $did = input('did');
        $name = WechatUser::where(['userid' => $did])->value('name');
        $list = WechatUser::where(['coach_id' => $did])->order('department desc,class_id')->select();
        foreach($list as $key => $model){
            $userClassModel = UserClassModel::where(['id' => $model['class_id']])->find();
            $list[$key]['class'] = $userClassModel['start_time'].' - '.$userClassModel['end_time'];
        }
        $this->assign('name',$name);
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 学员列表
     */
    public function student(){
        $did = input('did');
        $name = WechatUser::where(['userid' => $did])->value('name');
        $list = WechatUser::where(['coach_id' => $did])->order('class_id')->select();
        foreach($list as $key => $model){
            $userClassModel = UserClassModel::where(['id' => $model['class_id']])->find();
            $list[$key]['class'] = $userClassModel['start_time'].' - '.$userClassModel['end_time'];
        }
        $this->assign('name',$name);
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 	修改上课时间
     */
    public function editclass(){
        if(IS_POST) {
            $data = input('post.');
            $msg = WechatUser::get(input('id'));
            $param = array(
                'userid' => $msg['userid'],
                'extattr' => ['attrs' => array(
                    ["name" => "所属班级", "value" => $data['class_id']],
                )]
            );
            if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH) {//学员
                if($msg['coach_id']!=$data['coach_id']){
                    $param['extattr']['attrs'][] = ["name" => "原教练", "value" => $msg['now_coach']];
                    $param['extattr']['attrs'][] = ["name" => "现教练", "value" => $data['now_coach']];
                    $data['ever_coach'] = $msg['now_coach'];
                }
                if($msg['department']!=$data['department']){
                    $param['department'] = [$data['department']];
                    $param['position'] = $data['position'];
                }
            }else{
                if($msg['department'] == WechatDepartment::DEPARTMENT_ASSISTANT) {//助教
                    if($msg['coach_id']!=$data['coach_id']){
                        $param['extattr']['attrs'][] = ["name" => "上级教练", "value" => $data['now_coach']];
                    }
                }
            }
            $Wechat = new QYWechat(Config::get('mail'));
            $rs = $Wechat->updateUser($param);
            if($rs){
                $userModel = new WechatUser();
                $model = $userModel->save($data,['id'=> input('id')]);
                if($model) {
                    if($msg['member_type'] != WechatUser::MEMBER_TYPE_COACH){//学员
                        if($msg['department']!=$data['department']){
                            WechatDepartmentUser::where(['userid' => $msg['userid']])->update(['departmentid' => $data['department']]);
                        }
                    }
                    return $this->success('修改成功');
                }else{
                    return $this->get_update_error_msg($userModel->getError());
                }
            }else{
                return $this->error("企业号通讯录同步未开启，请联系管理员打开");
            }
        }else{
            $id = input('id');
            if(empty($id)){
                return $this->error();
            }else{
                $msg = WechatUser::get($id);
                if($msg['department'] == WechatDepartment::DEPARTMENT_ASSISTANT){//助教
                    $coach = WechatUser::where(['department' => WechatDepartment::DEPARTMENT_HEAD_COACH])->field('department,userid,name')->select();
                }else{
                    $coach = WechatUser::where(['member_type' => WechatUser::MEMBER_TYPE_COACH])->field('department,userid,name')->select();
                }
                if($coach){
                    $collection = new Collection($coach);
                    $coach = $collection->toArray();
                }else{
                    $coach = array();
                }
                $list = $this->lists('UserClass',[],'id');
                $class = [];
                foreach($list as $key => $model){
                    $class[$key]['id'] = $model['id'];
                    $class[$key]['name'] = $model['start_time'].' - '.$model['end_time'];
                }
                $this->assign('coach',$coach);
                $this->assign('class',$class);
                $this->assign('msg',$msg);
            }
            return $this->fetch();
        }
    }

    /**
     * 	改教练
     */
    public function changecoach(){
        $coach_id = input('coach_id');
        $msg = WechatUser::where(['userid' => $coach_id])->find();
        if($msg['department'] == WechatDepartment::DEPARTMENT_ASSISTANT){
            return false;
        }else{
            return true;
        }
    }


}