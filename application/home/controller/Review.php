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
            $Model = new NewsModel();
        }elseif($tab == 1){
            $Model = new NoticeModel();
        }else{
            $Model = new CertificateReviewModel();
        }
        //$Model = $tab ? new NoticeModel() : new NewsModel();
        $info = $Model->save(['status' => $status], ['id' => $id]);
        if ($info) {
            if($tab==1 && $status == 1){
                $httpUrl = config('http_url');
                $focus = $Model->where('id',$id)->find();
                $title = $focus['title'];
                $content = str_replace('&nbsp;','',strip_tags($focus['content']));
                $content = str_replace(" ",'',$content);
                $content = str_replace("\n",'',$content);
                $content = mb_substr($content, 0, 100);
                $url = $httpUrl."/home/notice/detail/id/".$focus['id'].".html";
                $pre = "【".NoticeModel::TYPE_ARRAY[$focus['type']]."】";

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