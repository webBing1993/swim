<?php
/**
 * Created by PhpStorm.
 * User: Lxx<779219930@qq.com>
 * Date: 2016/9/12
 * Time: 15:56
 */

namespace app\home\controller;
use app\home\model\Browse;
use app\home\model\Collect;
use app\home\model\Comment;
use app\home\model\Like;
use app\home\model\Notice;
use app\home\model\Picture;
use app\home\model\WechatUser;
use think\Controller;
use app\home\model\News as NewsModel;
/**
 * Class News
 * @package 新闻动态
 */
class News extends Base {
    /**
     * 主页
     */
    public function news1(){
        $this->anonymous(); //判断是否是游客
        $this->default_pic();
        $userId = session('userId');

        $c = input('c');
        $this->assign('c',$c);  // 新闻 百科 视频 tab切换

        // 新闻动态  轮播推荐
        $map1 = array(
            'type' => 0, // 新闻动态
            'status' => array('egt',0), // 审核通过
            'recommend' => 1
        );
        $recom = NewsModel::where($map1)->order('id desc')->limit(3)->select();
        if($recom){
            $this->assign('show',1);
        }else{
            $this->assign('show',0);
        }
        $this->assign('recommend',$recom);

        //新闻动态  列表
        $map2 = array(
            'type' => 0, // 新闻动态
            'status' => array('egt',0),  // 通过审核
        );
        $list = NewsModel::where($map2)->order('id desc')->limit(4)->select();
        if($list){
            $this->assign('shows',1);
        }else{
            $this->assign('shows',0);
        }
        $this->assign('list',$list);


        // 游泳百科   列表
        $map3 = array(
            'type' => 1 ,  // 百科
            'status' => array('egt',0) // 审核通过
        );
        $wiki = NewsModel::where($map3)->order('id desc')->limit(6)->select();
        if($wiki){
            $this->assign('show1',1);
        }else{
            $this->assign('show1',0);
        }
        $this->assign('wiki',$wiki);

        // 游泳视频  列表
        $map4 = array(
            'type' => 2, // 视频
            'status' => array('egt',0)
        );
        $video = NewsModel::where($map4)->order('id desc')->limit(3)->select();
        if($video){
            $this->assign('shows1',1);
        }else{
            $this->assign('shows1',0);
        }
        foreach($video as $value){
            $Pic = Picture::where('id',$value['front_cover'])->find();
            $value['img'] = $Pic['path'];
//            if($value['img'] == null){
//                $value['img'] = $value['front_cover'];
//            }else{
//                $file = fopen($value['img'],"r");
//                $value['img'] = fread($file,filesize($value['img']));
//                fclose($file);
//            }
            // 视频是否点赞
            $cons = array(
                'news_id' => $value['id'],
                'create_user' => $userId,
                'status' => 0 , //  正常
                'class' => 2 ,// 游泳动态
                'type' => 1 // 文章点赞
            );
            $Like = Like::where($cons)->find();
            if($Like) {
                $value['is_like'] = 1;
            }else{
                $value['is_like'] = 0;
            }
        }
        $this->assign('video',$video);

        return $this->fetch();
    }

