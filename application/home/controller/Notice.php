<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 16:12
 */

namespace app\home\controller;

use app\home\model\Collect;
use app\home\model\Comment;
use app\home\model\Like;
use app\home\model\Notice as NoticeModel;
use app\home\model\Picture;
use think\Db;

/**
 * Class Notice
 * @package  通知   活动
 */
class Notice extends Base {
    /**
     * 主页
     */
	public function index(){
		$Model = new NoticeModel();
		$topActivity = $Model->getThreeActivity();
		$this->assign('top_activity',$topActivity);
		$Activity = $Model->getActivity();
		$this->assign('activity',$Activity);
		$topNote = $Model->getThreeNote();
		$this->assign('top_note',$topNote);
		$Note = $Model->getNotes();
		$this->assign('note',$Note);
		return $this->fetch();
	}
	/**
	 * 详情页
	 */
	public function detail(){
		$this->anonymous();
		$this->jssdk();

		$id = input('id');
		$userId = session('userId');
		//浏览加一
		$info['views'] = array('exp','`views`+1');
		NoticeModel::where('id',$id)->update($info);

		if($userId != "visitor"){
			//浏览不存在则存入pb_browse表
			$this->browser(2,$userId,$id);
		}
		//详细信息
		$info = NoticeModel::get($id);
		//分享图片及链接及描述
		$image = Picture::where('id',$info['front_cover'])->find();
		$info['share_image'] = "http://".$_SERVER['SERVER_NAME'].$image['path'];
		$info['link'] = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL'];
		$info['desc'] = str_replace('&nbsp;','',strip_tags($info['content']));

		//获取 文章点赞
		$likeModel = new Like();
		$like = $likeModel->getLike(2,$id,$userId);
		$info['is_like'] = $like;
		//获取 收藏
		$collectModel = new Collect();
		$collect = $collectModel->getCollect(2,$id,$userId);
		$info['is_collect'] = $collect;
		$this->assign('detail',$info);
		//获取 评论
		$commentModel = new Comment();
		$comment = $commentModel->getComment(2,$id,$userId);
		$this->assign('comment',$comment);
		return $this->fetch();
	}

	/**
	 * 获取更多数据
	 */
	public function listMore(){
		$data = input('post.');
		$Model = new NoticeModel();
		$list = $Model->getMoreList($data);
		if($list){
			return $this->success("加载成功",'',$list);
		}else{
			return $this->error("加载失败");
		}
	}

}