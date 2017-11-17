<?php
/**
 * Created by PhpStorm.
 * User: 虚空之翼 <183700295@qq.com>
 * Date: 16/5/20
 * Time: 09:14
 */

namespace app\admin\controller;


use app\admin\model\WechatDepartment;
use app\admin\model\WechatDepartmentUser;
use app\admin\model\WechatTag;
use app\admin\model\WechatUser;
use app\admin\model\WechatUserTag;
use com\wechat\QYWechat;
use com\wechat\TPWechat;
use think\Config;
use think\Input;

class Wechat extends Admin
{
    public function index() {
        $xx = substr('1993.10',0,4);
        $ss = date("Y",time());
    }


    public function user() {
        $name = input('name');
        $map = ['name' => ['like', "%$name%"]];
        $list = $this->lists("WechatUser", $map);
        $department = WechatDepartment::column('id, name');
        foreach ($list as $key=>$value) {
            $departmentId = $value['department'];
            if($departmentId){
            	if ($departmentId ==1){$departmentId =2;}
                $name = $department[$departmentId];
                $list[$key]['department'] = $name;
            }else{
                $list[$key]['department'] = "暂无";
            }
        }
        // 状态转化
        wechat_status_to_string($list);
        $this->assign('list', $list);
        $this->assign('department', $department);

        return $this->fetch();
    }


    /**
     * 同步通讯录用户
     */
    public function synchronizeUser() {
        $Wechat = new QYWechat(Config::get('work'));
        if($Wechat->errCode != 40001) {
            return $this->error("同步出错");
        }
        /* 同步部门 */
        $list = $Wechat->getDepartment();
        /* 同步最顶级部门下面的用户 */
        $i = 0;
        foreach ($list['department'] as $key=>$value) {
            $users = $Wechat->getUserListInfo($list['department'][$key]['id']);
            foreach ($users['userlist'] as $userlist) {
                $user = [];
                $user['userid'] = $userlist['userid'];
                $user['name'] = $userlist['name'];
                $user['position'] = $userlist['position'];
                $user['mobile'] = $userlist['mobile'];
                $user['gender'] = $userlist['gender'];
                $user['email'] = $userlist['email'];
                $user['avatar'] = $userlist['avatar'];
                $user['status'] = $userlist['status'];
                $user['enable'] = $userlist['enable'];
                $user['isleader'] = $userlist['isleader'];
                $user['department'] = $userlist['department'][0];
                $user['order'] = $userlist['order'][0];
                foreach ($userlist['extattr']['attrs'] as $value) {
                    switch ($value['name']){
                        case "学历":
                            $user['education'] = $value['value'];
                            break;
                        case "身份证号":
                            $user['identity'] = $value['value'];
                            break;
                        case "出生年月":
                            $user['birthday'] = $value['value'];
                            break;
                        case "监护人":
                            $user['guardian'] = $value['value'];
                            break;
                        case "招生日期":
                            $user['enrollday'] = empty($value['value'])?null:$value['value'];
                            break;
                        case "身高":
                            $user['height'] = $value['value'];
                            break;
                        case "体重":
                            $user['weight'] = $value['value'];
                            break;
                        case "星级情况":
                            $user['star_level'] = $value['value'];
                            break;
                        case "原教练":
                            $user['ever_coach'] = $value['value'];
                            break;
                        case "现教练":
                            $user['now_coach'] = $value['value'];
                            break;
                        case "上级教练":
                            $user['upper_coach'] = $value['value'];
                            break;
                        case "家庭地址":
                            $user['address'] = $value['value'];
                            break;
                        case "就读学校":
                            $user['school'] = $value['value'];
                            break;
                        case "学籍号":
                            $user['student_code'] = $value['value'];
                            break;
                        case "文化成绩":
                            $user['achievement'] = $value['value'];
                            break;
                        case "所属班级":
                            $user['class_id'] = $value['value'] ? $value['value'] : 0;
                            break;
                        case "技术职称":
                            $user['technical_title'] = $value['value'];
                            break;
                        case "备用电话":
                            $user['emergency_call'] = $value['value'];
                            break;
                        /*case "成员类型":
                            $user['member_type'] = $value['value'] ? $value['value'] : 0;
                            break;*/
                        default:
                            break;
                    }
                }
                $user['extattr'] = json_encode($userlist['extattr']);
                if(!empty($user['now_coach'])){
                    $user['coach_id'] = WechatUser::where(['name'=>$user['now_coach']])->value('userid');
                }
                if(!empty($user['upper_coach'])){
                    $user['coach_id'] = WechatUser::where(['name'=>$user['upper_coach']])->value('userid');
                }
                if(empty($user['coach_id'])) {
                    $user['coach_id'] = '';
                }
                unset($user['upper_coach']);
                if(WechatUser::get(['userid'=>$user['userid']])) {
                    WechatUser::where(['userid'=>$user['userid']])->update($user);
                } else {
                    WechatUser::create($user);
                }
                $i++;
            }
            for($n=1;$n<=30;$n++){
                $j = $n<10 ? '0'.$n : $n;
                $user = [];
                $user['name'] = '';
                $user['member_type'] = WechatUser::MEMBER_TYPE_STUDENT;
                $user['userid'] = $user['mobile'] = '188888880'.$j;
                if(WechatUser::get(['userid'=>$user['userid']])) {
                    WechatUser::where(['userid'=>$user['userid']])->update($user);
                } else {
                    WechatUser::create($user);
                }
            }
        }
        $data = "用户数:".$i."!";

        return $this->success("同步成功", Url('user'), $data);
    }

