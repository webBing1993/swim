<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/8
 * Time: 10:29
 */

namespace app\admin\controller;

use app\admin\model\Apply;
use app\admin\model\WechatTag;
use app\admin\model\Notice as NoticeModel;
use app\admin\model\NoticeEnroll;
use app\admin\model\Push;
use app\admin\model\Picture;
use app\admin\model\PushReview;
use app\admin\model\WechatDepartment;
use app\admin\model\WechatUser;
use app\admin\model\NoticeTag;
use app\admin\model\WechatDepartmentUser;
use com\wechat\TPQYWechat;
use think\Config;

/**
 * Class Notice
 * @package 游泳协会活动通知
 */
class Notice extends Admin {
    /**
     * 相关通知
     * type: 1
     */
    public function index(){
        $map = array(
            'type' => 1,
            'status' => array('egt',0),
        );
        $list = $this->lists('Notice',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
        ));

        $this->assign('list',$list);
        return $this->fetch();
    }

    /**
     * 相关通知添加
     */
    public function add(){
        if(IS_POST) {
            $data = input('post.');
            $noticeModel = new NoticeModel();
            if(empty($data['id'])) {
                unset($data['id']);//为空则添加,将变量id释放
            }
            $data['tag'] = $data['tag'] ? json_encode($data['tag']) : null;
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if($data['recommend'] == 1) $data['status'] = 0;
            $model = $noticeModel->validate('Notice.other')->save($data);
            if($model) {
                //NoticeTag::where(['pid' => $noticeModel->id])->delete();
                foreach(json_decode($data['tag'], true) as $tagid){
                    $msg = ['tagid'=>$tagid, 'pid'=>$noticeModel->id];
                    if(empty(NoticeTag::where($msg)->find())) {
                        NoticeTag::create($msg);
                    }
                }
                return $this->success('新增相关通知成功',Url('Notice/index'));
            }else{
                return $this->error($noticeModel->getError());
            }
        }else{
            $this->default_pic();
            $tag = WechatTag::all();
            $this->assign('tag',$tag);
            $this->assign('msg',null);
            return $this->fetch('edit');
        }
    }

    /**
     * 相关通知修改
     */
    public function edit(){
        if(IS_POST) {
            $data = input('post.');
            $noticeModel = new NoticeModel();
            $data['tag'] = $data['tag'] ? json_encode($data['tag']) : null;
            $model = $noticeModel->validate('Notice.other')->save($data,['id'=> input('id')]);
            if($model) {
                NoticeTag::where(['pid' => $noticeModel->id])->delete();
                foreach(json_decode($data['tag'], true) as $tagid){
                    $msg = ['tagid'=>$tagid, 'pid'=>$noticeModel->id];
                    if(empty(NoticeTag::where($msg)->find())) {
                        NoticeTag::create($msg);
                    }
                }
                return $this->success('修改相关通知成功',Url('Notice/index'));
            }else{
                return $this->get_update_error_msg($noticeModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = NoticeModel::get($id);
            $tag = WechatTag::all();
            $msg['tag'] = NoticeTag::where(['pid'=>$id])->column('tagid');
            $this->assign('tag',$tag);
            $this->assign('msg',$msg);
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function del(){
        $id = input('id');
        $map['status'] = "-1";
        $info = NoticeModel::where('id',$id)->update($map);
        if($info) {
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }
    }

    /*
     * 活动情况
     * type: 2
     */
    public function activity(){
        $map = array(
            'type' => 2,
            'status' => array('egt',0),
        );
        $list = $this->lists('Notice',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
        ));

        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 活动情况添加
     */
    public function activityadd(){
        if(IS_POST) {
            $data = input('post.');
            if(empty($data['id'])) {
                unset($data['id']);
            }
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            $noticeModel = new NoticeModel();
            if (!empty($data['start_time']) && !empty($data['end_time'])){
                $data['start_time'] = strtotime($data['start_time']);
                $data['end_time'] = strtotime($data['end_time']);
            }
            $id = $noticeModel->validate('Notice.act')->save($data);
            if($id){
                return $this->success("新增相关活动成功",Url('Notice/activity'));
            }else{
                return $this->error($noticeModel->getError());
            }
        }else {
            $this->default_pic();
            $this->assign('msg','');
            return $this->fetch("activityedit");
        }
    }

    /**
     * 活动情况修改
     */
    public function activityedit(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            $noticeModel = new NoticeModel();
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $id = $noticeModel->validate('Notice.act')->save($data,['id'=>input('id')]);
            if($id){
                return $this->success("修改相关活动成功",Url('Notice/activity'));
            }else{
                return $this->error($noticeModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = NoticeModel::get($id);
            $this->assign('msg',$msg);
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function activitydel(){
        $id = input('id');
        $map['status'] = "-1";
        $info = NoticeModel::where('id',$id)->update($map);
        if($info) {
            NoticeEnroll::where('notice_id',$id)->update($map);
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }
    }

    /**
     * 查看报名
     */
    public function enroll() {
        $id = input('id');
        $notice = NoticeModel::get($id);
        $this->assign('name',$notice['title']);
        $map = array(
            'notice_id' => $id,
            'status' => array('egt',0)
        );
        $list = $this->lists("NoticeEnroll",$map);
        foreach ($list as $value) {
            $user = WechatUser::where('userid',$value['userid'])->find();
            $value['name'] = $user['name'];
            $value['mobile'] = $user['mobile'];
        }
        int_to_string($list,array(
            'status' => array(0=>'待审核',1=>"已报名"),
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }

}