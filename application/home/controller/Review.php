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
use think\Db;
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
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, 1 tab FROM sw_notice where ".$where." order by create_time desc")
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
            ->union("SELECT id, type, front_cover, title, content, publisher, create_time, 1 tab FROM sw_notice where ".$where." order by create_time desc")
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
        $Model = $tab ? new NoticeModel() : new NewsModel();
        $info = $Model->save(['status' => $status], ['id' => $id]);
        if ($info) {
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
		return $this->fetch();
	}
}