<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/10/8
 * Time: 10:29
 */

namespace app\admin\controller;

use app\admin\model\Apply;
use app\admin\model\Notice as NoticeModel;
use app\admin\model\Push;
use app\admin\model\Picture;
use app\admin\model\PushReview;
use app\admin\model\WechatDepartment;
use app\admin\model\WechatUser;
use app\admin\model\WechatDepartmentUser;
use com\wechat\TPQYWechat;
use think\Config;

/**
 * Class Notice
 * @package 游泳协会活动通知
 */
class Notice extends Admin {
    /**
     * 相关通知
     * type: 1
     */
    public function index(){
        $map = array(
            'type' => 1,
            'status' => array('egt',0),
        );
        $list = $this->lists('Notice',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
        ));

        $this->assign('list',$list);
        return $this->fetch();
    }

    /*
     * 活动情况
     * type: 2
     */
    public function activity(){
        $map = array(
            'type' => 2,
            'status' => array('egt',0),
        );
        $list = $this->lists('Notice',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
        ));

        $this->assign('list',$list);

        return $this->fetch();
    }

    /**
     * 活动情况添加
     */
    public function indexadd(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            $noticeModel = new NoticeModel();
            if (!empty($data['start_time']) && !empty($data['end_time'])){
                $data['start_time'] = strtotime($data['start_time']);
                $data['end_time'] = strtotime($data['end_time']);
            }
            $id = $noticeModel->validate('Notice.act')->save($data);
            if($id){
                return $this->success("新增相关活动成功",Url('Notice/activity'));
            }else{
                return $this->error($noticeModel->getError());
            }
        }else {
            $this->default_pic();
            return $this->fetch();
        }
    }

    /**
     * 活动情况修改
     */
    public function indexedit(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            $noticeModel = new NoticeModel();
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $id = $noticeModel->validate('Notice.act')->save($data,['id'=>input('id')]);
            if($id){
                return $this->success("修改相关活动成功",Url('Notice/activity'));
            }else{
                return $this->error($noticeModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = NoticeModel::get($id);
            $this->assign('msg',$msg);
            return $this->fetch();
        }
    }

    /**
     * 相关通知添加
     */
    public function add(){
        if(IS_POST) {
            $data = input('post.');
            $noticeModel = new NoticeModel();
            if(empty($data['id'])) {
                unset($data['id']);//为空则添加,将变量id释放
            }
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            $model = $noticeModel->validate('Notice.other')->save($data);
            if($model) {
                return $this->success('新增相关通知成功',Url('Notice/index'));
            }else{
                return $this->error($noticeModel->getError());
            }
        }else{
            $this->default_pic();
            $msg = array();
            $msg['class'] = 1; // 1为添加 ，2为修改
            $this->assign('msg',$msg);
            return $this->fetch('edit');
        }
    }

    /**
     * 相关通知修改
     */
    public function edit(){
        if(IS_POST) {
            $data = input('post.');
            $noticeModel = new NoticeModel();
            //将轮播图转换成json存在数据库
            $model = $noticeModel->validate('Notice.other')->save($data,['id'=> input('id')]);
            if($model) {
                return $this->success('修改相关通知成功',Url('Notice/index'));
            }else{
                return $this->get_update_error_msg($noticeModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = NoticeModel::get($id);
            $msg['class'] = 2;
            $this->assign('msg',$msg);
            return $this->fetch();
        }
    }

    /**
     * 删除
     */
    public function del(){
        $id = input('id');
        $map['status'] = "-1";
        $Notice = NoticeModel::where('id',$id)->find();
        if ($Notice['type'] == 1){
            // 相关通知
            $info = NoticeModel::where('id',$id)->update($map);
            if($info) {
                return $this->success("删除成功");
            }else{
                return $this->error("删除失败");
            }
        }else{
            // 活动情况
            $info = NoticeModel::where('id',$id)->update($map);
            if ($info){
                // 删除 报名表
                $res = Apply::where('notice_id',$id)->update($map);
                if ($res){
                    return $this->success("删除成功");
                }else{
                    return $this->error("删除失败");
                }
            }
        }
    }
    /*
     * 查看报名详情页面
     */
    public function apply(){
        $id = input('id');
        $map = array(
            'notice_id' => $id,
            'status' => array('egt',0),
        );
        $list = $this->lists('Apply',$map);
        foreach($list as $value){
            $User = WechatUser::where('userid',$value['userid'])->find();
            $value['name'] = $User['name'];
            $value['sex'] = $User['gender'];
            $Depart = WechatDepartmentUser::where('userid',$value['userid'])->find();
            $Obj = WechatDepartment::where('id',$Depart['departmentid'])->find();
            $value['department'] = $Obj->name;
        }
        int_to_string($list,array(
            'sex' => array(1=>'男',2=>'女',0=>'未定义'),
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 活动 推送列表
     */
    public function pushlist(){
        if(IS_POST){
            $news_id=input('news_id');//主图文id
            //副图文本周内的新闻消息
            date_default_timezone_set('PRC');     //初始化时区
            $y = date("Y");     //获取当天的年份
            $m = date("m");     //获取当天的月份
            $d = date("d");     //获取当天的日期
            $todayTime=mktime(0,0,0,$m,$d,$y);  //将今天开始的年月日时分秒转换成unix时间戳
            $time=date("N",$todayTime);     //获取星期数进行判断，当前时间做对比取本周一和上周一时间
            //$t为本周周一,$t为上周周一
            switch($time){
                case 1 : $t = $todayTime;
                    break;
                case 2 : $t = $todayTime - 86400*1;
                    break;
                case 3 : $t = $todayTime - 86400*2;
                    break;
                case 4 : $t = $todayTime - 86400*3;
                    break;
                case 5 : $t = $todayTime - 86400*4;
                    break;
                case 6 : $t = $todayTime - 86400*5;
                    break;
                case 7 : $t = $todayTime - 86400*6;
                    break;
                default :
            }
            $info = array(
                'id' => array('neq',$news_id),
                'type'=>array('in',[1,2]),
                'create_time' => array('egt',$t),
                'status' => 0
            );
            $infoes=NoticeModel::where($info)->select();
            foreach($infoes as $value){
                if ($value['type'] == 1){
                    $value['title'] = '【相关通知】'.$value['title'];
                }else{
                    $value['title'] = '【活动情况】'.$value['title'];
                }
            }
            return $this->success($infoes);
        }else{
            //相关活动列表
            $map = array(
                'con' => array('in',[1,2]),//  1 为通知 2 为活动
            );
            $list=$this->lists('Push',$map);
            int_to_string($list,array(
                'con' => array(1=>'相关通知',2=>'活动情况'),
                'status' => array(-1=>'不通过',0=>'待审核',1=>'已发布')
            ));
            //数据重组
            foreach($list as $value){
                $msg = NoticeModel::where('id',$value['focus_main'])->find();
                $value['title'] = $msg['title'];
                //审核信息
                $review = PushReview::where('push_id',$value['id'])->find();
                $value['review_name'] = $review['username'];
                $value['review_time'] = $review['review_time'];
            }
            $this->assign('list',$list);
            //主图文本周内的新闻消息
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
            $info = array(
                'create_time' => array('egt',$t),
                'status'      => 0,
                'type' =>array('in',[1,2])  // 1为通知  2 活动
            );
            $infoes=NoticeModel::where($info)->select();
            foreach($infoes as $value){
                if ($value['type'] == 1){
                    $value['title'] = '【相关通知】'.$value['title'];
                }else{
                    $value['title'] = '【活动情况】'.$value['title'];
                }
            }
            $this->assign('info',$infoes);
            return $this->fetch();
        }
    }
    /**
     * 推送
     */
    public function push(){
        $data = input('post.');
        $arr1 = $data['focus_main'];      //主图文id
        isset($data['focus_vice']) ? $arr2 = $data['focus_vice'] : $arr2 = "";  //副图文id
        if($arr1 == -1){
            return $this->error('请选择主图文');
        }else{
            //主图文信息
            $info1 = NoticeModel::where('id',$arr1)->find();
        }
        $update['status'] = '1';
        NoticeModel::where(['id' => $arr1])->update($update); // 推送成功  修改状态
        $title1 = $info1['title'];
        $str1 = strip_tags($info1['content']);
        $des1 = mb_substr($str1,0,40);
        $content1 = str_replace("&nbsp;","",$des1);  //空格符替换成空
        //  详情跳转到不同的模板
        $url1 = "http://swimming.0571ztnet.com/home/notice/notice_unreview/id/".$info1['id'].".html";
        $image1 = Picture::get($info1['front_cover']);
        $path1 = "http://swimming.0571ztnet.com".$image1['path'];
        if ($info1['type'] == 1){
            $pre = '【相关通知】';
            $data['con'] = 1;
        }else{
            $pre = '【活动情况】';
            $data['con'] = 2;
        }
        $information1 = array(
            'title' => $pre.$title1,
            'description' => $content1,
            'url'  => $url1,
            'picurl' => $path1
        );
        $information = array();
        if(!empty($arr2)){
            //副图文信息
            $information2 = array();
            foreach($arr2 as $key=>$value){
                NoticeModel::where(['id' => $value])->update($update); // 修改状态
                $info2 = NoticeModel::where('id',$value)->find();
                $title2 = $info2['title'];
                $str2 = strip_tags($info2['content']);
                $des2 = mb_substr($str2,0,40);
                $content2 = str_replace("&nbsp;","",$des2);  //空格符替换成空
                //详情跳转到不同的模板
                $url2 = "http://swimming.0571ztnet.com/home/notice/notice_unreview/id/".$info2['id'].".html";
                $image2 = Picture::get($info2['front_cover']);
                $path2 = "http://swimming.0571ztnet.com".$image2['path'];
                if ($info2['type'] == 1){
                    $pre = '【相关通知】';
                }else{
                    $pre = '【活动情况】';
                }
                $information2[] = array(
                    "title" =>$pre.$title2,
                    "description" => $content2,
                    "url" => $url2,
                    "picurl" => $path2,
                );
            }
            //数组合并,主图文放在首位
            foreach($information2 as $key=>$value){
                $information[0] = $information1;
                $information[$key+1] = $value;
            }
        }else{
            $information[0] = $information1;
        }
        //重组成article数据
        $send = array();
        $re[] = $information;
        foreach($re as $key => $value){
            $key = "articles";
            $send[$key] = $value;
        }
        //发送给服务号
        $Wechat = new TPQYWechat(Config::get('review'));
        $message = array(
            'totag' => "2", //审核标签用户
            "msgtype" => 'news',
            "agentid" => 9 ,
            "news" => $send,
            "safe" => "0"
        );
        $msg = $Wechat->sendMessage($message);
        if($msg['errcode'] == 0){
            $data['focus_vice'] ? $data['focus_vice'] = json_encode($data['focus_vice']) : $data['focus_vice'] = null;
            $data['create_user'] = session('user_auth.username');
            $data['status'] = 0;
            //保存到推送列表
            $result=Push::create($data);
            if($result){
                return $this->success('发送成功');
            }else{
                return $this->error('发送失败');
            }
        }else{
            return $this->error('发送失败');
        }
    }

}