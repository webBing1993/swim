<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/21
 * Time: 14:41
 */
namespace app\admin\controller;

use think\Controller;
use app\admin\model\PushReview;
use app\admin\model\Push;
use app\admin\model\Picture;
use com\wechat\TPQYWechat;
use app\admin\model\News as NewsModel;
use think\Config;
/**
 * Class News
 * @package 游泳动态控制器
 */
class News extends Admin {

    /**
     * 新闻列表
     */
    public function index(){
        $map = array(
            'type'=>0,
            'status' => array('egt',0),
        );
        $list = $this->lists('News',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
            'recommend' => array( 1=>"推荐" , 0=>"不推荐")
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 新闻添加
     */
    public function add(){
        if(IS_POST) {
            $data = input('post.');
            $data['create_user'] = $_SESSION['think']['user_auth']['id'];
            if(empty($data['id'])) {
                unset($data['id']);
            }
            $newModel = new NewsModel();
            $info = $newModel->validate('news.other')->save($data);
            if($info) {
                return $this->success("新增成功",Url('News/index'));
            }else{
                return $this->error($newModel->getError());
            }
        }else{
            $this->default_pic();
            $this->assign('msg','');

            return $this->fetch('edit');
        }
    }

    /**
     * 新闻修改
     */
    public function edit(){
        if(IS_POST) {
            $data = input('post.');
            $newModel = new NewsModel();
            $info = $newModel->validate('news.other')->save($data,['id'=>input('id')]);
            if($info){
                return $this->success("修改成功",Url("News/index"));
            }else{
                return $this->get_update_error_msg($newModel->getError());
            }
        }else{
            $this->default_pic();
            $id = input('id');
            $msg = NewsModel::get($id);
            $this->assign('msg',$msg);
            
            return $this->fetch();
        }
    }
    /*
     * 游泳百科
     */
    public function wiki(){
        $map = array(
            'type'=>1,
            'status' => array('egt',0),
        );
        $list = $this->lists('News',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布",1=>"已发布"),
            'recommend' => array( 1=>"推荐" , 0=>"不推荐")
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     * 添加\修改 百科
     */
    public function wikiadd(){
        $id = input('param.id');
        if($id){
            //修改
            if(IS_POST){
                // 修改后保存
                $data = input('post.');
                $newModel = new NewsModel();
                $info = $newModel->validate('news.act')->save($data,['id'=>input('id')]);
                if($info){
                    return $this->success("修改成功",Url("News/wiki"));
                }else{
                    return $this->get_update_error_msg($newModel->getError());
                }
            }else{
                //页面显示
                $newModel = new NewsModel();
                $map =['id'=> $id];
                $info = $newModel->where($map)->find();
                if($info == null){
                    return $this->error("系统不存在该数据");
                }
                $this->assign('msg',$info);
                return $this->fetch();
            }
        }else{
            //添加
            if(IS_POST){
                // 添加后保存
                $data = input('post.');
                if(empty($data['id'])){
                    unset($data['id']);
                }
                $data['create_user'] = $_SESSION['think']['user_auth']['id'];
                $newModel = new NewsModel();
                $info = $newModel->validate('news.act')->save($data);
                if($info) {
                    return $this->success("新增成功",Url('News/wiki'));
                }else{
                    return $this->error($newModel->getError());
                }
            }else{
                //页面显示
                $this->default_pic(); // 默认图片
                $msg = array(
                    'description' => '',
                    'content' => '',
                );
                $this->assign('msg',$msg);
                return $this->fetch();
            }
        }
    }
    /*
     * 游泳视频
     */
    public function vedio(){
        $map = array(
            'type'=>2,
            'status' => array('egt',0),
        );
        $list = $this->lists('News',$map);
        int_to_string($list,array(
            'status' => array(0=>"已发布"),
            'recommend' => array( 1=>"推荐" , 0=>"不推荐")
        ));
        $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     * 视频 添加 修改
     */
    public function vedioadd(){
        $id = input('param.id');
        $this->default_pic();
        if($id){
            //修改
            if(IS_POST){
                // 修改后保存
                $data = input('post.');
                if($data['net_path'] == ""){
                    return $this->error("请上传视频网址");
                }
                $data['content'] = $data['net_path'];
                unset($data['net_path']);
//                // 存到数据表相应字段  并释放掉之前的变量
//                if($data['video_path'] != "" ){
//                    $data['content'] = $data['video_path'];
//                    unset($data['video_path']);
//                    unset($data['net_path']);
//                }else{
//                    $data['content'] = $data['net_path'];
//                    unset($data['net_path']);
//                    unset($data['video_path']);
//                }
//                if($data['img']){
//                    // 判断视频第一帧图片 是否修改
//                    $newModel = new NewsModel();
//                    $map =['id'=> $id];
//                    $info = $newModel->where($map)->find();
//                    $file = fopen($info['img'],"r");
//                    $info['info'] = fread($file,filesize($info['img']));
//                    fclose($file);
//                    // 获取 视频第一帧图片
//                    if($data['img'] != $info['info']){
//                        $filename = "./text/".time().".txt";
//                        $file = fopen($filename,"w");
//                        fwrite($file,$data['img']);
//                        fclose($file);
//                        $data['img'] = $filename;
//                    }else{
//                        $data['img'] = $info['img'];
//                    }
//                    unset($data['path']);
//                }else{
//                    $data['front_cover'] = $data['path'];
//                    unset($data['path']);
//                }
                $data['create_user'] = $_SESSION['think']['user_auth']['id'];
                $newModel = new NewsModel();
                $info = $newModel->validate('news.lit')->save($data,['id'=>input('id')]);
                if($info) {
                    return $this->success("修改成功",Url('News/vedio'));
                }else{
                    return $this->error($newModel->getError());
                }
            }else{
                //页面显示
                $newModel = new NewsModel();
                $map =['id'=> $id];
                $info = $newModel->where($map)->find();
                if($info == null){
                    return $this->error("系统不存在该数据");
                }
                if($info['img'] != null){
                    $file = fopen($info['img'],"r");
                    $info['info'] = fread($file,filesize($info['img']));
                    fclose($file);
                }
                $info['class'] = 1; // 修改
                $this->assign('msg',$info);
                return $this->fetch();
            }
        }else{
            //添加
            if(IS_POST){
                // 添加后保存
                $data = input('post.');
                if($data['net_path'] == ""){
                     return $this->error("请上传视频网址");
                }
                $data['content'] = $data['net_path'];
                unset($data['net_path']);
//                // 存到数据表相应字段  并释放掉之前的变量
//                if($data['video_path'] != "" ){
//                    $data['content'] = $data['video_path'];
//                    unset($data['video_path']);
//                    unset($data['net_path']);
//                }else{
//                    $data['content'] = $data['net_path'];
//                    unset($data['net_path']);
//                    unset($data['video_path']);
//                }
                if(empty($data['id'])){
                    unset($data['id']);
                }
                // 获取 视频第一帧图片
//                if($data['img']){
//                    $filename = "./text/".time().".txt";
//                    $file = fopen($filename,"w");
//                    fwrite($file,$data['img']);
//                    fclose($file);
//                    $data['img'] = $filename;
//                    unset($data['path']);
//                }else{
//                    $data['front_cover'] = $data['path'];
//                    unset($data['path']);
//                }
                $data['create_user'] = $_SESSION['think']['user_auth']['id'];
                $newModel = new NewsModel();
                $info = $newModel->validate('news.lit')->save($data);
                if($info) {
                    return $this->success("新增成功",Url('News/vedio'));
                }else{
                    return $this->error($newModel->getError());
                }
            }else{
                //页面显示
                $msg = array(
                    'content' => '',
                    'info' => '',
                    'class' => 0, // 添加
                );
                $this->assign('msg',$msg);
                return $this->fetch();
            }
        }
    }
    /**
     * 删除功能
     */
    public function del(){
        $id = input('id');
        $data['status'] = '-1';
        $info = NewsModel::where('id',$id)->update($data);
        if($info) {
            return $this->success("删除成功");
        }else{
            return $this->error("删除失败");
        }

    }
    /*
     * 新闻动态推送列表
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
                'type'=> array('in',[0,1]),  // 游泳动态  游泳百科
                'create_time' => array('egt',$t),
                'status' => 0
            );
            $infoes=NewsModel::where($info)->select();
            foreach($infoes as $value){
                if ($value['type'] == 0){
                    $value['title'] = '【新闻动态】'.$value['title'];
                }else{
                    $value['title'] = '【游泳百科】'.$value['title'];
                }
            }
            return $this->success($infoes);
        }else{
            //相关活动列表
            $map = array(
                'con' => array('in',[3,4]),// 3 为新闻  4 百科
            );
            $list=$this->lists('Push',$map);
            int_to_string($list,array(
                'con' => array(3=>'新闻动态',4=>'游泳百科'),
                'status' => array(-1=>'不通过',0=>'待审核',1=>'已通过')
            ));
            //数据重组
            foreach($list as $value){
                $msg = NewsModel::where('id',$value['focus_main'])->find();
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
                'type' =>array('in',[0,1])  // 0 为新闻动态 1  游泳百科
            );
            $infoes=NewsModel::where($info)->select();
            foreach($infoes as $value){
                if ($value['type'] == 0){
                    $value['title'] = '【新闻动态】'.$value['title'];
                }else{
                    $value['title'] = '【游泳百科】'.$value['title'];
                }
            }
            $this->assign('info',$infoes);
            return $this->fetch();
        }
    }
    /*
     * 新闻动态  游泳百科推送
     */
    public function push(){
        $data = input('post.');
        $arr1 = $data['focus_main'];      //主图文id
        isset($data['focus_vice']) ? $arr2 = $data['focus_vice'] : $arr2 = "";  //副图文id
        if($arr1 == -1){
            return $this->error('请选择主图文');
        }else{
            //主图文信息
            $info1 = NewsModel::where('id',$arr1)->find();
        }
        $update['status'] = '1';
        NewsModel::where(['id' => $arr1])->update($update); // 推送成功  修改状态
        $title1 = $info1['title'];
        $str1 = strip_tags($info1['content']);
        $des1 = mb_substr($str1,0,40);
        $content1 = str_replace("&nbsp;","",$des1);  //空格符替换成空
        // 详情跳转到不同的模板
        $url1 = "http://swimming.0571ztnet.com/home/news/news_unreview/id/".$info1['id'].".html";
        $image1 = Picture::get($info1['front_cover']);
        $path1 = "http://swimming.0571ztnet.com".$image1['path'];
        if ($info1['type'] == 0){
            $pre = '【新闻动态】';
            $data['con'] = 3;
        }else{
            $pre = '【游泳百科】';
            $data['con'] = 4;
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
                NewsModel::where(['id' => $value])->update($update); // 修改状态
                $info2 = NewsModel::where('id',$value)->find();
                $title2 = $info2['title'];
                $str2 = strip_tags($info2['content']);
                $des2 = mb_substr($str2,0,40);
                $content2 = str_replace("&nbsp;","",$des2);  //空格符替换成空
                // 详情跳转到不同的模板
                $url2 = "http://swimming.0571ztnet.com/home/news/news_unreview/id/".$info2['id'].".html";
                $image2 = Picture::get($info2['front_cover']);
                $path2 = "http://swimming.0571ztnet.com".$image2['path'];
                if ($value['type'] == 0){
                    $pre = '【新闻动态】';
                }else{
                    $pre = '【游泳百科】';
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
        //发送给服务号   审核
        $Wechat = new TPQYWechat(Config::get('review'));
        $message = array(
            'totag' => "2", //审核标签用户
            "msgtype" => 'news',
            "agentid" =>9,
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