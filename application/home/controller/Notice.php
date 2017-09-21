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
use app\home\model\NoticeEnroll;
use app\home\model\Picture;
use app\home\model\WechatUserTag;
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
		$userid = session('userId');
		$tag_id = WechatUserTag::where(['userid' => $userid])->value('tagid');
		$Model = new NoticeModel();

		//活动轮播
		$topActivity = $Model->getThreeActivity($tag_id);
		$this->assign('top_activity',$topActivity);
		//新闻
		$Activity = $Model->getActivity($userid, $tag_id);
		$this->assign('activity',$Activity);
		//通知轮播
		$topNote = $Model->getThreeNote($tag_id);
		$this->assign('top_note',$topNote);
		//通知
		$Note = $Model->getNotes($tag_id);
		$this->assign('note',$Note);

		$this->assign('userid',$userid);
		return $this->fetch();
	}

	/**
	 * 报名
	 */
	public function enroll() {
		$id = input('id');
		$data = array(
			'userid' => session('userId'),
			'notice_id' => $id,
			'status' => 1,
		);
		$Model = new NoticeEnroll();
		$msg = $Model->where($data)->find();
		if(empty($msg)) {
			$info = NoticeModel::where(['id' => $id])->find();
			if($info['enrollnum']<$info['people']){
				$res = $Model->create($data);
				if($res) {
					NoticeModel::where('id',$id)->setInc("enrollnum");
					return $this->success("报名成功");
				}else {
					return $this->error("报名失败");
				}
			}else{
				return $this->error("人数已满");
			}
		}else {
			return $this->error("已报名");
		}
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
		$info['desc'] = str_replace(" ",'',$info['desc']);
		$info['desc'] = str_replace("\n",'',$info['desc']);

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
		$userid = session('userId');
		$tag_id = WechatUserTag::where(['userid' => $userid])->value('tagid');
		$Model = new NoticeModel();
		$list = $Model->getMoreList($data, $tag_id);
		if($list){
			return $this->success("加载成功",'',$list);
		}else{
			return $this->error("加载失败");
		}
	}

}