<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/12
 * Time: 10:23
 */

namespace app\home\model;


use think\Model;

class News extends Model {
    /**
     * 获取新闻顶部轮播
     */
    public function getThreeTop() {
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
     * 获取新闻详情
     */
    public function getNews() {
        $map = array(
            'status' => 1,
            'type' => 1
        );
        $order = array("create_time desc");
        $res = $this->where($map)->order($order)->limit(7)->select();
        return $res;
    }

    /**
     * 获取游泳百科
     */
    public function getWiki() {
        $map = array(
            'status' => 1,
            'type' => 2
        );
        $order = array("create_time desc");
        $res = $this->where($map)->order($order)->limit(7)->select();
        return $res;
    }

    /**
     * 获取在线视频
     */
    public function getVideo($userid) {
        $map = array(
            'status' => 1,
            'type' => 3
        );
        $order = array("create_time desc");
        $res = $this->where($map)->order($order)->limit(7)->select();
        foreach ($res as $value) {
            //是否点赞
            $info = array(
                'type' => 1,
                'uid' => $userid,
                'aid' => $value['id'],
                'status' => 0,
            );
            $is_like = Like::where($info)->find();
            if($is_like) {
                $value['is_like'] = 1;
            }else {
                $value['is_like'] = 0;
            }
        }
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