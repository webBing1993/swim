<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/12
 * Time: 10:23
 */

namespace app\home\model;


use think\Model;

class Notice extends Model {
    const TYPE_NOTICE = 1;
    const TYPE_ACTIVITY = 2;
    const TYPE_ARRAY = [
        self::TYPE_NOTICE  => '相关通知',
        self::TYPE_ACTIVITY  => '活动情况',
    ];
    /**
     * 获取活动顶部轮播
     */
    public function getThreeActivity($tag_id) {
        $map = array(
            'sn.type' => 2,
            'sn.status' => 1,
            'sn.recommend' =>1,
            'snt.tagid' =>$tag_id,
        );
        $order = array("sn.create_time desc");
        $res = \think\Db::field('sn.*')
            ->table('sw_notice sn')
            ->join('sw_notice_tag snt','sn.id = snt.pid')
            ->where($map)
            ->order($order)
            ->limit(3)
            ->select();
        //$res = $this->where($map)->order($order)->limit(3)->select();
        return $res;
    }

    /**
     * 获取活动详情
     */
    public function getActivity($userid, $tag_id) {
        $map = array(
            'sn.status' => 1,
            'sn.type' => 2,
            'snt.tagid' =>$tag_id,
        );
        $order = array("sn.create_time desc");
        $res = \think\Db::field('sn.*,0 is_enroll')
            ->table('sw_notice sn')
            ->join('sw_notice_tag snt','sn.id = snt.pid')
            ->where($map)
            ->order($order)
            ->limit(6)
            ->select();
        //$res = $this->where($map)->order($order)->limit(6)->select();
        foreach ($res as $value) {
            //1已报名 2已截止 3已满
            if($value['start_time'] > time()) {
                $value['is_enroll'] = 2;
            }else {
                if($value['enrollnum'] == $value['people']) {
                    $value['is_enroll'] = 3;
                }else {
                    $data = array(
                        'notice_id' => $value['id'],
                        'userid' => $userid,
                        'status' => 1
                    );
                    $msg = NoticeEnroll::where($data)->find();
                    if($msg) {
                        $value['is_enroll'] = 1;
                    }else {
                        $value['is_enroll'] = 0;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * 获取活动顶部轮播
     */
    public function getThreeNote($tag_id) {
        $map = array(
            'sn.type' => 1,
            'sn.status' => 1,
            'sn.recommend' =>1,
            'snt.tagid' =>$tag_id,
        );
        $order = array("sn.create_time desc");
        $res = \think\Db::field('sn.*')
            ->table('sw_notice sn')
            ->join('sw_notice_tag snt','sn.id = snt.pid')
            ->where($map)
            ->order($order)
            ->limit(3)
            ->select();
        //$res = $this->where($map)->order($order)->limit(3)->select();
        return $res;
    }
    
    /**
     * 获取通知
     */
    public function getNotes($tag_id) {
        $map = array(
            'sn.status' => 1,
            'sn.type' => 1,
            'snt.tagid' =>$tag_id,
        );
        $order = array("sn.create_time desc");
        $res = \think\Db::field('sn.*')
            ->table('sw_notice sn')
            ->join('sw_notice_tag snt','sn.id = snt.pid')
            ->where($map)
            ->order($order)
            ->limit(6)
            ->select();
        //$res = $this->where($map)->order($order)->limit(6)->select();
        return $res;
    }

    /**
     * 获取更多
     */
    public function getMoreList($data, $tag_id) {
        $type = (int)$data['type'];
        $map = array(
            'sn.status' => 1,
            'sn.type' => $type,
            'snt.tagid' =>$tag_id,
        );
        $order = array('sn.create_time desc');
        $list = \think\Db::field('sn.*')
            ->table('sw_notice sn')
            ->join('sw_notice_tag snt','sn.id = snt.pid')
            ->where($map)
            ->order($order)
            ->limit($data['length'],6)
            ->select();
        //$list = $this->where($map)->order($order)->limit($data['length'],6)->select();
        foreach($list as $value){
            $img = Picture::get($value['front_cover']);
            $value['src'] = $img['path'];
            $value['time'] = date("Y-m-d",$value['create_time']);
            if($type == 2) {
                //1已报名 2已截止 3已满
                if($value['start_time'] > time()) {
                    $value['is_enroll'] = 2;
                }else {
                    if($value['enrollnum'] == $value['people']) {
                        $value['is_enroll'] = 3;
                    }else {
                        $data = array(
                            'notice_id' => $value['id'],
                            'userid' => $data['userid'],
                            'status' => 1
                        );
                        $msg = NoticeEnroll::where($data)->find();
                        if($msg) {
                            $value['is_enroll'] = 1;
                        }else {
                            $value['is_enroll'] = 0;
                        }
                    }
                }
            }
        }
        return $list;
    }

}