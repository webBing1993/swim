<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/19
 * Time: 19:09
 */

namespace app\home\controller;
use app\home\model\Browse;
use app\home\model\Comment;
use app\home\model\Like;
use app\home\model\Score;
use app\home\model\WechatUser;
use think\Db;

/**
 * Class Rank
 * 排行榜
 */
class Rank extends Base {

    /**
     * 个人中心排行榜
     */
    public function index(){
        $this->anonymous();
        $wechatModel = new WechatUser();
        $userId = session('userId');
        //个人信息
        $personal = $wechatModel::where('userid',$userId)->find();
//        $personal['score'] = $personal['score'] + 10;  // 关注企业号  基础分10
        //总榜
        $con['score'] = array('neq',0);
        $all  = $wechatModel->where($con)->order('score desc')->select();
//        var_dump($all);die;
        foreach ($all as $k => $value){
//            $value['score'] += 10;  // 关注企业号  基础分10
            $all[$k]['scores'] = $value['score'];
            $all[$k]['path'] = get_header($value['userid']);
            if($value['userid'] == $userId){
                $personal['rank'] = $k+1;
            }
        }
        $this->assign('all',$all);
        $this->assign('personal',$personal);

        //获取周榜信息
        date_default_timezone_set("PRC");        //初始化时区
        $y = date("Y");        //获取当天的年份
        $m = date("m");        //获取当天的月份
        $d = date("d");        //获取当天的号数
        $todayTime= mktime(0,0,0,$m,$d,$y);        //将今天开始的年月日时分秒，转换成unix时间戳
        $time = date("N",$todayTime);        //获取星期数进行判断，当前时间做对比取本周一和上周一时间。
        //$t为本周周一，$s为上周周一
        switch($time){
            case 1: $t = $todayTime;
                break;
            case 2: $t = $todayTime - 86400*1;
                break;
            case 3: $t = $todayTime - 86400*2;
                break;
            case 4: $t = $todayTime - 86400*3;
                break;
            case 5: $t = $todayTime - 86400*4;
                break;
            case 6: $t = $todayTime - 86400*5;
                break;
            case 7: $t = $todayTime - 86400*6;
                break;
            default:
        }
        $map = array(
            'create_time' => array('egt',$t),
        );
        $week = Score::where($map)->field('userid, sum(score) scores')->group('userid')->order('scores desc, userid')->limit(20)->select();
        //最终重组，限制输出20名，获取用户个人信息
        foreach ($week as $key => $value){
            $week[$key]['path'] = get_header($value['userid']);
            $week[$key]['name'] = WechatUser::getName($value['userid']);
        }
        $this->assign('week',$week);

        //获取月榜信息
        date_default_timezone_set("PRC");        //初始化时区
        $start = mktime(0,0,0,date('m'),1,date('Y'));
        $end = mktime(23,59,59,date('m'),date('t'),date('Y'));
        $map = array(
            'create_time' => array('between',[$start,$end]),    //在时间区间内
        );

        $month = Score::where($map)->field('userid, sum(score) scores')->group('userid')->order('scores desc, userid')->limit(20)->select();
        //最终重组，限制输出20名，获取用户个人信息
        foreach ($month as $key => $value){
            $month[$key]['path'] = get_header($value['userid']);
            $month[$key]['name'] = WechatUser::getName($value['userid']);
        }
//        var_dump($month);die;
        $this->assign('month',$month);

        return $this->fetch();
    }


}