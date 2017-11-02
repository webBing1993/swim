<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// $Id$

return [
    'url_route_on' => true,
//    'log'          => [
//        'type' => 'trace', // 支持 socket trace file
//    ],

    /* 默认模块和控制器 */
    'default_module' => 'home',

    /* URL配置 */
    'base_url'=>'',
    'parse_str'=>[
        '__ROOT__' => '/',
        '__STATIC__' => '/static',
        '__ADMIN__' => '/admin',
        '__HOME__' => '/home',
    ],

    /* UC用户中心配置 */
    'uc_auth_key' => '(.t!)=JTb_OPCkrD:-i"QEz6KLGq5glnf^[{p;je',

    /* 分页自定义 */
    'paginate' => [
        'type'     => '\org\Pager',
        'var_page' => 'page',
        'list_rows'=> 12
    ],

    //游泳模块
    'work' => array(
        'login' => 'http://swim.0571ztnet.com/home/index/login',
//        'token' => 'RMRUYhgJh7C',
//        'encodingaeskey' => 'XO7KtGSIpsnGPR24x3UmfTnLXSmEfogGhmRqUkoefNj',
        'appid' => 'wwc2778c4e14ba4809',
        'appsecret' => 'Kv-8VAYDn5tj8q4Rl0PEVog6Qtn3269SqV5s3WKIscg',
        'agentid' => 1000005
    ),
    //通讯录模块
    'mail' => array(
        'login' => 'http://swim.0571ztnet.com/home/index/login',
//        'token' => 'RMRUYhgJh7C',
//        'encodingaeskey' => 'XO7KtGSIpsnGPR24x3UmfTnLXSmEfogGhmRqUkoefNj',
        'appid' => 'wwc2778c4e14ba4809',
        'appsecret' => 'UQKeB8EtsBfqxLru0BppTyLpKONzk0I4h4GdlAx0_Ps',
        'agentid' => 1000005
    ),

    //游客模块
    'visitor' => [
        'appid' => 'wwc2778c4e14ba4809',
        'appsecret' => 'LNvpUNXhfFJUEEQqsxtFKgSapSBnyC6VS65Yf1WdbmY',
        'agentid' => 1000006
    ],
    //消息模块
    'news' => [
        'appid' => 'wwc2778c4e14ba4809',
        'appsecret' => 'zq0-StU_DOZ_Jxx11hzuqaDX5Y9RevtmS4AyrXpgVs4',
        'agentid' => 1000004
    ],
    //消息审核模块
    'review' => [
        'appid' => 'wwc2778c4e14ba4809',
        'appsecret' => 'ainNsCe_K7ZRI2UbMxLi8inZQG6QFEIvpyXBeIkcLHc',
        'agentid' => 1000007
    ],

    //默认头像图片
    'de_header' => '/home/images/common/default.png',
    //默认证书图片
    'de_card' => '/home/images/common/card.png',

    // 推送域名
    'http_url' => 'http://xpf.pb.cn',
    //推送接受用户  @all全部
    'touser' => 'ben',

];
