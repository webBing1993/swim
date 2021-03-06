<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/3 0003
 * Time: 下午 3:02
 */
/**
 * Created by PhpStorm.
 * User: stiff <1570004138@163.com>
 * Date: 2017/6/5
 * Time: 13:53
 */
namespace app\home\controller;
use app\home\model\News as NewsModel;
use app\home\model\Notice as NoticeModel;
use app\home\model\CertificateReview as CertificateReviewModel;
use app\home\model\WechatTag;
use app\home\model\WechatUser;
use app\home\model\WechatUserTag;
use think\Config;
use think\Db;
use app\admin\model\Picture;
use com\wechat\TPQYWechat;
/**
 * 审核页面
 */
class Review extends Base{
    /**
     * @return mixed
     * 未审核列表
     */
    public function reviewlist() {
        $userId = session('userId');
        $tag = WechatUserTag::where(['userid' => $userId, 'tagid' => WechatTag::TAG_LEADER])->find();
        if(!$tag){
            return $this ->fetch('user/null');
        }
        $map = array(
            'status' => 0,
            'recommend' => 1
        );
        $where = ' status=0 and recommend=1';
        $res = Db::field('id , type, front_cover, title, content, publisher, create_time, 0 tab')
            ->table('sw_news')
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, 1 tab FROM sw_notice where status=0 ")
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, 2 tab FROM sw_certificate_review where ".$where." order by create_time desc")
            ->where($map)
            ->select();
        //var_dump($res);die;
        $this->assign('res', $res);
        return $this->fetch();
    }

    /**
     * @return mixed
     * 已审核列表
     */
    public function passlist() {
        $userId = session('userId');
        $tag = WechatUserTag::where(['userid' => $userId, 'tagid' => WechatTag::TAG_LEADER])->find();
        if(!$tag){
            return $this ->fetch('user/null');
        }
        $map = array(
            'status' => ['neq', 0],
            'recommend' => 1
        );
        $where = ' status<>0 and recommend=1';
        $res = Db::field('id , type, front_cover, title, content, publisher, create_time, status, 0 tab')
            ->table('sw_news')
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, status, 1 tab FROM sw_notice where status<>0 ")
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, status, 2 tab FROM sw_certificate_review where ".$where." order by create_time desc limit 10")
            ->where($map)
            ->select();
        //var_dump($res);die;
        $this->assign('res', $res);
        return $this->fetch();
    }
    /**
     * 已审核列表 加载更多
     */
    public function passlistmore(){
        $len = input('length');
        $userId = session('userId');
        $map = array(
            'status' => ['neq', 0],
            'recommend' => 1
        );
        $where = ' status<>0 and recommend=1';
        $list = Db::field('id , type, front_cover, title, content, publisher, create_time, status, 0 tab')
            ->table('sw_news')
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, status, 1 tab FROM sw_notice where status<>0 ")
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, status, 2 tab FROM sw_certificate_review where ".$where." order by create_time desc limit $len,6")
            ->where($map)
            ->select();
        foreach($list as $key => $value){
            $list[$key]['time'] = date("Y-m-d",$value['create_time']);
            $img = Picture::get($value['front_cover']);
            $list[$key]['path'] = $img['path'];
            if($value['tab'] == 0){
                $list[$key]['sort'] = NewsModel::TYPE_ARRAY[$value['type']];
            }elseif($value['tab'] == 1){
                $list[$key]['sort'] = NoticeModel::TYPE_ARRAY[$value['type']];
            }else{
                $list[$key]['sort'] = CertificateReviewModel::TYPE_ARRAY[$value['type']];;
            }
        }
        if($list){
            return $this->success("加载成功",'',$list);
        }else{
            return $this->error("加载失败");
        }
    }
    public function review()
    {
        $id = input('id');
        $status = input('status');
        $tab = input('tab');
        if($tab == 0){
            $info = NewsModel::update(['status' => $status], ['id' => $id]);
        }elseif($tab == 1){
            $info = NoticeModel::update(['status' => $status], ['id' => $id]);
        }else{
            $info = CertificateReviewModel::update(['status' => $status], ['id' => $id]);
        }
        //$Model = $tab ? new NoticeModel() : new NewsModel();
        //$info = $Model::save(['status' => $status], ['id' => $id]);

        if ($info) {
            if($tab<=1 && $status == 1){
                if($tab == 0){
                    $focus = NewsModel::where('id',$id)->find();
                    $url = "/home/news/detail/id/".$focus['id'].".html";
                    $pre = "【".NewsModel::TYPE_ARRAY[$focus['type']]."】";
                }elseif($tab == 1){
                    $focus = NoticeModel::where('id',$id)->find();
                    $url = "/home/notice/detail/id/".$focus['id'].".html";
                    $pre = "【".NoticeModel::TYPE_ARRAY[$focus['type']]."】";
                }else{
                    return $this->error();
                }
                $this->push($focus, $url, $pre);
            }
            if($tab==2){
                $coachModel = CertificateReviewModel::get($id);
                WechatUser::where('userid',$coachModel['userid'])->update(['certificate_status'=>$status]);
            }

            return $this->success();
        } else {
            return $this->error();
        }
    }
	/**
	 * @return mixed
	 * 助教证件
	 */
	public function carddetail(){
        $id = input('id');
        $coachModel = CertificateReviewModel::get($id);
        $this->assign('coachModel',$coachModel);
		return $this->fetch();
	}
}