    /*
     * 详细页面
     */
    public  function news_details(){
        //判断是否是游客
        $this->anonymous();
        $this->jssdk();
        $userId = session('userId');
        $newsModel = new NewsModel();
        $id = input("id");
        // 浏览量 +1
        $info['views'] = array('exp','`views`+1');
        $newsModel::where('id',$id)->update($info);
        if($userId != "visitor"){
            //浏览不存在则存入sw_browse表
            $con = array(
                'user_id' => $userId,
                'news_id' => $id, // 新闻动态 id
            );
            $history = Browse::get($con);
            if(!$history && $id != 0){
                Browse::create($con);
            }
        }
        //  新闻动态 基本信息
        $result =  $newsModel::get($id);
        $result['user'] = session('userId');
        //分享图片及链接及描述
        $image = Picture::where('id',$result['front_cover'])->find();
        $result['share_image'] = "http://".$_SERVER['SERVER_NAME'].$image['path'];
        $result['link'] = $_SERVER['REDIRECT_URL'];
        $result['desc'] = str_replace('&nbsp;','',strip_tags($result['content']));

        //文章点赞是否存在
        $map2 = array(
            'news_id' => $id,
            'create_user' => $userId,
            'status' => 0,
            'type' => 1,
        );
        $msg = Like::where($map2)->find();
        if($msg) {
            $result['is_like'] = 1;
        }else{
            $result['is_like'] = 0;
        }
        //收藏是否存在
        unset($map2['type']);
        $col = Collect::where($map2)->find();
        if($col) {
            $result['is_collect'] = 1;
        }else {
            $result['is_collect'] = 0;
        }

        $this->assign('result',$result);

        //评论
        $map = array(
            'news_id' => $id,
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
            if ($value['create_user'] == "visitor"){
                $value['header'] = '/home/images/vistor.jpg';
                $value['nickname'] = '游客';
            }else{
                $users = WechatUser::where('userid',$value['create_user'])->find();
                $value['header'] = ($users['header']) ? $users['header'] : $users['avatar'];
                $value['nickname'] = ($users['nickname']) ? $users['nickname'] : $users['name'];
            }
        }
        $this->assign('comment',$comment);

        return $this->fetch();
    }
    /*
     * 待审核页面详情
     */
    public  function news_unreview(){
        $id = input('param.id');
        $map = array(
            'id'  => $id,
            'status' => 1   // 待审核
        );
        $list = NewsModel::where($map)->find();
        $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     * 新闻 加载更多
     */
    public function newsmore(){
        $id = input('post.id');

        $map = array(
            'type' => 0,  // 新闻
            'status' => array('eq',2), // 审核通过
        );
        $obj = NewsModel::where($map)->order('id desc')->limit($id,3)->select();
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
    /*
     * 百科 加载更多
     */
    public function wikimore(){
        $id = input('post.id');
        $maps = array(
            'type' => 1, // 百科
            'status' => array('eq',2)  // 审核通过
        );
        $objs = NewsModel::where($maps)->order('id desc')->limit($id,3)->select();
        if($objs){
            foreach($objs as $value){
                $Pictures = Picture::where(array('id' => $value['front_cover']))->find();
                $value['front_cover'] = $Pictures['path']; // 数据重组  直径获取图片路径在js中显示
                $value['create_time'] = date('Y-m-d',$value['create_time']); // 数据重组  获取时间 年与日
            }
            return $objs;
        }else{
            return 0;
        }
    }
    /*
     * 视频  加载更多
     */
    public function videomore(){
        $id = input('post.id');
        $userId = session('userId');
        $con = array(
            'type' => 2 , //  视频
            'status' => array('eq',0)  // 正常
        );
        $objj = NewsModel::where($con)->order('id desc')->limit($id,2)->select();
        if($objj){
            foreach($objj as $value){
                if (!empty($value['img'])){
                    $files = fopen($value['img'],"r");
                    $value['img'] = fread($files,filesize($value['img']));
                    fclose($files);
                }
                $value['create_time'] = date("Y-m-d",$value['create_time']);
                // 视频是否点赞
                $conss = array(
                    'news_id' => $value['id'],
                    'create_user' => $userId,
                    'status' => 0 , //  正常
                    'class' => 2 ,// 游泳动态
                    'type' => 1 // 文章点赞
                );
                $Likes = Like::where($conss)->find();
                if($Likes) {
                    $value['is_like'] = 1;
                }else{
                    $value['is_like'] = 0;
                }
            }
            return $objj;
        }else{
            return 0;
        }
    }
    /*
     * 视频 访问量
     */
    public function videodetail(){
        $id = input('param.id');
        $mapp = array(
            'id' => $id,
            'type' => 2, //  在线视频
            'status' => 0
        );
        $infos['views'] = array('exp','`views`+1');
        NewsModel::where($mapp)->update($infos);
        $result = NewsModel::where($mapp)->find();
        $this->assign('img',$result['content']);
        return $this->fetch('addviews');
    }
}