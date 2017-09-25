<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/14
 * Time: 19:44
 */
namespace app\home\model;
use think\Model;
use think\Collection;

class WeekSummary extends Model
{
    public $insert = [
        'views' => 0,
        'status' => 0,
        'create_time' => NOW_TIME,
    ];

    /**
     * 获取周总结列表
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
     * 获取周总结
     */
    public static function getModelById($id) {
        $map = array(
            'id' => $id,
        );
        $res = self::where($map)->field("*, FROM_UNIXTIME(start,'%Y-%m-%d') start_time, FROM_UNIXTIME(end,'%Y-%m-%d') end_time")->find()->toArray();
        return $res;
    }
}