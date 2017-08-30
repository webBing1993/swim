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
use app\home\model\News;
use app\home\model\Notice;
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
            'status' => 1,
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
        $data = input('post.');
        var_dump($data);die;
        $newModel = new NewsModel();
        $info = $newModel->validate(true)->save($data, ['id' => input('id')]);
        if ($info) {
            return $this->success("修改成功", Url("News/index"));
        } else {
            return $this->get_update_error_msg($newModel->getError());
        }
    }

}