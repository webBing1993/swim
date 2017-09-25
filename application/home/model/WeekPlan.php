<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/16
 * Time: 17:12
 */
namespace app\home\model;
use think\Model;
use think\Collection;

class WeekPlan extends Model
{
    public $insert = [
        'views' => 0,
        'status' => 0,
        'create_time' => NOW_TIME,
    ];

    /**
     * 获取列表
     */
    public static function getList($userId, $time) {
        $map = array(
            'status' => 0,
            'userid' => $userId,
            "FROM_UNIXTIME(start,'%Y年%m月')" => $time,
        );
        $order = array("start");
        $res = self::where($map)->field("*, FROM_UNIXTIME(start,'%Y-%m-%d') start_time, FROM_UNIXTIME(end,'%Y-%m-%d') end_time")->order($order)->select();
        if($res){
            $collection = new Collection($res);
            return $collection->toArray();
        }else{
            return array();
        }
    }
    /**
     * 获取周计划
     */
    public static function getModelById($id) {
        $map = array(
            'id' => $id,
        );
        $res = self::where($map)->field("*, FROM_UNIXTIME(start,'%Y-%m-%d') start_time, FROM_UNIXTIME(end,'%Y-%m-%d') end_time")->find()->toArray();
        $res['contents'] = WeekContent::where(['pid' => $res['id']])->field('id, type, contentself, load, duration')->order('type')->select();
        if($res['contents']){
            $collection = new Collection($res['contents']);
            $res['contents'] = $collection->toArray();
            foreach($res['contents'] as $key => $model) {
                $res['contents'][$key]['content'] = WeekTrain::where(['pid' => $model['id']])->field('group, num, distance, pose, detail')->order('`order`')->select();
                if($res['contents'][$key]['content']){
                    $collection = new Collection($res['contents'][$key]['content']);
                    $res['contents'][$key]['content'] = $collection->toArray();
                    foreach($res['contents'][$key]['content'] as $k =>  $content) {
                        $res['contents'][$key]['content'][$k] = array_values($content);
                    }
                }
                unset($res['contents'][$key]['id']);
            }
        }
        return $res;
    }
}