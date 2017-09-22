<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/21
 * Time: 14:41
 */
namespace app\admin\controller;

use think\Db;
use think\Controller;
use app\admin\model\News as NewsModel;
use app\admin\model\Browse;
use org\Page;
use think\Config;
use app\admin\model\Picture;
use com\wechat\TPQYWechat;
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
                $httpUrl = config('http_url');
                $focus = NewsModel::where('id',$newModel->id)->find();
                $title = $focus['title'];
                $content = str_replace('&nbsp;','',strip_tags($focus['content']));
                $content = str_replace(" ",'',$content);
                $content = str_replace("\n",'',$content);
                $content = mb_substr($content, 0, 100);
                //$str = strip_tags($focus['content']);
                //$des = mb_substr($str,0,100);
                //$content = str_replace("&nbsp;","",$des);  //空格符替换成空
                $url = $httpUrl."/home/news/detail/id/".$focus['id'].".html";
                $pre = "【".NewsModel::TYPE_ARRAY[$focus['type']]."】";

                $img = Picture::get($focus['front_cover']);
                $path = $httpUrl.$img['path'];
                $info = array(
                    "title" => $pre.$title,
                    "description" => $content,
                    "url" => $url,
                    "picurl" => $path,
                );

                //重组成article数据
                $send = array();
                $send['articles'][0] = $info;
                //发送给企业号
                $Wechat = new TPQYWechat(Config::get('news'));
                $touser = config('touser');
                $newsConf = config('news');
                $message = array(
                    "touser" => $touser, //发送给全体，@all
                    "msgtype" => 'news',
                    "agentid" => $newsConf['agentid'],
                    "news" => $send,
                    "safe" => "0"
                );
                $msg = $Wechat->sendMessage($message);

                return $this->success("新增成功",Url('News/index'));
            }else{
                return $this->error($newModel->getError());
            }
        }else{
            $this->default_pic();
            $this->assign('num',rand(101, 112));
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
                $httpUrl = config('http_url');
                $focus = NewsModel::where('id',$newModel->id)->find();
                $title = $focus['title'];
                $content = str_replace('&nbsp;','',strip_tags($focus['content']));
                $content = str_replace(" ",'',$content);
                $content = str_replace("\n",'',$content);
                $content = mb_substr($content, 0, 100);
                //$str = strip_tags($focus['content']);
                //$des = mb_substr($str,0,100);
                //$content = str_replace("&nbsp;","",$des);  //空格符替换成空
                $url = $httpUrl."/home/news/detail/id/".$focus['id'].".html";
                $pre = "【".NewsModel::TYPE_ARRAY[$focus['type']]."】";

                $img = Picture::get($focus['front_cover']);
                $path = $httpUrl.$img['path'];
                $info = array(
                    "title" => $pre.$title,
                    "description" => $content,
                    "url" => $url,
                    "picurl" => $path,
                );

                //重组成article数据
                $send = array();
                $send['articles'][0] = $info;
                //发送给企业号
                $Wechat = new TPQYWechat(Config::get('news'));
                $touser = config('touser');
                $newsConf = config('news');
                $message = array(
                    "touser" => $touser, //发送给全体，@all
                    "msgtype" => 'news',
                    "agentid" => $newsConf['agentid'],
                    "news" => $send,
                    "safe" => "0"
                );
                $msg = $Wechat->sendMessage($message);

                return $this->success("新增成功",Url('News/wiki'));
            }else{
                return $this->error($newModel->getError());
            }
        }else{
            $this->default_pic();
            $this->assign('num',rand(101, 112));
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
                $httpUrl = config('http_url');
                $focus = NewsModel::where('id',$newModel->id)->find();
                $title = $focus['title'];
                $content = str_replace('&nbsp;','',strip_tags($focus['content']));
                $content = str_replace(" ",'',$content);
                $content = str_replace("\n",'',$content);
                $content = mb_substr($content, 0, 100);
                //$str = strip_tags($focus['content']);
                //$des = mb_substr($str,0,100);
                //$content = str_replace("&nbsp;","",$des);  //空格符替换成空
                $url = $httpUrl."/home/news/detail/id/".$focus['id'].".html";
                $pre = "【".NewsModel::TYPE_ARRAY[$focus['type']]."】";

                $img = Picture::get($focus['front_cover']);
                $path = $httpUrl.$img['path'];
                $info = array(
                    "title" => $pre.$title,
                    "description" => $content,
                    "url" => $url,
                    "picurl" => $path,
                );

                //重组成article数据
                $send = array();
                $send['articles'][0] = $info;
                //发送给企业号
                $Wechat = new TPQYWechat(Config::get('news'));
                $touser = config('touser');
                $newsConf = config('news');
                $message = array(
                    "touser" => $touser, //发送给全体，@all
                    "msgtype" => 'news',
                    "agentid" => $newsConf['agentid'],
                    "news" => $send,
                    "safe" => "0"
                );
                $msg = $Wechat->sendMessage($message);

                return $this->success("新增成功",Url('News/video'));
            }else{
                return $this->error($newModel->getError());
            }
        }else{
            $this->default_pic();
            $this->assign('num',rand(101, 112));
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

	/**
	 * 阅读名单
	 */
	public function view(){
        $table = input('table');
        $id = input('id');
        if(!$table || !$id){
            return "参数错误";
        }
        $total = Db::field('wu.header, wu.avatar, wu.name, wu.mobile, wb.create_time')
            ->table('sw_browse wb')
            ->join('sw_wechat_user wu','wb.uid = wu.userid')
            ->where(['wb.table' => $table, 'wb.aid' => $id, 'wb.status' => 0])
            ->order('wb.create_time desc')
            ->count();
        //var_dump($total);die;
        $Page = new Page($total, 10);
        if($total>10) {
            $Page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p = $Page->show();
        $list = Db::field('wu.header, wu.avatar, wu.name, wu.mobile, wb.create_time')
            ->table('sw_browse wb')
            ->join('sw_wechat_user wu','wb.uid = wu.userid')
            ->where(['wb.table' => $table, 'wb.aid' => $id, 'wb.status' => 0])
            ->order('wb.create_time desc')
            ->limit($Page->firstRow, 10)
            ->select();
        //var_dump($list);die;
        $this->assign('_page', $p ? $p: '');
        $this->assign('_total',$total);
        $this->assign('list',$list);
		return $this->fetch();
	}

}