    /**
     * 同步部门
     */
    public function synchronizeDp(){
        $Wechat = new QYWechat(Config::get('work'));
        if($Wechat->errCode != 40001) {
            return $this->error("同步出错");
        }

        /* 同步部门 */
        $list = $Wechat->getDepartment();
        foreach ($list['department'] as $key=>$value) {
            if(WechatDepartment::get($value['id'])){
                WechatDepartment::update($value);
            } else {
                WechatDepartment::create($value);
            }
        }

        /* 同步部门-用户关系表 */
        WechatDepartmentUser::where('1=1')->delete();
        foreach ($list['department'] as $key=>$value) {
            $users = $Wechat->getUserListInfo($value['id']);
            foreach ($users['userlist'] as $user) {
                $data = array(
                    'departmentid'=>$value['id'],
                    'userid'=>$user['userid'],
                    'order' => $user['order'][0],
                );
                if(empty(WechatDepartmentUser::where($data)->find())){
                    WechatDepartmentUser::create($data);
                }
                
//                if($value['id'] != 1) {
//                    $data1 = ['departmentid' => 1, 'userid' => $user['userid']];     //当部门补位1时补全用户
//                    if(empty(WechatDepartmentUser::where($data1)->find())){
//                        WechatDepartmentUser::create($data1);
//                    }
//                }
            }
        }

        $data = "同步部门数:".(count($list['department'])+1)."!";

        return $this->success("同步成功", Url('department'), $data);
    }

    /**
     * 同步标签
     */
    public function synchronizeTag(){
        $Wechat = new QYWechat(Config::get('work'));
        if($Wechat->errCode != 40001) {
            return $this->error("同步出错");
        }

        /* 同步标签 */
        //WechatTag::where('1=1')->delete();
        $tags = $Wechat->getTagList();
        foreach ($tags['taglist'] as $tag) {
            if(WechatTag::get(['tagid'=>$tag['tagid']])) {
                WechatTag::where(['tagid'=>$tag['tagid']])->update($tag);
            } else {
                WechatTag::create($tag);
            }
        }

        /* 同步标签-用户关系表 */
        WechatUserTag::where('1=1')->delete();
        foreach ($tags['taglist'] as $value) {
            $users = $Wechat->getTag($value['tagid']);
            if(!empty($users['partylist'])){
                foreach ($users['partylist'] as $user){
                    $info = $Wechat->getUserListInfo($user);
                    foreach ($info['userlist'] as $val){
                        $data = ['tagid' => $value['tagid'],'userid' => $val['userid']];
                        if(empty(WechatUserTag::where($data)->find())){
                            WechatUserTag::create($data);
                            $member_type = WechatTag::TAG_TO_METYPE[$value['tagid']];
                            if($member_type !== WechatUser::where(['userid'=>$val['userid']])->value('member_type')){
                                WechatUser::where(['userid'=>$val['userid']])->update(['member_type'=>$member_type]);
                            }
                        }
                    }
                };
            }
            if(!empty($users['userlist'])){
                foreach ($users['userlist'] as $user) {
                    $data = ['tagid'=>$value['tagid'], 'userid'=>$user['userid']];
                    if(empty(WechatUserTag::where($data)->find())){
                        WechatUserTag::create($data);
                        $member_type = WechatTag::TAG_TO_METYPE[$value['tagid']];
                        if($member_type !== WechatUser::where(['userid'=>$user['userid']])->value('member_type')){
                            WechatUser::where(['userid'=>$user['userid']])->update(['member_type'=>$member_type]);
                        }
                    }
                }
            }
        }
        for($i=1;$i<=30;$i++){
            $j = $i<10 ? '0'.$i : $i;
            $data = ['tagid'=>WechatTag::TAG_STUDENT_SPECIAL, 'userid'=>'188888880'.$j];
            if(empty(WechatUserTag::where($data)->find())){
                WechatUserTag::create($data);
            }
        }
        $data = "同步标签数:".count($tags['taglist'])."!";

        return $this->success("同步成功", Url('tag'), $data);
    }
    
    public function department(){
        $list = $this->lists("WechatDepartment");
        $this->assign('list', $list);

        return $this->fetch();
    }

    public function tag(){
        $list = $this->lists("WechatTag");
        $this->assign('list', $list);

        return $this->fetch();
    }


}