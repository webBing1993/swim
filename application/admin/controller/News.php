<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/21
 * Time: 14:41
 */
namespace app\admin\controller;

use think\Controller;
use app\admin\model\News as NewsModel;
/**
 * Class News
 * @package 游泳动态控制器
 */
class News extends Admin {
    /**
     * 新闻列表
     */
    public function index(){
        $map = array(
            'type'=> 1,
            'status' => array('egt',0),
        );
        $list = $this->lists('News',$map);
        int_to_string($list,array(
            'status' => array(0=>"待审核",1=>"已发布"),
            'recommend' => array( 1=>"推荐" , 0=>"不推荐")
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 新闻添加
     */
    public function add(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if(empty($data['id'])) {
                unset($data['id']);
            }
            if($data['recommend'] == 1) $data['status'] = 0;
            $newModel = new NewsModel();
            $info = $newModel->validate(true)->save($data);
            if($info) {
                return $this->success("新增成功",Url('News/index'));
            }else{
                return $this->error($newModel->getError());
            }
        }else{
            $this->default_pic();
            $this->assign('msg','');

            return $this->fetch('edit');
        }
    }

    /**
     * 新闻修改
     */
    public function edit(){
        if(IS_POST) {
            $data = input('post.');
            $newModel = new NewsModel();
            $info = $newModel->validate(true)->save($data,['id'=>input('id')]);
            if($info){
                return $this->success("修改成功",Url("News/index"));
            }else{
                return $this->get_update_error_msg($newModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = NewsModel::get($id);
            $this->assign('msg',$msg);
            
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function del() {
        $id = input('id');
        $data['status'] = '-1';
        $info = NewsModel::where('id',$id)->update($data);
        if($info) {
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }
    }

    /**
     * 游泳百科
     */
    public function wiki(){
        $map = array(
            'type'=> 2,
            'status' => array('egt',0),
        );
        $list = $this->lists('News',$map);
        int_to_string($list,array(
            'status' => array(0=>"待审核",1=>"已发布"),
            'recommend' => array( 1=>"推荐" , 0=>"不推荐")
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 百科添加
     */
    public function wikiadd(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if(empty($data['id'])) {
                unset($data['id']);
            }
            $newModel = new NewsModel();
            $info = $newModel->validate(true)->save($data);
            if($info) {
                return $this->success("新增成功",Url('News/wiki'));
            }else{
                return $this->error($newModel->getError());
            }
        }else{
            $this->default_pic();
            $this->assign('msg','');

            return $this->fetch('wikiedit');
        }
    }

    /**
     * 百科修改
     */
    public function wikiedit(){
        if(IS_POST) {
            $data = input('post.');
            $newModel = new NewsModel();
            $info = $newModel->validate(true)->save($data,['id'=>input('id')]);
            if($info){
                return $this->success("修改成功",Url("News/wiki"));
            }else{
                return $this->get_update_error_msg($newModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = NewsModel::get($id);
            $this->assign('msg',$msg);

            return $this->fetch();
        }
    }
    

    /**
     * 在线视频
     */
    public function video(){
        $map = array(
            'type'=> 3,
            'status' => array('egt',0),
        );
        $list = $this->lists('News',$map);
        int_to_string($list,array(
            'status' => array(0=>"待审核",1=>"已发布"),
            'recommend' => array( 1=>"推荐" , 0=>"不推荐")
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 视频添加
     */
    public function videoadd(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if(empty($data['id'])) {
                unset($data['id']);
            }
            $newModel = new NewsModel();
            $info = $newModel->validate(true)->save($data);
            if($info) {
                return $this->success("新增成功",Url('News/video'));
            }else{
                return $this->error($newModel->getError());
            }
        }else{
            $this->default_pic();
            $this->assign('msg','');

            return $this->fetch('videoedit');
        }
    }

    /**
     * 视频修改
     */
    public function videoedit(){
        if(IS_POST) {
            $data = input('post.');
            $newModel = new NewsModel();
            $info = $newModel->validate(true)->save($data,['id'=>input('id')]);
            if($info){
                return $this->success("修改成功",Url("News/video"));
            }else{
                return $this->get_update_error_msg($newModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = NewsModel::get($id);
            $this->assign('msg',$msg);

            return $this->fetch();
        }
    }
    
    
    
    
}