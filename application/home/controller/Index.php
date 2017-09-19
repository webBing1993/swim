<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/4/20
 * Time: 13:47
 */

namespace app\home\controller;
use app\home\model\Message;
use app\home\model\WechatUser;
use app\home\model\WechatUserTag;
use app\home\model\Notice;
use com\wechat\TPQYWechat;
use think\Config;
use think\Controller;
use app\user\controller\Index as APIIndex;
use think\Log;

/**
 * 主页
 */
class Index extends Controller {
    public function index(){
        $userId = session('userId');
        $tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
        $map = array(
            'sn.status' => 1,
            'sn.recommend' => 1,
            'snt.tagid' =>$tag_id,
        );
        $order = array("sn.create_time desc");
        $newsList = \think\Db::field('sn.id,sn.front_cover,sn.title,sn.create_time')
            ->table('sw_notice sn')
            ->join('sw_notice_tag snt','sn.id = snt.pid')
            ->where($map)
            ->order($order)
            ->limit(2)
            ->select();
        //$newsList = Notice::where(['status' => 1, 'recommend' => 1])->order('create_time desc')->field('id,front_cover,title,create_time')->limit(2)->select();
        $this->assign('tag_id',$tag_id);
        $this->assign('newsList',$newsList);
        return $this->fetch();
    }
    public function demo(){
        $userId = session('userId');
        $id = input('id', 3);
        if($id == 1){
            $tag_id = 1;
        }
        if($id == 2){
            $tag_id = 2;
        }
        if($id == 3){
            $tag_id = 4;
        }
        //$tag_id = WechatUserTag::where(['userid' => $userId])->value('tagid');
        $map = array(
            'sn.status' => 1,
            'sn.recommend' => 1,
            'snt.tagid' =>$tag_id,
        );
        $order = array("sn.create_time desc");
        $newsList = \think\Db::field('sn.id,sn.front_cover,sn.title,sn.create_time')
            ->table('sw_notice sn')
            ->join('sw_notice_tag snt','sn.id = snt.pid')
            ->where($map)
            ->order($order)
            ->limit(2)
            ->select();
        //$newsList = Notice::where(['status' => 1, 'recommend' => 1])->order('create_time desc')->field('id,front_cover,title,create_time')->limit(2)->select();
        $this->assign('tag_id',$tag_id);
        $this->assign('newsList',$newsList);
        return $this->fetch('index');
    }

    /**
     * 用户登入获取信息
     */
    public function login(){
        // 获取用户信息
        $Wechat = new TPQYWechat(config('work'));
        $result = $Wechat->getUserId(input('code'), config('work.agentid'));
        if(empty($result['UserId'])) {
            session('userId', 'visitor');//游客userid为0
            session('name', '游客');
        } else {
            $userInfo = $Wechat->getUserInfo($result['UserId']);
            $data = [
                'userid' => $userInfo['userid'],
                'name' => $userInfo['name'],
                'mobile' => $userInfo['mobile'],
                'gender' => $userInfo['gender'],
                'avatar' => $userInfo['avatar'],
                'department' => $userInfo['department'][0], //只选第一个所属部门
                'status' => $userInfo['status'],
                'order' => $userInfo['order'][0],
            ];
            if(isset($userInfo['extattr']['attrs'])) {
                $data['extattr'] = json_encode($userInfo['extattr']['attrs']);

                foreach ($userInfo['extattr']['attrs'] as $attrs) {
                    switch ($attrs['name']){
                        case "学历":
                            $data['education'] = $attrs['value'];
                            break;
                        case "身份证号":
                            $data['identity'] = $attrs['value'];
                            break;
                        default:
                            break;
                    }
                }
            }

            $wechatUser = new WechatUser();
            if ($wechatUser->checkUserExist($userInfo['userid'])) {
                $wechatUser->save($data, ['userid' => $userInfo['userid']]);
            } else {
                $wechatUser->save($data);
            }

            session('userId', $userInfo['userid']);
            session('name', $userInfo['name']);
            session('gender', $userInfo['gender']);
            session('avatar', $userInfo['avatar']);
            session('department', $userInfo['department'][0]);

            $this->redirect(session('requestUri'));
        }
    }

    /**
     * 微信公众号跳转
     */
    public function jump() {
        $url = "https://mp.weixin.qq.com/mp/profile_ext?action=home&__biz=MzAwNjA1MDE1OQ==&scene=124#wechat_redirect";
        $this->redirect($url);
    }
}