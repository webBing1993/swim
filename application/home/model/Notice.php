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
    /**
     * 获取活动顶部轮播
     */
    public function getThreeActivity() {
        $map = array(
            'type' => 2,
            'status' => 1,
            'recommend' =>1,
        );
        $order = array("create_time desc");
        $res = $this->where($map)->order($order)->limit(3)->select();
        return $res;
    }

    /**
     * 获取活动详情
     */
    public function getActivity() {
        $map = array(
            'status' => 1,
            'type' => 2
        );
        $order = array("create_time desc");
        $res = $this->where($map)->order($order)->limit(7)->select();
        return $res;
    }

    /**
     * 获取活动顶部轮播
     */
    public function getThreeNote() {
        $map = array(
            'type' => 1,
            'status' => 1,
            'recommend' =>1,
        );
        $order = array("create_time desc");
        $res = $this->where($map)->order($order)->limit(3)->select();
        return $res;
    }
    
    /**
     * 获取通知
     */
    public function getNotes() {
        $map = array(
            'status' => 1,
            'type' => 1
        );
        $order = array("create_time desc");
        $res = $this->where($map)->order($order)->limit(7)->select();
        return $res;
    }

    /**
     * 获取更多
     */
    public function getMoreList($data) {
        $map = array(
            'status' => 1,
            'type' => $data['type']
        );
        $order = array('create_time desc');
        $list = $this->where($map)->order($order)->limit($data['length'],5)->select();
        foreach($list as $value){
            $img = Picture::get($value['front_cover']);
            $value['src'] = $img['path'];
            $value['time'] = date("Y-m-d",$value['create_time']);
        }
        return $list;
    }

}