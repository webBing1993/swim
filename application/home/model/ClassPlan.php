<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/17
 * Time: 14:31
 */
namespace app\home\model;
use think\Model;
use think\Collection;

class ClassPlan extends Model
{
    const WEEK_SUNDAY = 1;
    const WEEK_MONDAY = 2;
    const WEEK_TUESDAY = 3;
    const WEEK_WEDNESDAY = 4;
    const WEEK_THURSDAY = 5;
    const WEEK_FRIDAY = 6;
    const WEEK_SATURDAY = 7;
    const WEEK_ARRAY = [
        self::WEEK_SUNDAY  => '星期天',
        self::WEEK_MONDAY  => '星期一',
        self::WEEK_TUESDAY  => '星期二',
        self::WEEK_WEDNESDAY  => '星期三',
        self::WEEK_THURSDAY  => '星期四',
        self::WEEK_FRIDAY  => '星期五',
        self::WEEK_SATURDAY  => '星期六',
    ];

    public $insert = [
        'views' => 0,
        'create_time' => NOW_TIME,
    ];

    /**
     * 获取列表
     */
    public static function getList($userId, $time) {
        $map = array(
            'userid' => $userId,
            "FROM_UNIXTIME(start,'%Y年%m月')" => $time,
        );
        $order = array("start");
        $res = self::where($map)->field("*, FROM_UNIXTIME(start,'%Y-%m-%d') start_time, DAYOFWEEK(FROM_UNIXTIME(start,'%Y-%m-%d')) week")->order($order)->select();
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
        $res = self::where($map)->field("*, FROM_UNIXTIME(start,'%Y-%m-%d') start_time")->find()->toArray();
        $res['score'] = ClassScore::where(['pid' => $res['id']])->field('userid, name, score')->order('`order`')->select();
        if($res['score']){
            $collection = new Collection($res['score']);
            $res['score'] = $collection->toArray();
        }
        $res['contents'] = ClassContent::where(['pid' => $res['id']])->field('id, load, strength, duration')->order('type')->select();
        if($res['contents']){
            $collection = new Collection($res['contents']);
            $res['contents'] = $collection->toArray();
            foreach($res['contents'] as $key => $model) {
                $res['contents'][$key]['content'] = ClassTrain::where(['pid' => $model['id']])->field('group, num, distance, pose')->order('`order`')->select();
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