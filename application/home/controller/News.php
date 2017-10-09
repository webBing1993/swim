<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 15:56
 */

namespace app\home\controller;
use app\home\model\Browse;
use app\home\model\Collect;
use app\home\model\Comment;
use app\home\model\Like;
use app\home\model\Notice;
use app\home\model\Picture;
use app\home\model\WechatUser;
use think\Controller;
use app\home\model\News as NewsModel;
use think\Url;

/**
 * Class News
 * @package 新闻动态
 */
class News extends Base {
    /**
     * 主页
     */
    public function index(){
		$Model = new NewsModel;
		$userid = session('userId');
		$tops = $Model->getThreeTop();
		$this->assign('tops',$tops);

		$news = $Model->getNews();
		$this->assign('news',$news);

		$wiki = $Model->getWiki();
		$this->assign('wiki',$wiki);
		
		$videos = $Model->getVideo($userid);
		$this->assign('videos',$videos);

		return $this->fetch();
	}

	/**
	 * 视频点击
	 */
	public function video() {
		$id = input('id');
		$userId = session('userId');
		if($userId != "visitor"){
			//浏览不存在则存入pb_browse表
			$this->browser(1,$userId,$id);
		}
		/*$map = array(
				'id' => input('id')
		);
		$res = NewsModel::where($map)->setInc('views');*/
		if(true) {
			return $this->success("播放成功");
		}else {
			return $this->error("播放失败");
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
		/*if($info['status'] == -1){
			return $this->redirect('public/404');
		}*/
		//浏览加一
		/*$info['views'] = array('exp','`views`+1');
		NewsModel::where('id',$id)->update($info);*/

		if($userId != "visitor"){
			//浏览不存在则存入pb_browse表
			$this->browser(1,$userId,$id);
		}
		//详细信息
		$info = NewsModel::get($id);
		//分享图片及链接及描述
		$image = Picture::where('id',$info['front_cover'])->find();
		$info['share_image'] = "http://".$_SERVER['SERVER_NAME'].$image['path'];
		$info['link'] = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REDIRECT_URL'];
		$info['desc'] = str_replace('&nbsp;','',strip_tags($info['content']));
		$info['desc'] = str_replace(" ",'',$info['desc']);
		$info['desc'] = str_replace("\n",'',$info['desc']);
		//$info['desc'] = substr($info['desc'], 0, 150);
		//var_dump($info['desc']);die;

		//获取 文章点赞
		$likeModel = new Like();
		$like = $likeModel->getLike(1,$id,$userId);
		$info['is_like'] = $like;
		//获取 收藏
		$collectModel = new Collect();
		$collect = $collectModel->getCollect(1,$id,$userId);
		$info['is_collect'] = $collect;
		$this->assign('detail',$info);
		//获取 评论
		$commentModel = new Comment();
		$comment = $commentModel->getComment(1,$id,$userId);
		$this->assign('comment',$comment);
		return $this->fetch();
	}

	/**
	 * 获取更多数据
	 */
	public function listMore(){
		$data = input('post.');
		$Model = new NewsModel();
		$list = $Model->getMoreList($data);
		if($list){
			return $this->success("加载成功",'',$list);
		}else{
			return $this->error("加载失败");
		}
	}
}