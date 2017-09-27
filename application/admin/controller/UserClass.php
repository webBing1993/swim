<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/27
 * Time: 9:14
 */
namespace app\admin\controller;
use app\admin\model\UserClass as UserClassModel;
use org\Page;
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
        $list = $this->lists('UserClass');
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

}