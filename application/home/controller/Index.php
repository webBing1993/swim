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
        $newsList = Notice::where(['type' => 1, 'status' => 1])->order('create_time desc')->field('id,front_cover,title,content')->limit(2)->select();
        $this->assign('newsList',$newsList);
        return $this->fetch();
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