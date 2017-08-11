<?php
/**
 * Created by PhpStorm.
 * User: zyf
 * QQ: 2205446834@qq.com
 * Date: 2017/6/28
 * Time: 16:26
 */

namespace app\home\controller;
use app\home\model\WechatDepartment;
use app\home\model\WechatUser;
use app\home\model\WechatUserTag;
use think\Db;
class Book extends Base
{
    /*通讯录首页*/
    public function  index(){
//        $this->anonymous();
//        // 获取  我的部门
//        $userId = session('userId');
//        $depart = Db::table('pb_wechat_department_user')
//            ->alias('a')
//            ->join('pb_wechat_department b','a.departmentid = b.id','LEFT')
//            ->where('a.userid',$userId)
//            ->field('a.departmentid,b.name')
//            ->select();
//        $this->assign('list',$depart);
//
//        //判断是否有通讯录查看权限
//        $map = array(
//            'tagid' => 3,
//            'userid' => $userId
//        );
//        $tag = WechatUserTag::where($map)->find();
//        if(empty($tag)) {
//            $this->assign('is_power',0);
//        }else {
//            $this->assign('is_power',1);
//        }
        return $this->fetch();
    }
    /* 搜索
     */
    public function search(){
//        $name = input('val');
//        $list = Db::table('pb_wechat_user')->where('name',['like',"%$name%"],['neq',''])->field('id,userid,name,header,avatar')->select();  // 模糊查询
//        foreach($list as $key => $value){
//            if (empty($value['header'])){  //  获取头像
//                if (empty($value['avatar'])){
//                    $list[$key]['header'] = '';
//                }else{
//                    $list[$key]['header'] = $value['avatar'];
//                }
//            }
//            $Depart = Db::table('pb_wechat_department_user')->where(['userid' => $value['userid']])->order('id desc')->field('departmentid')->find(); //  获取用户所在部门
//            $list[$key]['did'] = $Depart['departmentid'];
//        }
//        return $this->success('','',$list);
//    }
//    public function grouplist(){
//        $this->anonymous();
//        $did = input('get.did/d');   // 获取部门列表
//        $list = Db::table('pb_wechat_department')->where('parentid',$did)->order('id')->field('id,name')->select();
//        $this->assign('list',$list);
        return $this->fetch();
    }

    /*   通讯录用户列表*/
    public function  userlist(){
//        $this->anonymous();
//        $did = input('get.did/d');
//        $department = Db::table('pb_wechat_department')->field('name')->find($did);
//        $list = Db::table('pb_wechat_department_user')->where('departmentid',$did)->order(['order'=>'desc','id'])->field('userid')->select();  // 获取 用户列表
//        foreach($list as $key => $value){
//            $User = Db::table('pb_wechat_user')->where('userid',$value['userid'])->field('id,header,name,avatar')->find();
//            $list[$key]['name'] = $User['name'];
//            $list[$key]['id'] = $User['id'];
//            if (empty($User['header'])){   //  头像
//                if (empty($User['avatar'])){
//                    $list[$key]['header'] = '';
//                }else{
//                    $list[$key]['header'] = $User['avatar'];
//                }
//            }else{
//                $list[$key]['header'] = $User['header'];
//            }
//        }
//        $this->assign('list',$list);
//        $this->assign('depart',$department['name']);
//        $this->assign('did',$did);
        return $this->fetch();
    }
    /*   通讯录用户详情*/
    public function  userinfo(){
//        $this->anonymous();  // 用户信息
//        $id = input('id');
//        $did = input('get.did/d');
//        $department = Db::table('pb_wechat_department')->field('name')->find($did);
//        $userinfo =  WechatUser::where('id',$id)->find();
//        if (empty($userinfo['header'])){
//            if (empty($userinfo['avatar'])){
//                $userinfo['header'] = '';
//            }else{
//                $userinfo['header'] = $userinfo['avatar'];
//            }
//        }
//        $userinfo['depart'] = $department['name'];
//        $userinfo['did'] = $did;
//        $this->assign("info",$userinfo);
        return $this->fetch();
    }
	/* 二级列表*/
	public function  grouplist(){
		return $this->fetch();
	}
	/* 三级列表*/
	public function  deplist(){
		return $this->fetch();
	}
}