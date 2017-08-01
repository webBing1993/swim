<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 16:12
 */

namespace app\home\controller;
use app\admin\model\News;
use app\home\model\Apply;
use app\home\model\Browse;
use app\home\model\Collect;
use app\home\model\Comment;
use app\home\model\Like;
use app\admin\model\Push;
use app\home\model\Picture;
use app\home\model\WechatUser;
use think\Controller;

use app\home\model\Notice as NoticeModel;
use think\Db;

/**
 * Class Notice
 * @package  通知   活动
 */
class Notice extends Base {
    /**
     * 主页
     */
    public function notice1(){
        $this->anonymous(); //判断是否是游客
        $this->default_pic();

        //相关通知  轮播
        $map1 = array(
            'type' =>1,
            'status' => array('egt',0)
        );
        $list1 = NoticeModel::where($map1)->order('id desc')->limit(3)->select();
        //判断是否为空
        if (empty($list1)){
            $this->assign('show',0);
        }else{
            $this->assign('show',1);
        }
        $this->assign('relevant',$list1);

        // 相关通知  列表
        $relevant_list = NoticeModel::where($map1)->order('id desc')->limit(4)->select();
        //判断是否为空
        if (empty($relevant_list)){
            $this->assign('shows',0);
        }else{
            $this->assign('shows',1);
        }
        $this->assign('relevant_list',$relevant_list);

        //活动情况  轮播
        $map2 = array(
            'type' =>2,
            'status' => array('egt',0)
        );
        $list2 = NoticeModel::where($map2)->order('id desc')->limit(3)->select();
        //判断是否为空
        if (empty($list2)){
            $this->assign('show2',0);
        }else{
            $this->assign('show2',1);
        }

        $this->assign('activity',$list2);

        //  活动情况列表
        $list3 = NoticeModel::where($map2)->order('id desc')->limit(4)->select();
        //判断是否为空
        if (empty($list3)){
            $this->assign('shows2',0);
        }else{
            $this->assign('shows2',1);
        }
        foreach($list3 as $value){
            $maap = array(
                'notice_id' => $value['id'],
                'userid' => session('userId'),
                'status' => 0
            );
            $Apply = Apply::where($maap)->find();
            if($Apply){
                $value['apply'] = 1;
            }else{
                $value['apply'] = 0;
            }
            if ($value['end_time'] > time()){
                //  未截止
                $value['end'] = 0;
            }else{
                // 已截止
                $value['end'] = 1;
            }
            if ($value['enrollnum'] >= $value['people']){
                // 人数已满
                $value['full'] = 1;
            }else{
                // 人数未满
                $value['full'] = 0;
            }
        }
        $this->assign('activitys',$list3);
        return $this->fetch();
    }
    /*
     * 相关通知  详细页面
     */
    public function notice_detail(){
        //判断是否是游客
        $this->anonymous();
        $this->jssdk();

        $userId = session('userId');
        $noticeModel = new NoticeModel();
        $id = input('param.id');
        //浏览加一
        $info['views'] = array('exp','`views`+1');
        $noticeModel::where('id',$id)->update($info);
        if($userId != "visitor"){
            //浏览不存在则存入sw_browse表
            $con = array(
                'user_id' => $userId,
                'notice_id' => $id, // 相关通知  活动情况 id
            );
            $history = Browse::get($con);
            if(!$history && $id != 0){
                Browse::create($con);
            }
        }
        //通知 基本信息
        $list = $noticeModel::get($id);
        //重组轮播图片
        $list['carousel'] = json_decode($list['carousel_images']);
        $list['user'] = session('userId');
        //分享图片及链接及描述
        $image = Picture::where('id',$list['front_cover'])->find();
        $list['share_image'] = "http://".$_SERVER['SERVER_NAME'].$image['path'];
        $list['link'] = $_SERVER['REDIRECT_URL'];
        $list['desc'] = str_replace('&nbsp;','',strip_tags($list['content']));

        //文章点赞是否存在
        $map2 = array(
            'notice_id' => $id,
            'create_user' => $userId,
            'status' => 0,
            'type' => 1,
        );
        $msg = Like::where($map2)->find();
        if($msg) {
            $list['is_like'] = 1;
        }else{
            $list['is_like'] = 0;
        }
        //收藏是否存在
        unset($map2['type']);
        $col = Collect::where($map2)->find();
        if($col) {
            $list['is_collect'] = 1;
        }else {
            $list['is_collect'] = 0;
        }

        $this->assign('result',$list);

        //评论
        $map = array(
            'notice_id' => $id,
            'status' => 0
        );
        //敏感词屏蔽
        $badword = array(
            '法轮功','法轮','FLG','六四','6.4','flg'
        );
        $badword1 = array_combine($badword,array_fill(0,count($badword),'***'));
        $comment = Comment::where($map)->limit(0,5)->order('likes desc,id desc')->select();
        foreach($comment as $value){
            $content = $value['content'];
            $str = strtr($content, $badword1);
            $value['content'] = $str;
            if($value['create_user'] == $userId){
                $value['self'] = 1;
            }else{
                $value['self'] = 0;
            }
            $map1 = array(
                'create_user' => $userId,
                'comment_id' => $value['id'],
                'status' => array('egt',0),
                'type' => 2, //评论点赞
            );

            $like = Like::where($map1)->find();
            ($like)?$value['is_like'] = 1:$value['is_like'] = 0;
            $users = WechatUser::where('userid',$value['create_user'])->find();
            $value['header'] = ($users['header']) ? $users['header'] : $users['avatar'];
            $value['nickname'] = ($users['nickname']) ? $users['nickname'] : $users['name'];
        }
        $this->assign('comment',$comment);

        return $this->fetch();
    }
    /*
     *  活动情况  详细页面
     */
    public  function notice_details(){
        //判断是否是游客
        $this->anonymous();
        $this->jssdk();

        $userId = session('userId');
        $noticeModel = new NoticeModel();
        $id = input('param.id');
        //浏览加一
        $info['views'] = array('exp','`views`+1');
        $noticeModel::where('id',$id)->update($info);
        if($userId != "visitor"){
            //浏览不存在则存入sw_browse表
            $con = array(
                'user_id' => $userId,
                'notice_id' => $id, // 相关通知  活动情况 id
            );
            $history = Browse::get($con);
            if(!$history && $id != 0){
                Browse::create($con);
            }
        }
        //通知 活动 基本信息
        $list = $noticeModel::get($id);
        //重组轮播图片
        $list['carousel'] = json_decode($list['carousel_images']);
        $list['user'] = session('userId');
        //分享图片及链接及描述
        $image = Picture::where('id',$list['front_cover'])->find();
        $list['share_image'] = "http://".$_SERVER['SERVER_NAME'].$image['path'];
        $list['link'] = $_SERVER['REDIRECT_URL'];
        $list['desc'] = str_replace('&nbsp;','',strip_tags($list['content']));

        //文章点赞是否存在
        $map2 = array(
            'notice_id' => $id,
            'create_user' => $userId,
            'status' => 0,
            'type' => 1,
        );
        $msg = Like::where($map2)->find();
        if($msg) {
            $list['is_like'] = 1;
        }else{
            $list['is_like'] = 0;
        }
        //收藏是否存在
        unset($map2['type']);
        $col = Collect::where($map2)->find();
        if($col) {
            $list['is_collect'] = 1;
        }else {
            $list['is_collect'] = 0;
        }

        // 报名是否存在
        $map3 = array(
            'notice_id' => $id,
            'userid' => $userId,
            'status' => 0
        );
        $coll = Apply::where($map3)->find();
        if($coll){
            $list['is_apply'] = 1;
        }else{
            $list['is_apply'] = 0;
        }
        if ($list['end_time'] > time()){
            //  未截止
            $list['end'] = 0;
        }else{
            // 已截止
            $list['end'] = 1;
        }
        if ($list['enrollnum'] >= $list['people']){
            // 人数已满
            $list['full'] = 1;
        }else{
            // 人数未满
            $list['full'] = 0;
        }
        
        $this->assign('list',$list);

        //评论
        $map = array(
            'notice_id' => $id,
            'status' => 0
        );
        //敏感词屏蔽
        $badword = array(
            '法轮功','法轮','FLG','六四','6.4','flg'
        );
        $badword1 = array_combine($badword,array_fill(0,count($badword),'***'));
        $comment = Comment::where($map)->limit(0,5)->order('likes desc,id desc')->select();
        foreach($comment as $value){
            $content = $value['content'];
            $str = strtr($content, $badword1);
            $value['content'] = $str;
            if($value['create_user'] == $userId){
                $value['self'] = 1;
            }else{
                $value['self'] = 0;
            }
            $map1 = array(
                'create_user' => $userId,
                'comment_id' => $value['id'],
                'status' => array('egt',0),
                'type' => 2, //评论点赞
            );

            $like = Like::where($map1)->find();
            ($like)?$value['is_like'] = 1:$value['is_like'] = 0;
            $users = WechatUser::where('userid',$value['create_user'])->find();
            $value['header'] = ($users['header']) ? $users['header'] : $users['avatar'];
            $value['nickname'] = ($users['nickname']) ? $users['nickname'] : $users['name'];
        }
        $this->assign('comment',$comment);

        return $this->fetch();
    }
    /*
     * 待审核消息详情 页面
     */
    public  function notice_unreview(){
        $id = input('param.id');
        $map = array(
            'id'  => $id,
            'status' => 1
        );
        $list = NoticeModel::where($map)->find();
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 更多通知
     */
    public function noticemore(){
        $id = input('post.id');

        $map = array(
            'type' => 1,  // 通知
            'status' => array('eq',2), // 审核通过
        );
        $obj = NoticeModel::where($map)->order('id desc')->limit($id,3)->select();
        if($obj){
            foreach($obj as $value){
                $Picture = Picture::where(array('id' => $value['front_cover']))->find();
                $value['front_cover'] = $Picture['path'];// 数据重组  直接获取图片路径在js中显示
                $value['create_time'] = date("Y-m-d",$value['create_time']);  // 数据重组 获取创建时间  年月日
            }
            return $obj;
        }else{
            return 0;
        }

    }
    /**
     * 更多活动
     */
    public function activitymore(){
        $id = input('post.id');
        $map = array(
            'type' => 2, // 活动
            'status' => array('eq',2), // 审核通过
        );
        $obj1 = NoticeModel::where($map)->order('id desc')->limit($id,2)->select();
        if($obj1){
            foreach($obj1 as $value){
                $Picture = Picture::where(array('id' => $value['front_cover']))->find();
                $value['front_cover'] = $Picture['path'];// 数据重组  直接获取图片路径在js中显示
                $value['create_time'] = date("Y-m-d",$value['create_time']);  // 数据重组 获取创建时间  年月日
                // 是否报名
                $mmap = array(
                    'notice_id' => $value['id'],
                    'userid' => session('userId'),
                    'status' => 0
                );
                $Applys = Apply::where($mmap)->find();
                if($Applys){
                    $value['apply'] = 1;
                }else{
                    $value['apply'] = 0;
                }
                if ($value['end_time'] > time()){
                    //  未截止
                    $value['end'] = 0;
                }else{
                    // 已截止
                    $value['end'] = 1;
                }
                if ($value['enrollnum'] >= $value['people']){
                    // 人数已满
                    $value['full'] = 1;
                }else{
                    // 人数未满
                    $value['full'] = 0;
                }
            }
            return $obj1;
        }else{
            return 0;
        }
    }

}