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
        $map = array(
            'status' => 0,
            'recommend' => 1
        );
        $where = ' status=0 and recommend=1';
        $res = Db::field('id , type, front_cover, title, content, publisher, create_time, 0 tab')
            ->table('sw_news')
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, 1 tab FROM sw_notice where ".$where)
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
        $map = array(
            'status' => 1,
            'recommend' => 1
        );
        $where = ' status=1 and recommend=1';
        $res = Db::field('id , type, front_cover, title, content, publisher, create_time, 0 tab')
            ->table('sw_news')
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, 1 tab FROM sw_notice where ".$where)
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, 2 tab FROM sw_certificate_review where ".$where." order by create_time desc")
            ->where($map)
            ->select();
        //var_dump($res);die;
        $this->assign('res', $res);
        return $this->fetch();
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