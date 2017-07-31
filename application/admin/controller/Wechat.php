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
        $Wechat = new QYWechat(Config::get('mail'));
        if($Wechat->errCode != 40001) {
            return $this->error("同步出错");
        }

        /* 同步部门 */
        $list = $Wechat->getDepartment();

        /* 同步最顶级部门下面的用户 */
        foreach ($list['department'] as $key=>$value) {
            $users = $Wechat->getUserListInfo($list['department'][$key]['id']);
            foreach ($users['userlist'] as $user) {
                $user['department'] = $user['department'][0];
                $user['order'] = $user['order'][0];

                foreach ($user['extattr']['attrs'] as $value) {
                    switch ($value['name']){
                        case "出生年月":
                            $user['birthday'] = $value['value'];
                            break;
                        case "民族":
                            $user['nation'] = $value['value'];
                            break;
                        case "学历":
                            $user['education'] = $value['value'];
                            break;
                        case "入党时间":
                            $user['partytime'] = $value['value'];
                            break;
                        case "工作时间":
                            $user['worktime'] = $value['value'];
                            break;
                        case "所在支部":
                            $user['branch'] = $value['value'];
                            break;
                        case "虚拟网":
                            $user['virtualnet'] = $value['value'];
                            break;
                        default:
                            break;
                    }
                }
                $user['extattr'] = json_encode($user['extattr']);

                if(WechatUser::get(['userid'=>$user['userid']])) {
                    WechatUser::where(['userid'=>$user['userid']])->update($user);
                } else {
                    WechatUser::create($user);
                }
            }
        }
        $data = "用户数:".count($users['userlist'])."!";

        return $this->success("同步成功", '', $data);
    }

    /**
     * 同步部门
     */
    public function synchronizeDp(){
        $Wechat = new QYWechat(Config::get('mail'));
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

        $data = "同步部门数:".count($list['department'])."!";

        return $this->success("同步成功", '', $data);
    }

    /**
     * 同步标签
     */
    public function synchronizeTag(){
        $Wechat = new QYWechat(Config::get('mail'));
        if($Wechat->errCode != 40001) {
            return $this->error("同步出错");
        }

        /* 同步标签 */
        WechatTag::where('1=1')->delete();
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
            if(empty($users['userlist'])){
                foreach ($users['partylist'] as $user){
                    $info = $Wechat->getUserListInfo($user);
                    foreach ($info['userlist'] as $val){
                        $data = ['tagid' => $value['tagid'],'userid' => $val['userid']];
                        if(empty(WechatUserTag::where($data)->find())){
                            WechatUserTag::create($data);
                        }
                    }
                };
            }else{
                foreach ($users['userlist'] as $user) {
                    $data = ['tagid'=>$value['tagid'], 'userid'=>$user['userid']];
                    if(empty(WechatUserTag::where($data)->find())){
                        WechatUserTag::create($data);
                    }
                }
            }
        }

        $data = "同步标签数:".count($tags['taglist'])."!";

        return $this->success("同步成功", '', $data);
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